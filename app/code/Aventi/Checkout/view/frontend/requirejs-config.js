/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
var config = {
    config: {
        mixins: {
            'Magento_Checkout/js/view/shipping': {
                'Aventi_Checkout/js/checkout-phone-validation': true
            },
            'Magento_Checkout/js/view/billing-address': {
                'Aventi_Checkout/js/checkout-phone-validation': true
            },
            'Magento_Ui/js/lib/validation/validator': {
                'Aventi_Checkout/js/document-validation': true
            }
        }
    }
};
