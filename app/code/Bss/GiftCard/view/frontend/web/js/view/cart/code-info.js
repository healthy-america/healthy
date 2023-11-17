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
    'Bss_GiftCard/js/model/cart/code-data'
], function (
    $,
    Component,
    _,
    ko,
    checkCode,
    codeData
) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Bss_GiftCard/cart/code-info'
        },

        initialize: function () {
            this._super();
        },

        isDisplay: function () {
            return !_.isUndefined(codeData.data());
        },

        getCode: function () {
            return codeData.data();
        }
    });
});

