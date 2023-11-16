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
    'Magento_Checkout/js/model/error-processor',
    'Bss_GiftCard/js/model/payment/giftcard-messages',
    'mage/storage',
    'mage/translate',
    'Magento_Checkout/js/action/get-payment-information',
    'Magento_Checkout/js/model/totals',
    'Magento_Checkout/js/model/full-screen-loader',
    'Bss_GiftCard/js/model/resource-url-manager',
    'Magento_Checkout/js/model/quote',
    'Bss_GiftCard/js/view/cart/giftcard-apply',
    'Bss_GiftCard/js/model/cart/code-data'
], function (
    ko,
    $,
    errorProcessor,
    messageContainer,
    storage,
    $t,
    getPaymentInformationAction,
    totals,
    fullScreenLoader,
    urlManager,
    quote,
    giftCardApply,
    codeData
) {
    'use strict';

    return function (giftCardId) {
        var quoteId = quote.getQuoteId(),
            url = urlManager.getCancelGiftCardUrl(giftCardId, quoteId),
            message = $t('Your gift card code was successfully removed.');

        fullScreenLoader.startLoader();

        return storage.delete(
            url,
            false
        ).done(function (response) {
            var deferred = $.Deferred();
            totals.isLoading(true);
            getPaymentInformationAction(deferred);
            $.when(deferred).done(function () {
                totals.isLoading(false);
                fullScreenLoader.stopLoader();
            });
            if (response && $.isArray(response)) {
                codeData.getGiftCardApply(response);
            }
            messageContainer.addSuccessMessage({
                'message': message
            });
        }).fail( function (response) {
            totals.isLoading(false);
            fullScreenLoader.stopLoader();
            errorProcessor.process(response, messageContainer);
        });
    };
});


