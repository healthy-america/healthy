/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/components/insert-form'
], function (Insert) {
    'use strict';

    return Insert.extend({
        defaults: {
            listens: {
                responseData: 'onResponse'
            },
            modules: {
                priceListCustomersListing: '${ $.priceListCustomersListingProvider }',
                priceListCustomersModal: '${ $.priceListCustomersModalProvider }'
            }
        },

        /**
         * Close modal, reload customer address listing and save customer address
         *
         * @param {Object} responseData
         */
        onResponse: function (responseData) {
            if (!responseData.error) {
                this.priceListCustomersModal().closeModal();
                this.priceListCustomersListing().reload({
                    refresh: true
                });
            }
        },
    });
});
