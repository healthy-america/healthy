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
    'jquery',
    'mage/storage',
    'Bss_GiftCard/js/model/resource-url-manager',
    'Bss_GiftCard/js/model/cart/code-data',
    'Magento_Ui/js/modal/alert',
    'mage/translate',
    'Bss_GiftCard/js/model/cart/full-screen-loader'
], function ($, storage, urlManager, codeData, alert, $t, fullScreenLoader) {
    "use strict";

    return function (giftCardCode) {
        fullScreenLoader.startLoader();
        codeData.data([]);
        var url = urlManager.getCheckGiftCardUrl();

        return storage.post(
            url,
            JSON.stringify({
                code: giftCardCode
            }),
            false
        ).done(function (response) {
            if (response[0]) {
                response = response[0];
            }
            if (response.status) {
                codeData.data(response.data);
            } else if (!response.status) {
                alert({
                    title: $t('Note'),
                    content: response.data
                });
            } else {
                alert({
                    title: $t('Note'),
                    content: $t('Can not check.')
                });
            }
            fullScreenLoader.stopLoader();
            }
        ).fail(function (response) {
            fullScreenLoader.stopLoader();
            alert({
                title: $t('Note'),
                content: $t('Fail'),
            });
        });
    };
});


