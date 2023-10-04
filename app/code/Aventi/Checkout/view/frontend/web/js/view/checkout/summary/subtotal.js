define([
    'ko',
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'Magento_Catalog/js/price-utils',
    'Magento_Checkout/js/model/quote'
], function (ko, Component, customerData, priceUtils, quote) {
    'use strict';

    return Component.extend({
        /**
         * @override
         */
        initialize: function () {
            this._super();
            this.summary_custom_discount = priceUtils.formatPrice(customerData.get('customdiscount')().custom_discount, quote.getPriceFormat());
        },
    });
});
