/*
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

define([
    'ko',
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Checkout/js/model/totals',
    'Magento_Checkout/js/model/cart/cache'
], function (ko, Component, totals, cartCache) {
        "use strict";
        return Component.extend({
            defaults: {
                template: 'Bss_GiftCard/cart/totals/giftcard'
            },

            totals: totals.totals(),

            getGiftCardInfo: ko.observableArray([]),

            /** @inheritdoc */
            initialize: function () {
                this._super();
            },

            getPureValue: function (amount) {
                var price = 0;
                cartCache.clear('cart-data');
                if (totals && totals.getSegment('bss_giftcard') && amount) {
                    price = parseFloat(-amount);
                }
                return price;
            },

            isDisplayed: function () {
                if (this.isFullMode() && totals.getSegment('bss_giftcard')) {
                    var giftCardDetails = totals.getSegment('bss_giftcard').extension_attributes.bss_giftcard_details;
                    this.getGiftCardInfo(giftCardDetails);
                    return true;
                }
                this.getGiftCardInfo([]);
                return false;
            },

            getValue: function (amount) {
                return this.getFormattedPrice(this.getPureValue(amount));
            }
        });
    }
);


