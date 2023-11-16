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

namespace Bss\GiftCard\Plugin;

/**
 * Class update gift card for order by paypal
 *
 * Bss\GiftCard\Plugin
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class UpdateGiftCardForOrderByPaypal
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * UpdateGiftCardForOrderByPaypal constructor.
     * @param \Magento\Checkout\Model\Session $checkoutSession
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession
    ) {
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * Before Get All Items
     *
     * @param \Magento\Paypal\Model\Cart $cart
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function beforeGetAllItems(\Magento\Paypal\Model\Cart $cart)
    {
        $quote = $this->checkoutSession->getQuote();
        $paymentMethod = $quote->getPayment()->getMethod();
        $paypalMethodList = [
            'payflowpro',
            'payflow_link',
            'payflow_advanced',
            'braintree_paypal',
            'paypal_express_bml',
            'payflow_express_bml',
            'payflow_express',
            'paypal_express'
        ];
        if (!in_array($paymentMethod, $paypalMethodList)) {
            return;
        }
        $giftCardAmount = $quote->getBaseBssGiftcardAmount();
        if ($giftCardAmount && $giftCardAmount > 0) {
            $giftCardAmount = - $giftCardAmount;
            $cart->addCustomItem(__("Gift Card"), 1, $giftCardAmount, 'gift_card');
            $cart->addSubtotal($giftCardAmount);
        }
    }

    /**
     * After Get All Items
     *
     * @param \Magento\Paypal\Model\Cart $cart
     * @param array $result
     * @return mixed
     * @SuppressWarnings(PHPMD.UnusedLocalVariable)
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function afterGetAllItems(
        \Magento\Paypal\Model\Cart $cart,
        $result
    ) {
        if (empty($result)) {
            return $result;
        }
        $found = false;
        foreach ($result as $key => $item) {
            if ($item->getId() != 'gift_card') {
                continue;
            }
            if ($found) {
                unset($result[$key]);
                continue;
            }
            $found = true;
        }
        return $result;
    }
}
