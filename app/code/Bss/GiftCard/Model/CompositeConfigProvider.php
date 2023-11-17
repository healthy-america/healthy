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
 * @copyright  Copyright (c) 2017-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\GiftCard\Model;

use Bss\GiftCard\Helper\Data as HelperData;
use Bss\GiftCard\Model\ResourceModel\GiftCard\QuoteFactory;
use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Checkout\Model\SessionFactory as CheckoutSession;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class composite config provider
 *
 * Bss\GiftCard\Model
 */
class CompositeConfigProvider implements ConfigProviderInterface
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManage;
    /**
     * @var HelperData
     */
    protected $helperData;
    /**
     * @var \Magento\Checkout\Model\SessionFactory
     */
    private $checkoutSession;

    /**
     * @var QuoteFactory
     */
    private $giftCardQuoteFactory;

    /**
     * CompositeConfigProvider constructor.
     * @param HelperData $helperData
     * @param StoreManagerInterface $storeManage
     * @param QuoteFactory $giftCardQuoteFactory
     * @param CheckoutSession $checkoutSession
     */
    public function __construct(
        HelperData $helperData,
        StoreManagerInterface $storeManage,
        QuoteFactory $giftCardQuoteFactory,
        CheckoutSession $checkoutSession
    ) {
        $this->helperData = $helperData;
        $this->storeManage = $storeManage;
        $this->giftCardQuoteFactory = $giftCardQuoteFactory;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * @inheritdoc
     */
    public function getConfig()
    {
        $output = [];
        $quote = $this->checkoutSession->create()->getQuote();
        $result = $this->giftCardQuoteFactory->create()->getGiftCardCode($quote);
        $output['bssGiftCard'] = $result;
        $storeId = $this->storeManage->getStore()->getId();
        $output['isEnabled'] = (bool)$this->helperData->isEnabled($storeId);
        return $output;
    }
}
