<!--
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
-->
var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/action/place-order': {
                'Aventi_CheckoutAdvisor/js/action/place-order-mixin': true
            },
            'Magento_Checkout/js/action/set-payment-information': {
                'Aventi_CheckoutAdvisor/js/action/set-payment-information-mixin': false
            },

        }
    }
};
