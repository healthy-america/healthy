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
    'jquery',
    'uiComponent',
    'underscore',
    'ko',
    'Bss_GiftCard/js/action/cart/check-code',
    'Bss_GiftCard/js/model/cart/code-data',
    'Bss_GiftCard/js/action/remove-giftcard',
], function (
    $,
    Component,
    _,
    ko,
    checkCode,
    codeData,
    removeGiftcardAction
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Bss_GiftCard/cart/giftcard-apply'
        },

        getGiftCard: function (giftCardApply) {
            return codeData.getGiftCardApply();
        },

        initialize: function () {
            this._super();
            var giftCardApply = window.checkoutConfig.bssGiftCard;
            if (!_.isUndefined(giftCardApply)) {
                codeData.getGiftCardApply(giftCardApply);
            }
        },

        isDisplayed: function () {
            return true;//!_.isUndefined(codeData.data());
        },

        remove: function (giftCardCode) {
            if (giftCardCode && !_.isUndefined(giftCardCode.id)) {
                removeGiftcardAction(giftCardCode.id);
            }
        }
    });
});

