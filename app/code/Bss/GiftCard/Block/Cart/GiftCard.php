<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_GiftCard
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\GiftCard\Block\Cart;

use Bss\GiftCard\Helper\Data;
use Bss\GiftCard\Model\ResourceModel\GiftCard\QuoteFactory;
use Magento\Checkout\Block\Cart\AbstractCart;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class gift card
 *
 * Bss\GiftCard\Block\Cart
 */
class GiftCard extends AbstractCart
{
    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var QuoteFactory
     */
    private $giftCardQuoteFactory;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @param Context $context
     * @param CustomerSession $customerSession
     * @param CheckoutSession $checkoutSession
     * @param QuoteFactory $giftCardQuoteFactory
     * @param Data $helper
     * @param array $data
     * @codeCoverageIgnore
     */
    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        CheckoutSession $checkoutSession,
        QuoteFactory $giftCardQuoteFactory,
        Data $helper,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $customerSession,
            $checkoutSession,
            $data
        );
        $this->checkoutSession = $checkoutSession;
        $this->_isScopePrivate = true;
        $this->giftCardQuoteFactory = $giftCardQuoteFactory;
        $this->helper = $helper;
    }

    /**
     * Is display
     *
     * @return bool
     */
    public function isDisplay()
    {
        return $this->helper->isEnabled();
    }

    /**
     * Get gift card apply
     *
     * @return array
     *
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getGiftCardApply()
    {
        $quote = $this->checkoutSession->getQuote();
        return $this->giftCardQuoteFactory->create()->getGiftCardCode($quote);
    }

    /**
     * Get remove url
     *
     * @param   array $giftCard
     * @return  string
     */
    public function getRemoveUrl($giftCard)
    {
        return $this->getUrl(
            'giftcard/cart/giftCardRemove',
            ['id' => $giftCard['id']]
        );
    }
}
