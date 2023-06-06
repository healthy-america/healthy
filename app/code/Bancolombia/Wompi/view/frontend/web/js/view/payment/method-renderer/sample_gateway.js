/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(
    [
        'Magento_Checkout/js/view/payment/default',
        'Magento_Checkout/js/action/redirect-on-success',
        'mage/url'
    ],
    function (Component, redirectOnSuccessAction, url) {
        'use strict';

        return Component.extend({
            defaults: {
                template: 'Bancolombia_Wompi/payment/form',
                transactionResult: ''
            },

            afterPlaceOrder: function () {
                redirectOnSuccessAction.redirectUrl = url.build('pago/checkout/start');
                this.redirectAfterPlaceOrder = true;
            }
        });
    }
);