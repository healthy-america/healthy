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

namespace Bss\GiftCard\Controller\Cart;

use Bss\GiftCard\Model\ResourceModel\GiftCard\QuoteFactory;
use Magento\Checkout\Controller\Cart as CheckoutCart;
use Magento\Checkout\Model\Cart;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class gift card remove
 *
 * Bss\GiftCard\Controller\Cart
 */
class GiftCardRemove extends CheckoutCart
{
    /**
     * @var QuoteFactory
     */
    private $giftCardQuoteFactory;

    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param Session $checkoutSession
     * @param StoreManagerInterface $storeManager
     * @param Validator $formKeyValidator
     * @param Cart $cart
     * @param QuoteFactory $giftCardQuoteFactory
     * @param CartRepositoryInterface $quoteRepository
     * @codeCoverageIgnore
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        Session $checkoutSession,
        StoreManagerInterface $storeManager,
        Validator $formKeyValidator,
        Cart $cart,
        QuoteFactory $giftCardQuoteFactory,
        CartRepositoryInterface $quoteRepository
    ) {
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart
        );
        $this->giftCardQuoteFactory = $giftCardQuoteFactory;
        $this->quoteRepository = $quoteRepository;
    }

    /**
     * Execute
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $quote = $this->cart->getQuote();
        $giftCardQuote = $this->giftCardQuoteFactory->create();
        if ($id && $quote) {
            try {
                $giftCardQuote->removeGiftCardQuote($id);
                $quote->collectTotals();
                $this->quoteRepository->save($quote);
                $this->messageManager->addSuccessMessage(
                    __('You remove the coupon code success.')
                );
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('Cannot remove gift card code.'));
            }
        }
        return $this->_goBack();
    }
}
