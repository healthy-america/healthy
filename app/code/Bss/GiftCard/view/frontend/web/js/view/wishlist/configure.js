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
    'underscore'
], function ($, _) {
    'use strict';

    /**
     * @inheritDoc
     */
    $.widget('bss.wishlistConfigure', {
        options: {
            itemOptions: ''
        },
        /**
         * @private
         */
        _create: function () {
            var self = this;
            $('#product_addtocart_form').on('updateContent', function () {
                self._updateOptions();
                self._updateDate();
                self._updateTimezoneSelect();
            });
            $('#bss-giftcard-delivery-date-input').on('updateContent', function () {
                self._updateDate();
            });
            $('#bss-giftcard-timezone-select').on('updateContent', function () {
                self._updateTimezoneSelect();
            });
            this._super();
        },
        /**
         * @private
         */
        _updateOptions: function () {
            var optionsArr = this.options.itemOptions;
            if (undefined !== optionsArr) {
                _.each(optionsArr, function (item, idx) {
                    var itemElem = $('#' + idx);
                    if (itemElem.length) {
                        itemElem.val(item);
                        itemElem.trigger('change');
                        if (idx === 'bss_giftcard_selected_image') {
                            $('#bss_giftcard_image_' + item).trigger('click');
                        }
                    }
                });
            }
        },
        /**
         * @private
         */
        _updateDate: function () {
            var optionsArr = this.options.itemOptions;
            if (undefined !== optionsArr) {
                _.each(optionsArr, function (item, idx) {
                    if (idx === 'bss_giftcard_delivery_date') {
                        $('#bss-giftcard-delivery-date-input').val(item);
                    }
                });
            }
        },
        /**
         * @private
         */
        _updateTimezoneSelect: function () {
            var optionsArr = this.options.itemOptions;
            if (undefined !== optionsArr) {
                _.each(optionsArr, function (item, idx) {
                    if (idx === 'bss_giftcard_timezone') {
                        $('#bss-giftcard-timezone-select').val(item);
                    }
                });
            }
        }
    });

    return $.bss.wishlistConfigure;
});
