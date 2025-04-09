/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
/*browser:true*/
/*global define*/
define(['Magento_Checkout/js/view/payment/default', 'Magento_Checkout/js/model/quote', 'mage/url', 'jquery', 'ko',], function (Component, quote, url, $, ko) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'MagentoSistecredito_SistecreditoPaymentGateway/payment/form',
            transactionResult: '',
            gatewayError$: null,
            idType: 'CC',
            idNumber: '',
            nameProduct: 'Checkout'
        },

        sistecreditoLogoSrc: window.populateSistecredito.logoImageUrl,

        redirectAfterPlaceOrder: false,

        initObservable: function () {
            this.errorMessages = ko.observableArray([]);
            this._super()
                .observe(['transactionResult', 'idType', 'gatewayError$', 'idNumber', 'nameProduct']);
            return this;
        },

        getCode: function () {
            return 'sistecredito_gateway';
        },

        getOnSameSite: function () {
            return window.checkoutConfig.payment.sistecredito_gateway.onSameSite;
        },

        getDataKey: function () {
            return window.checkoutConfig.payment.sistecredito_gateway.dataKey;
        },

        getVisorJs: function () {
            return window.checkoutConfig.payment.sistecredito_gateway.visorJs;
        },

        getVendorId: function () {
            return window.checkoutConfig.payment.sistecredito_gateway.vendorId;
        },

        getStoreId: function () {
            return window.checkoutConfig.payment.sistecredito_gateway.storeId;
        },

        getOrderId: function () {
            return window.checkoutConfig.payment.sistecredito_gateway.orderId;
        },

        getResponseUrl: function () {
            return window.checkoutConfig.payment.sistecredito_gateway.responseUrl;
        },

        getAuthentication: function () {
            return window.checkoutConfig.payment.sistecredito_gateway.authentication;
        },

        getEnvironment: function () {
            return window.checkoutConfig.payment.sistecredito_gateway.environment;
        },

        getStoreApps: function () {
            return window.checkoutConfig.payment.sistecredito_gateway.storeApps;
        },

        getSubscriptionKeyVisor: function () {
            return window.checkoutConfig.payment.sistecredito_gateway.subscriptionKeyVisor;
        },
        getUrlReturn: function (){
            return window.checkoutConfig.payment.sistecredito_gateway.urlReturn
        },

        getData: function () {
            return {
                'method': this.item.method, 'additional_data': {
                    'transaction_result': this.transactionResult(),
                    'id_type': this.idType(),
                    'id_number': this.idNumber()
                }
            };
        },

        getIdTypes: function () {
            return _.map(window.checkoutConfig.payment.sistecredito_gateway.idTypes, function (value, key) {
                return {
                    'value': key, 'id_type': value
                }
            });
        },

        afterPlaceOrder: async function () {
            let self = this;

            if (self.getDataKey()) {
                await self.validateWidget().then(function (response) {
                    self.widgetTransaction()
                }).catch(function (error) {
                    self.redirectTransaction();
                })
            } else {
                self.redirectTransaction();
            }
        },

        widgetTransaction: async function () {

            let self = this;
            let checkout = document.getElementById(self.nameProduct());
            let options = JSON.parse(checkout.attributes.options.value)
            options.typeDocument = self.idType();
            options.idDocument = self.idNumber();

            await $.ajax({
                url: url.build(`sistecredito/gateway/gatewayurl?typeDocument=${self.idType()}&idDocument=${self.idNumber()}&onSameSite=true`),
                headers: {
                    'X-Requested-With': 'XMLHttpRequest', 'Content-Type': 'application/x-www-form-urlencoded'
                },
                method: 'GET',
                async: false,
                success: function (data) {
                    options.orderId = data.orderId;
                    options.authentication = data.authentication;
                    checkout.setAttribute("options", JSON.stringify(options));

                    //Open app visor with product, Checkout.
                    let opener = new CustomEvent("sc:visor:open", {
                        detail: self.nameProduct(),
                    });
                    document.dispatchEvent(opener);
                },
                error: function (error) {
                    console.log('error: ' + error);
                    window.location.replace(url.build("checkout/cart"));
                }
            })
        },

        redirectTransaction: function () {
            const self = this;
            window.location.replace(url.build(`sistecredito/gateway/gatewayurl?typeDocument=${self.idType()}&idDocument=${self.idNumber()}`));
        },

        validateWidget: async function () {

            const self = this;
            await $.ajax({
                url: `${self.getStoreApps()}${self.getDataKey()}`,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'Ocp-Apim-Subscription-Key': self.getSubscriptionKeyVisor(),
                    'SCLocation': '0,0',
                    'SCOrigen': self.getEnvironment(),
                    'country': 'co',
                },
                method: 'GET',
                async: false,
                success: function (data) {
                    console.log(data)
                    return data.errorCode === 0;
                },
                error: function (error) {
                    console.log(error)
                    return false;
                }
            })
        },

        createScriptHtml: async function () {
            if (this.getDataKey()) {

                const existsScriptTag = document.querySelector('#visor');

                const scriptTag = document.createElement("script");
                scriptTag.setAttribute('id', 'visor');
                scriptTag.setAttribute('data-key', this.getDataKey());
                scriptTag.setAttribute('src', this.getVisorJs());

                if (existsScriptTag) {
                    existsScriptTag.remove();
                }

                document.querySelector("head").appendChild(scriptTag);

                //Crear elementos para la carga del script del visor
                await this.createAppVisorHtml();
            }
        },

        createAppVisorHtml: function () {
            const divAppVisor = document.querySelector('#app-visor');

            const appVisor = document.createElement("app-visor");
            appVisor.setAttribute('id', this.nameProduct());
            appVisor.setAttribute('app', this.nameProduct());

            const totals = quote.getTotals();
            let valueToPaid = 0;
            valueToPaid = totals._latestValue.base_grand_total;

            let dataCheckout = {
                idDocument: "00000000",
                typeDocument: this.idType(),
                valueToPaid,
                vendorId: this.getVendorId(),
                orderId: this.getOrderId(),
                storeId: this.getStoreId(),
                responseUrl: this.getResponseUrl(),
                authentication: this.getAuthentication(),
                externalPageRedirection: this.getOnSameSite(),
                buttonFloating: "false",
                defaultButtonHidden: "true",
                openModal: "true"
            };

            appVisor.setAttribute("options", JSON.stringify(dataCheckout));

            divAppVisor.appendChild(appVisor);

        },
    });
});
