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
    'Bss_GiftCard/js/model/cart/full-screen-loader',
    'mage/url',
    'mage/template',
    'Magento_Ui/js/modal/alert',
    'mage/translate'
], function ($, fullScreenLoader, url, mageTemplate, alert, $t) {
    "use strict";

    $.widget('bss.giftcard_details', {
        _create: function () {
            var self = this;
            this.element.submit(function (e) {
                e.preventDefault();
                self.getGiftCardDetails();
            });
        },

        getGiftCardDetails: function () {
            var self = this,
                ajaxUrl = url.build('giftcard/customer/giftcarddetails');
            fullScreenLoader.startLoader();
            $('.bss-giftcard-details').html('');
            $.ajax({
                type: 'post',
                url: ajaxUrl,
                data: self.element.serialize(),
                dataType: 'json',
                success: function (data) {
                    if (data.success) {
                        var html = mageTemplate('#bss-giftcard-details',{data: data.content});
                        $('.bss-giftcard-details').html(html);
                    } else {
                        alert({
                            title: $t('Note'),
                            content: data.message
                        });
                    }
                    fullScreenLoader.stopLoader();
                },
                error: function () {
                    alert({
                        title: $t('Note'),
                        content: $t('Fails.')
                    });
                    fullScreenLoader.stopLoader();
                }
            });
        }
    });

    return $.bss.giftcard_details;
});

