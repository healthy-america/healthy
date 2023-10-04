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
            this.custom_discount = this.getFormattedPrice(customerData.get('customdiscount')().custom_discount);
        },

        getFormattedPrice: function (price) {
            var priceFormat = {
                decimalSymbol: '.',
                groupLength: 3,
                groupSymbol: ",",
                integerRequired: false,
                pattern: "$%s",
                precision: 2,
                requiredPrecision: 2
            };

            return priceUtils.formatPrice(price, priceFormat);
        }
    });
});
