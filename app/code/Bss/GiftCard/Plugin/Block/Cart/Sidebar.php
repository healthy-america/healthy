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
namespace Bss\GiftCard\Plugin\Block\Cart;

use Magento\Checkout\Model\Cart;
use Bss\GiftCard\Model\Product\Type\GiftCard;
use Bss\GiftCard\Helper\Catalog\Product\Configuration;

/**
 * Class sidebar
 *
 * Bss\ConfigurableProductWholesale\Plugin\Block\Cart
 */
class Sidebar
{
    public const ACTIVE_CHECKOUT_CART_CONFIGURE = 'checkout_cart_configure';

    /**
     * @var Cart
     */
    private $cart;

    /**
     * @var Configuration
     */
    private $configurationHelper;

    /**
     * Sidebar constructor.
     * @param Cart $cart
     * @param Configuration $configurationHelper
     */
    public function __construct(
        Cart $cart,
        Configuration $configurationHelper
    ) {
        $this->cart = $cart;
        $this->configurationHelper = $configurationHelper;
    }

    /**
     * Add item edit to json
     *
     * @param \Magento\Checkout\Block\Cart\Sidebar $subject
     * @param \Magento\Checkout\Block\Cart\Sidebar $result
     * @return \Magento\Checkout\Block\Cart\Sidebar
     */
    public function afterGetConfig(\Magento\Checkout\Block\Cart\Sidebar $subject, $result)
    {
        $itemId = (int)$subject->getRequest()->getParam('id');
        $giftCardData = [];
        $fullActionName = $subject->getRequest()->getFullActionName();
        if (!$itemId || $fullActionName != self::ACTIVE_CHECKOUT_CART_CONFIGURE) {
            return $result;
        }
        $item = $this->cart->getQuote()->getItemById($itemId);
        $customOptions = $item->getProduct()->getTypeInstance(true)->getOrderOptions($item->getProduct());
        if (isset($customOptions['info_buyRequest']) && $item->getProduct()->getTypeId() == GiftCard::BSS_GIFT_CARD) {
            foreach ($this->configurationHelper->getBuyRequestOptions() as $optionCode) {
                if (isset($customOptions['info_buyRequest'][$optionCode])) {
                    $giftCardData[$optionCode] = $customOptions['info_buyRequest'][$optionCode];
                }
            }
        }
        $result['bssGiftCardData'] = $giftCardData;
        return $result;
    }
}
