/**
 * Copyright © 2023 Aventi, SAS. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'ko',
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'Magento_Catalog/js/price-utils'
], function (ko, Component, customerData, priceUtils) {
    'use strict';

    return Component.extend({
        /**
         * @override
         */
        initialize: function () {
            this._super();
            this.cart = customerData.get('cart');
            this.discount = this.getFormattedPrice(this.cart().discount_amount);
        },

        /**
         * @param price
         * @returns {*}
         */
        getFormattedPrice: function (price) {
            const priceFormat = {
                decimalSymbol: '.',
                groupLength: 3,
                groupSymbol: ",",
                integerRequired: false,
                pattern: "$ %s",
                precision: 2,
                requiredPrecision: 2
            };
            return priceUtils.formatPrice(price, priceFormat);
        }
    });
});
