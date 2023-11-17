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
    'Magento_Ui/js/form/element/abstract',
    'mageUtils',
    'jquery',
    'Bss_GiftCard/js/lib/colorpicker'
], function (Component, utils, $) {
    'use strict';

    return Component.extend({
        defaults: {
            visible: true,
            label: '',
            error: '',
            uid: utils.uniqueid(),
            disabled: false,
            links: {
                value: '${ $.provider }:${ $.dataScope }'
            }
        },

        initialize: function () {
            this._super();
        },

        initColorPickerCallback: function (element) {
            var self = this,
                value = $(element).val();
            $(element).css('backgroundColor', '#' + value);
            $(element).ColorPicker({
                onSubmit: function(hsb, hex, rgb, el) {
                    self.value(hex);
                    $(el).css('backgroundColor', '#' + hex);
                    $(el).ColorPickerHide();
                },
                onBeforeShow: function () {
                    $(this).ColorPickerSetColor(this.value);
                },
                onChange: function (hsb, hex, rgb, m) {
                    $(this).css('backgroundColor', '#' + hex);
                }
            }).bind('keyup', function(){
                $(this).ColorPickerSetColor(this.value);
                $(this).css('backgroundColor', '#' + this.value);
            });
        }
    });
});


