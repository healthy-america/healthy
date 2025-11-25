define([
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'underscore',
    'ko'
], function(
    Component,
    customerData,
    _,
    ko
) {
    'use strict';
    
    return Component.extend({
        defaults: { 
            subtotal: 0,
            template: 'Healthy_FreeShippingBlock/free-shipping-banner',
            tracks: {
                subtotal: true
            }
        },
        initialize: function() {
            this._super();

            var self = this;
            var cart = customerData.get('cart');

            customerData.getInitCustomerData().done(function() {
                if (!_.isEmpty(cart()) && !_.isUndefined(cart().subtotalAmount)) {
                    self.subtotal = parseFloat(cart().subtotalAmount);
                }
            });

            cart.subscribe(function(cart) {
                if (!_.isEmpty(cart) && !_.isUndefined(cart.subtotalAmount)) {
                    self.subtotal = parseFloat(cart.subtotalAmount)
                }
            });

            self.message = ko.pureComputed(function() {
                if (_.isUndefined(self.subtotal) || self.subtotal === 0) {
                    return self.messageDefault.replace('$x.xxx', self.formatCurrency(self.freeShippingThreshold));
                }

                if (self.subtotal > 0 && self.subtotal < self.freeShippingThreshold) {
                    var subtotalRemaining = self.freeShippingThreshold - self.subtotal;
                    var formattedSubtotalRamining = self.formatCurrency(subtotalRemaining);
                    return self.messageItemsInCart.replace('$x.xxx', formattedSubtotalRamining);
                }

                if (self.subtotal >= self.freeShippingThreshold) {
                    return self.messageFreeShipping
                }
            });

            self.isFreeShipping = ko.pureComputed(function () {
                return self.subtotal >= self.freeShippingThreshold;
            });

            self.isFreeShipping2 = ko.pureComputed(function () {
                return self.subtotal > 0 && self.subtotal < self.freeShippingThreshold;
            });

            self.isFreeShipping1 = ko.pureComputed(function () {
                return _.isUndefined(self.subtotal) || self.subtotal === 0;
            });

        },

        formatCurrency: function(value) {
            return new Intl.NumberFormat('es-CO', {
                style: 'currency',
                currency: 'COP',
                minimumFractionDigits: 0
            }).format(value);
        }
    });
});