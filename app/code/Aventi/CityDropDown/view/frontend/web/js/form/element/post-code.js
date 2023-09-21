/**
 * @api
 */
define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/post-code'
], function (_, registry, Abstract) {
    'use strict';

    return Abstract.extend({
        defaults: {
            shippingForm: 'checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset',
            imports: {
                update: '${ $.parentName }.city_id:value'
            }
        },

        /**
         * Initializes observable properties of instance
         *
         * @returns {Abstract} Chainable.
         */
        initObservable: function () {
            this._super();

            /**
             * equalityComparer function
             *
             * @returns boolean.
             */
            this.value.equalityComparer = function (oldValue, newValue) {
                return !oldValue && !newValue || oldValue === newValue;
            };

            return this;
        },

        /**
         * @param {String} value
         */
        update: function (value) {
            let valSplit = null;
            let postcode = '';

            if (window.location.hash === '#payment' && this.parentName === this.shippingForm) {
                return;
            }

            if (!value || value === '') {
                this.value('');
                return;
            }

            valSplit = value.split('-');
            postcode = valSplit.length === 2 ? valSplit[1] : '';
            this.value(postcode);
        }
    });
});
