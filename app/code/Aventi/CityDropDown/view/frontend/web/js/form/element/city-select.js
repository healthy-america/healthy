/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
    'Magento_Checkout/js/model/default-post-code-resolver',
    'jquery',
    'ko',
    'Magento_Checkout/js/model/quote',
    'mage/utils/wrapper',
    'mage/template',
    'mage/validation',
    'underscore',
    'Magento_Ui/js/form/element/abstract',
    'jquery/ui'
], function (_, registry, Select, defaultPostCodeResolver, $, ko, quote) {
    'use strict';

    return Select.extend({
        defaults: {
            shippingForm: 'checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset',
            cartForm: 'block-summary.block-shipping.address-fieldsets',
            skipValidation: false,
            imports: {
                update: '${ $.parentName }.region_id:value'
            },
            options: [{
                value:'',
                title: 'Por favor seleccione una ciudad.',
                label: 'Por favor seleccione una ciudad.'
            }],
        },

        /**
         * @param {String} value
         */
        update: function (value) {
            var self = this,
                cityOptions = [],
                cityValue = this.getDefaultValue(value);
            $('[name="city]').trigger('processStart');
            cityOptions.push(self.getDefaultOptions());
            if (value === undefined || value === '') {
                return;
            }
            $.ajax({
                url: BASE_URL + 'citydropdown/index/index',
                type: "post",
                dataType: "json",
                data: {region_id: value},
                cache: false
            }).done(function (json) {
                $.each(json, function (i, attribute) {
                    let name = attribute.name;
                    let jsonObject = {
                        value: attribute.id+'-'+attribute.postalCode,
                        title: name,
                        label: self.capitalize(name)
                    };
                    cityOptions.push(jsonObject);
                });
                self.setOptions(cityOptions);
                if (cityValue !== null && value !== undefined) {
                    self.value(self.getSelectedCity(cityValue));
                }
                $('[name="city]').trigger('processStop');
            })
        },

        getDefaultOptions: function() {
            return {
                value:'',
                title: 'Por favor seleccione una ciudad.',
                label: 'Por favor seleccione una ciudad.'
            };
        },

        capitalize: function(str) {
            const lower = str.toLowerCase();
            return str.charAt(0).toUpperCase() + lower.slice(1);
        },

        getDefaultValue: function(region) {
            let address = null;
            if (region !== '' &&  region !== undefined) {
                if (this.parentName === this.shippingForm || this.parentName === this.cartForm) {
                    address = quote.shippingAddress();
                } else {
                    address = quote.billingAddress();
                }
            }
            return address ? address.city : null;
        },

        getSelectedCity: function (city) {
            var _key = null;

            if (!city) {
                return _key;
            }

            city = city.toUpperCase();

            $.each(this.indexedOptions, function( key, value ) {
                if (value['title'] === city) {
                    _key = key;
                    return false;
                }
            });
            return _key;
        }
    });
});
