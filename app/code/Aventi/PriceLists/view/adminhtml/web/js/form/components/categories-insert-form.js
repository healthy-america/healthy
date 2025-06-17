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
                priceListCategoriesListing: '${ $.priceListCategoriesListingProvider }',
                priceListCategoriesModal: '${ $.priceListCategoriesModalProvider }'
            }
        },

        /**
         * Close modal, reload customer address listing and save customer address
         *
         * @param {Object} responseData
         */
        onResponse: function (responseData) {
            if (!responseData.error) {
                this.priceListCategoriesModal().closeModal();
                this.priceListCategoriesListing().reload({
                    refresh: true
                });
            }
        },
    });
});
