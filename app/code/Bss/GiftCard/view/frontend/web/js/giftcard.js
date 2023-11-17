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
    'Bss_GiftCard/js/action/cart/check-code'
], function ($, checkCode) {
    "use strict";

    $.widget('bss.giftcard', {
        options: {
            bssGiftcardCode : '#bss-giftcard-code',
            bssGiftcardRemove : '#remove-bss-giftcard',
            bssGiftcardApply : 'button.action.bss-giftcard-apply',
            bssGiftcardCheckBtn : 'button.action.bss-giftcard-check'
        },
        _create: function () {
            this.bssGiftcardCode = $(this.options.bssGiftcardCode);
            this.bssGiftcardRemove = $(this.options.bssGiftcardRemove);

            $(this.options.bssGiftcardApply).on('click', $.proxy(function () {
                this.bssGiftcardCode.attr('data-validate', '{required:true}');
                this.bssGiftcardRemove.attr('value', '0');
                $(this.element).validation().submit();
            }, this));

            $(this.options.bssGiftcardCheckBtn).on('click', $.proxy(function () {
                this.bssGiftcardCode.attr('data-validate', '{required:true}');
                var code = this.bssGiftcardCode.val();
                if ($(this.element).validation()) {
                    checkCode(code);
                }
            }, this));
        }
    });

    return $.bss.giftcard;
});


