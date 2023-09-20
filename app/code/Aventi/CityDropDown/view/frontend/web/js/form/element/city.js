/**
 * @api
 */
define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/abstract'
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
            let cities = registry.get(this.parentName + '.' + 'city_id');
            let options = cities['indexedOptions'];
            let option = null;

            if (window.location.hash === '#payment' && this.parentName === this.shippingForm) {
                //city.setValue(quote.shippingAddress().city);
                return;
            }

            if (!value || value === '') {
                this.value('');
                return;
            }
            option = options[value];
            if (option !== undefined) {
                this.value(option['title']);
                //this.value(option['labeltitle']);
            }
        }
    });
});
