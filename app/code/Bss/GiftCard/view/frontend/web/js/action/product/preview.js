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
    'mage/url',
    'Magento_Ui/js/modal/modal',
    'Magento_Ui/js/modal/alert',
    'mage/translate'
], function ($, storage, urlBuilder, modal, alert, $t) {
    'use strict';

    return function () {

        var popupId = '#bss-giftcard-email-popup',
            options = {
                type: 'popup',
                responsive: true,
                innerScroll: true
            },
            popup = modal(options, $(popupId));

        return $.ajax({
            url: urlBuilder.build('giftcard/product/preview'),
            data: {
                formData: $('#product_addtocart_form').serializeArray()
            },
            dataType: 'json',
            showLoader: true,
            success: function (res) {
                if (res.success) {
                    $(popupId).empty().html(res.content);
                    $(popupId).modal('openModal');
                    $(popupId).data('mage-modal').modal.on('modalclosed', function(){
                        $(popupId).empty();
                    })
                } else {
                    alert({
                        content: res.content
                    });
                }
            }
        });
    };
});

