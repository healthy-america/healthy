<!--
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
-->
define([
    'jquery',
    'mage/utils/wrapper'
], function ($, wrapper) {
    'use strict';

    return function (placeOrderAction) {

        /** Override place-order-mixin for set-payment-information action as they differs only by method signature */
        return wrapper.wrap(placeOrderAction, function (originalAction, paymentData, messageContainer) {
            if (paymentData['additional_data'] === undefined || paymentData['additional_data'] == null) {
                paymentData['additional_data'] = {};
            }
            paymentData['additional_data']['payment_advisor'] = $('#payment_advisor').val();


            return originalAction(paymentData, messageContainer);
        });
    };
});
