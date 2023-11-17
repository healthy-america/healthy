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
    return function (addToWishlist) {
        $.widget('mage.addToWishlist', addToWishlist, {
            options: {
                bssGiftCardElements: [
                    'change#bss_giftcard_amount',
                    'change#bss_giftcard_amount_dynamic',
                    'change#bss_giftcard_sender_name',
                    'change#bss_giftcard_recipient_name',
                    'change#bss_giftcard_sender_email',
                    'change#bss_giftcard_recipient_email',
                    'change#bss_giftcard_message_email',
                    'change#bss_giftcard_delivery_date',
                    'change#bss_giftcard_template',
                    'change#bss_giftcard_timezone',
                    'change#bss_giftcard_selected_image',
                    'click.bss-giftcard-template-images',
                    'change#bss-giftcard-delivery-date-input'
                ]
            },
            /**
             * @private
             */
            _bind: function () {
                this._super();
                var productTypes = this.options.productType;
                if (productTypes.indexOf('bss_giftcard') !== -1) {
                    var events = {},
                        dataUpdateFunc = '_updateWishlistData';

                    _.each(this.options.bssGiftCardElements, function (elem, idx) {
                        events[elem] = dataUpdateFunc;
                    });
                    this._on(events);
                }
            },
            /**
             * @param dataAdd
             * @private
             */
            _updateAddToWishlistButton: function (dataAdd, e) {
                var selectedImageElem = $('#bss_giftcard_selected_image'),
                    deliveryDateElem = $('[name="bss_giftcard_delivery_date"]');
                if (selectedImageElem.length) {
                    dataAdd.bss_giftcard_selected_image = selectedImageElem.val();
                }
                if (deliveryDateElem.length) {
                    dataAdd.bss_giftcard_delivery_date = deliveryDateElem.val();
                }
                this._super(dataAdd, e);
            }
        });

        return $.mage.addToWishlist;
    }
});
