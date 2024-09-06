/**
 * Copyright © Aventi, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'uiComponent',
    'Magento_Checkout/js/model/shipping-rates-validator',
    'Magento_Checkout/js/model/shipping-rates-validation-rules',
    'Aventi_Servientrega/js/model/shipping-rates-validator',
    'Aventi_Servientrega/js/model/shipping-rates-validation-rules'
], function (
    Component,
    defaultShippingRatesValidator,
    defaultShippingRatesValidationRules,
    servientregaShippingRatesValidator,
    servientregaShippingRatesValidationRules
) {
    'use strict';

    defaultShippingRatesValidator.registerValidator('servientrega', servientregaShippingRatesValidator);
    defaultShippingRatesValidationRules.registerRules('servientrega', servientregaShippingRatesValidationRules);

    return Component;
});
