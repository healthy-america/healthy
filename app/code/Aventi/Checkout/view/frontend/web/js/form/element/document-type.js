define([
    'underscore',
    'uiRegistry',
    'Magento_Ui/js/form/element/select',
    'Magento_Checkout/js/model/default-post-code-resolver',
    'jquery',
    'ko',
    'mage/translate'
], function (_, registry, Select, defaultPostCodeResolver, $, ko, $t) {
    'use strict';

    return Select.extend({
        defaults: {
            captionDocumentType: $t('Please select a document type'),
            naturalDocumentType: {
                'CC': $t('Identification card'),
                'CE': $t('Foreigner ID'),
                'RUT': $t('RUT')
            },
            legalDocumentType: {
                'RUT': $t('RUT'),
            },
            enableSelect: ko.observable(),
            customerDocumentTypeOptions: ko.observableArray([])
        },

        initialize: function () {
            this._super();
            this.enableSelect(false);
            this.tributaryInfoField = registry.get('checkout.steps.shipping-step.shippingAddress.shipping-address-fieldset.company');
            this.tributaryInfoField.hide();
            return this;
        },

        onUpdate: function (value) {
            this._super();
            this.getDocumentTypes()
            this.enableTributaryField()
        },

        getDocumentTypes: function () {
            let self = this;
            const customerType = this.value();

            // Check if the selected value is the same as the caption value
            if (!customerType) {
                this.enableSelect(false);
                return;
            }

            switch (customerType) {
                case 'Natural':
                    self.setDocumentTypes(self.naturalDocumentType);
                    break;
                case 'Legal':
                    self.setDocumentTypes(self.legalDocumentType);
                    break;
            }
            this.enableSelect(true);
        },

        selectOption: function (_name, _value) {
            this.label = ko.observable(_name);
            this.value = ko.observable(_value);
            return this;
        },

        setDocumentTypes: function (documentTypes) {
            let self = this;
            let documentTypeOptions = [];

            $.each(documentTypes, function (key, value) {
                documentTypeOptions.push(new self.selectOption(value, key));
            });

            self.customerDocumentTypeOptions(documentTypeOptions);
        },

        enableTributaryField: function () {
            const customerType = this.value();

            if(customerType === 'Legal'){
                this.tributaryInfoField.show();
            }else{
                this.tributaryInfoField.hide();
            }
        }
    });
});
