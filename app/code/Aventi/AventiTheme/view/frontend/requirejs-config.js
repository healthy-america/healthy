var config = {
    map: {
        '*': {
            'Magento_Checkout/template/billing-address/details':
                'Aventi_AventiTheme/template/billing-address/details',
            'Magento_Checkout/template/shipping-address/address-renderer':
                'Aventi_AventiTheme/template/shipping-address/address-renderer',
        }
    },
    config: {
        mixins: {
            'Lillik_PriceDecimal/js/price-utils': {
                'Aventi_AventiTheme/js/price-utils': true
            }
        }
    }
};
