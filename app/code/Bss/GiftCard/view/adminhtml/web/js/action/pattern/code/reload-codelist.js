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
    'Magento_Ui/js/modal/alert',
    'mage/translate'
], function ($, alert, $t) {
    'use strict';

    return function (id, qty, amount, obj, expiry) {

        return $.ajax({
            url: obj.generateUrl,
            data: {
                id: id,
                qty: qty,
                amount: amount,
                expiry: expiry
            },
            dataType: 'json',
            showLoader: true,
            success: function (result) {
                if (!result.status) {
                    alert({
                        title: $t('Error'),
                        content: result.message
                    });
                } else {
                    alert({
                        title: $t('Success'),
                        content: result.message,
                        actions: {
                            cancel: function () {
                                $('[name="pattern_code_qty"]').val(result.totalQty);
                                $('[name="pattern_code_unused"]').val(result.totalQtyUnused);
                                eval(obj.jsObjectName).reload();
                            }
                        }
                    });
                }
            },
            error: function () {
                alert({
                    title: $t('Error'),
                    content: $t('Please enter again')
                });
            }
        });
    };
});

