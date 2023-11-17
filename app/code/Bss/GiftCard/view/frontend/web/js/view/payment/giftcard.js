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
 * @copyright  Copyright (c) 2017-2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

define(
    [
        'jquery',
        'ko',
        'uiComponent',
        'Bss_GiftCard/js/action/set-giftcard',
        'Bss_GiftCard/js/action/cart/check-code'
    ],
    function ($, ko, Component, setGiftCardAction, checkCodeAction) {
        'use strict';

        var data = window.checkoutConfig,

            giftCardCode = ko.observable(null);

        return Component.extend({
            defaults: {
                template: 'Bss_GiftCard/payment/giftcard'
            },

            giftCardCode: giftCardCode,
            apply: function () {
                if (this.validate()) {
                    setGiftCardAction(this.giftCardCode());
                }
            },

            checkStatus: function () {
                if (this.validate()) {
                    checkCodeAction(this.giftCardCode());
                }
            },

            validate: function () {
                var form = '#bss-giftcard-form';

                return $(form).validation() && $(form).validation('isValid');
            },

            isDisplayed: function () {
                return data['isEnabled'];
            }
        });
    }
);


