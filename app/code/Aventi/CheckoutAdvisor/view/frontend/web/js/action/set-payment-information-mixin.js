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

    return function (setPaymentInformationAction) {

        /** Override place-order-mixin for set-payment-information action as they differ only by method signature */
        return wrapper.wrap(setPaymentInformationAction, function (originalAction, messageContainer, paymentData) {
            if (paymentData['additional_data'] === undefined) {
                paymentData['additional_data'] = {};
            }
            paymentData['additional_data']['payment_advisor'] = $('#payment_advisor').val();

            return originalAction(messageContainer, paymentData);
        });
    };
});
