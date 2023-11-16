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
    'Magento_Customer/js/model/customer',
    'Magento_Checkout/js/model/url-builder',
    'mageUtils'
], function (customer, urlBuilder, utils) {
        'use strict';

        return {
            /**
             * @param {String} giftCardCode
             * @param {String} quoteId
             * @return {*}
             */
            getApplyGiftCardUrl: function (giftCardCode, quoteId) {
                var params = {
                        quoteId: quoteId,
                        giftCardCode: giftCardCode
                    },
                    urls = {
                        'default': '/bssGiftCard/apply/:quoteId/:giftCardCode'
                    };

                return this.getUrl(urls, params);
            },

            /**
             * @param {Int} giftCardId
             * @param {String} quoteId
             * @return {*}
             */
            getCancelGiftCardUrl: function (giftCardId, quoteId) {
                var params = {
                        cartId: quoteId,
                        giftCardQuoteId: giftCardId
                    },
                    urls = {
                        'default': '/bssGiftCard/guest/remove/:cartId/:giftCardQuoteId'
                    };

                return this.getUrl(urls, params);
            },

            getCheckGiftCardUrl: function () {
                var urls = {
                        'default': '/bssGiftCard/checkCode'
                    };

                return this.getUrl(urls, {});
            },

            /**
             * Get url for service.
             *
             * @param {*} urls
             * @param {*} urlParams
             * @return {String|*}
             */
            getUrl: function (urls, urlParams) {
                var url;

                if (utils.isEmpty(urls)) {
                    return 'Provided service call does not exist.';
                }

                if (!utils.isEmpty(urls['default'])) {
                    url = urls['default'];
                } else {
                    url = urls[this.getCheckoutMethod()];
                }

                return urlBuilder.createUrl(url, urlParams);
            },

            /**
             * @return {String}
             */
            getCheckoutMethod: function () {
                return customer.isLoggedIn() ? 'customer' : 'guest';
            }
        };
    }
);


