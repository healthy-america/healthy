define([
    'uiComponent',
    'Magento_Customer/js/customer-data',
    'ko'
], function (
    Component,
    customerData,
    ko
) {
    'use strict';

    return Component.extend({
        defaults: {
            subtotal: 0,
            freeShippingThreshold: 200000,
            template: 'Healthy_FreeShippingBlock/free-shipping-banner',
            tracks: {
                subtotal: true
            }
        },

        initialize: function () {
            this._super();

            const cart = customerData.get('cart');

            // Carga inicial
            this.updateSubtotal(cart());

            // Actualiza cuando cambia
            cart.subscribe(this.updateSubtotal.bind(this));

            this.message = ko.pureComputed(this.getMessage.bind(this));

            this.freeStatus = ko.pureComputed(this.getFreeStatus.bind(this));

            return this;
        },

        updateSubtotal: function (cart) {
            this.subtotal = parseFloat(cart?.subtotalAmount || 0);
        },

        getMessage: function () {
            if (this.freeStatus() === 'empty') {
                return this.messageDefault.replace('$x.xxx', this.formatCurrency(this.freeShippingThreshold));
            }

            if (this.freeStatus() === 'partial') {
                const remaining = this.freeShippingThreshold - this.subtotal;
                return this.messageItemsInCart.replace('$x.xxx', this.formatCurrency(remaining));
            }

            return this.messageFreeShipping;
        },

        // Un solo computed para clases CSS
        getFreeStatus: function () {
            if (this.subtotal === 0) return 'empty';
            if (this.subtotal < this.freeShippingThreshold) return 'partial';
            return 'free';
        },

        formatCurrency: function (value) {
            return new Intl.NumberFormat('es-CO', {
                style: 'currency',
                currency: 'COP',
                minimumFractionDigits: 0
            }).format(value);
        }
    });
});
