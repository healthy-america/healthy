<!--
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
-->
define([
    'jquery',
    'ko',
    'uiComponent',
    'Magento_Checkout/js/model/quote',
    'Aventi_CheckoutAdvisor/js/model/advisor-model'
], function ($, ko, Component, quote, advisor) {
    'use strict';

    var totals = quote.getTotals(),
        advisorValue = advisor.getAdvisor();

    return Component.extend({
        defaults: {
            template: 'Aventi_CheckoutAdvisor/payment/advisor',
        },
        advisor: advisorValue,
    });
});
