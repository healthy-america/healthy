define([
    'ko',
    'Magento_Checkout/js/view/summary/abstract-total',
    'Magento_Customer/js/customer-data',
    'Magento_Checkout/js/model/quote'
], function (ko, Component, customerData, quote) {
    'use strict';

    let displaySubtotalMode = window.checkoutConfig.reviewTotalsDisplayMode;

    return Component.extend({
        defaults: {
            displaySubtotalMode: displaySubtotalMode,
        },
        totals: quote.getTotals(),

        /**
         * @override
         */
        initialize: function () {
            this._super();
            this.cart = customerData.get('cart');
        },

        /**
         * @return {*|String}
         */
        getValue: function () {
            var price = 0;

            if (this.totals()) {
                price = parseInt(this.totals().subtotal) + this.cart().discount_amount;
            }

            return this.getFormattedPrice(price);
        },

        /**
         * @return {Boolean}
         */
        isBothPricesDisplayed: function () {
            return this.displaySubtotalMode === 'both'; //eslint-disable-line eqeqeq
        },

        /**
         * @return {Boolean}
         */
        isIncludingTaxDisplayed: function () {
            return this.displaySubtotalMode === 'including'; //eslint-disable-line eqeqeq
        },
    });
});
