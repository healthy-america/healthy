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
namespace Bss\GiftCard\Plugin\Model;

use Magento\Framework\Serialize\Serializer\Json as JsonSerialize;

class WishlistItemToCart
{
    /**
     * @var JsonSerialize
     */
    protected $jsonSerialize;

    /**
     * WishlistItemToCart constructor.
     * @param JsonSerialize $jsonSerialize
     */
    public function __construct(
        JsonSerialize $jsonSerialize
    ) {
        $this->jsonSerialize = $jsonSerialize;
    }

    /**
     * Add to cart
     *
     * @param \Magento\Wishlist\Model\Item $item
     * @param \Magento\Checkout\Model\Cart $cart
     * @param bool $delete
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeAddToCart(
        \Magento\Wishlist\Model\Item $item,
        \Magento\Checkout\Model\Cart $cart,
        $delete = false
    ) {
        $product = $item->getProduct();
        if ($product->getTypeId() === "bss_giftcard") {
            $product->getTypeInstance()->validateGiftCard(
                $item->getBuyRequest(),
                $product,
                'full'
            );
        }
        return [
            $cart,
            $delete
        ];
    }
}
