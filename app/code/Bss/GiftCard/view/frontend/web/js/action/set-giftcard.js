/*
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_GiftCard
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

define([
    'ko',
    'jquery',
    'Bss_GiftCard/js/model/resource-url-manager',
    'Magento_Checkout/js/model/error-processor',
    'Bss_GiftCard/js/model/payment/giftcard-messages',
    'mage/storage',
    'mage/translate',
    'Magento_Checkout/js/action/get-payment-information',
    'Magento_Checkout/js/model/totals',
    'Magento_Checkout/js/model/full-screen-loader',
    'Magento_Checkout/js/model/quote',
    'Bss_GiftCard/js/model/cart/code-data',
    'uiRegistry'
], function (
    ko,
    $,
    urlManager,
    errorProcessor,
    messageContainer,
    storage,
    $t,
    getPaymentInformationAction,
    totals,
    fullScreenLoader,
    quote,
    codeData,
    registry
) {
    'use strict';

    return function (giftCardCode) {
        var quoteId = quote.getQuoteId(),
            url = urlManager.getApplyGiftCardUrl(giftCardCode, quoteId),
            message = $t('Your gift card code was successfully applied.');

        fullScreenLoader.startLoader();

        return storage.put(
            url,
            {},
            false
        ).done(function (response) {
            var deferred;
            var giftCardComponent = registry.get('checkout.steps.billing-step.payment.afterMethods.giftcard');

            if (typeof giftCardComponent === "undefined"){
                giftCardComponent = registry.get('checkout.sidebar.klarna_sidebar.giftcard');
            }

            if (response && response.length > 0) {
                deferred = $.Deferred();

                totals.isLoading(true);
                getPaymentInformationAction(deferred);
                $.when(deferred).done(function () {
                    fullScreenLoader.stopLoader();
                    totals.isLoading(false);
                });
                codeData.getGiftCardApply(response);
                giftCardComponent.giftCardCode('');
                messageContainer.addSuccessMessage({
                    'message': message
                });
            }
        }).fail(function (response) {
            fullScreenLoader.stopLoader();
            totals.isLoading(false);
            errorProcessor.process(response, messageContainer);
        });
    };
});


