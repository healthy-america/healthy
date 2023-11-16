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
 * @copyright  Copyright (c) 2017-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

define([
    'ko',
    'underscore',
    'uiComponent',
    'jquery',
    'Bss_GiftCard/js/action/product/preview',
    'mage/translate',
    'Magento_Catalog/js/price-utils',
    'Bss_GiftCard/js/lib/owl.carousel',
    'mage/calendar'
], function (ko, _, Component, $, Preview, $t, priceUtils) {
    'use strict';

    return Component.extend({

        defaults: {
            template: 'Bss_GiftCard/product/view/type/giftcard',
            giftCardConfig: {
                amount: {
                    amountDynamic: {},
                    amountList: []
                },
                template: {},
                message: false,
                isPhysical: false
            }
        },

        currentTemplate: {},

        giftCardJsonAmount: {},

        giftCardJsonTemplate: {},

        isDisplayedDynamicAmount: ko.observable(false),

        isMessage: ko.observable(false),

        isPhysical: ko.observable(false),

        isSelectedImage: ko.observable(),

        senderName: ko.observable(),

        recipientName: ko.observable(),

        senderEmail: ko.observable(),

        recipientEmail: ko.observable(),

        messageValue: ko.observable(),

        deliveryDate: ko.observable(),

        timeZone: ko.observable(),

        isDisplayedImages: ko.observable(false),

        customAmountOption: {
            value: 'custom',
            label: $t('Other Amount...'),
            price: 0,
            tax_percent: null
        },


        selectAmount: ko.observable(),

        changeAmount: ko.observable(),

        selectedTemplate: ko.observable(),

        templateImages: ko.observableArray([]),

        initialize: function () {
            this._super();
            this.giftCardJsonAmount = this.giftCardConfig.amount;
            this.giftCardJsonTemplate = this.giftCardConfig.template;
            var amountDynamic = this.giftCardJsonAmount.amountDynamic;
            if (this.giftCardConfig.message) {
                this.isMessage(true);
            }
            if (this.giftCardConfig.isPhysical) {
                this.isPhysical(true);
            }
            if (!_.isUndefined(amountDynamic) && !_.isEmpty(amountDynamic)) {
                this.customAmountOption.tax_percent = this.giftCardConfig.amount.taxPercent;
                this.giftCardJsonAmount.amountList.push(this.customAmountOption);
            }
            this.addConfigure();
        },

        initObservable: function () {
            var self = this;
            this._super();
            this.selectAmount.subscribe(function(value) {
                if (value === 'custom') {
                    self.isDisplayedDynamicAmount(true);
                    var tax_percent_val = self.getAmountList()[self.getAmountList().length - 1].tax_percent;
                    self.updatePriceBox(self.changeAmount(), true, null, tax_percent_val);
                } else {
                    var price = 0,
                        priceIncludeTax = null,
                        selected = $.grep(self.getAmountList(), function(amount) {
                        return amount.value === value;
                    });
                    self.isDisplayedDynamicAmount(false);
                    if (selected && selected.length > 0) {
                        price = selected[0].price;
                        priceIncludeTax = selected[0].price_include_tax;
                    }
                    self.updatePriceBox(price, false, priceIncludeTax);
                }
            });
            this.selectedTemplate.subscribe(function(value) {
                if (value) {
                    self.isDisplayedImages(true);
                } else {
                    self.isDisplayedImages(false);
                }
                self.onChangeTemplate(value);
            });
            this.changeAmount.subscribe(function(value) {
                var tax_percent_val = self.getAmountList()[self.getAmountList().length - 1].tax_percent;
                self.updatePriceBox(value, true, null, tax_percent_val);
            });
            $('li.bss-img-selected').click();

            return this;
        },

        onChangeTemplate: function (templateId) {
            var templates = this.giftCardJsonTemplate.filter(function(template) {
                return template.template_id === templateId;
            });

            if (templates.length > 0) {
                $('.bss-giftcard-template-images-ul').trigger('destroy.owl.carousel').empty();
                this.templateImages(templates[0].images);
                this.currentTemplate = templates;
                if (!_.isUndefined(templates[0].images[0].id)) {
                    this.isSelectedImage(templates[0].images[0].id);
                    this.updateBaseImage(templates[0].images[0].id);
                    $('.bss-giftcard-template-images-ul').owlCarousel({
                        loop:false,
                        margin:10,
                        nav:true,
                        navText: [
                            '<div class="bss_owl_arr bss_owl_arr-prev"><span>&#8249;</span></div>',
                            '<div class="bss_owl_arr bss_owl_arr-next"><span>&#8250;</div>'
                        ]
                    });
                }
            } else {
                this.templateImages({});
            }
        },

        selectImage: function ($parent, id) {
            var zoomImage = $("#preview #magnifier-item-0-large"),
                images = this.url,
                e;

            if (_.isObject(id)) {
                id = this.id;
            }
            e = $('#' + $parent.getType() + '_image_' + id);
            e.closest('.bss-giftcard-template-images-ul').find('li').removeClass('bss-img-selected');
            e.addClass('bss-img-selected');
            $parent.isSelectedImage(id);
            $parent.updateBaseImage(id);

            if (zoomImage.length && images) {
                zoomImage.attr('src', images);
            }
        },

        updateBaseImage: function (imageId) {
            var fullImage = $('.fotorama__stage .fotorama__active img.fotorama__img'),
                images = this.currentTemplate[0].images,
                url = $.grep(images, function(image) {
                    return image.id === imageId;
                });
            if(fullImage && !_.isUndefined(url[0]) && url[0].url) {
                fullImage.attr('src', url[0].url);
            }
        },

        updatePriceBox: function (val, custom, priceIncludeTax = null, taxPercent = null) {
            var productId = this.giftCardConfig.productId,
                value = parseFloat(val),
                changes;

            if (value < 0) {
                value = 0;
            }
            if (custom && !_.isUndefined(this.giftCardJsonAmount.amountDynamic.percentageValue)) {
                value *= parseFloat(this.giftCardJsonAmount.amountDynamic.percentageValue)/100;
            }

            if (taxPercent) {
                priceIncludeTax = value * taxPercent;
            }

            if (priceIncludeTax !== null) {
                changes = {
                    "giftcard": {
                        "finalPrice": {
                            "amount": parseFloat(priceIncludeTax).toFixed(2)
                        }
                    }
                };
            } else { // Default
                changes = {
                    "giftcard": {
                        "finalPrice": {
                            "amount": value
                        }
                    }
                };
            }

            // Update price default
            var selectorPrice = $('#product-price-' + productId);
            if (selectorPrice.length > 0) {
                selectorPrice.trigger('updatePrice', changes);
            }

            // Update include price & exclude price
            var selectorPriceTaxIn = $('#price-including-tax-product-price-' + productId);
            var selectorPriceTaxEx = $('#price-excluding-tax-product-price-' + productId);
            if (selectorPriceTaxIn.length > 0) {
                selectorPriceTaxIn.trigger('updatePrice', changes);
            }
            if (selectorPriceTaxEx.length > 0) {
                selectorPriceTaxEx.text(priceUtils.formatPrice(value));
            }
        },

        /**
         * @return {array}
         */
        getAmountList: function () {
            return this.giftCardJsonAmount.amountList;
        },

        getTimezoneList: function () {
            var obj = this.giftCardConfig.timeZone,
                timeZone = Object.values(obj);
            return timeZone;
        },

        /**
         * @return {array}
         */
        getTemplateList: function () {
            return this.giftCardJsonTemplate;
        },

        getMinAmount: function () {
            return this.giftCardJsonAmount.amountDynamic.minAmount;
        },

        getMaxAmount: function () {
            return this.giftCardJsonAmount.amountDynamic.maxAmount;
        },

        /**
         * @returns {String}
         */
        getType: function () {
            return 'bss_giftcard';
        },
        /**
         * @return {exports}
         */
        getRenderType: function() {
            $('#product_addtocart_form').trigger('updateContent');
            return this;
        },
        /**
         * @return {exports}
         */
        getRenderTypeSelect: function() {
            $('#bss-giftcard-timezone-select').trigger('updateContent');
            return this;
        },
        /**
         * @param elm
         * @return {exports}
         */
        initDate: function (elm) {
            $(elm).datepicker({
                showsTime: false,
                minDate: new Date()
            });
            $('#bss-giftcard-delivery-date-input').trigger('updateContent');
            return this;
        },

        cardPreview: function () {
            if ($('#product_addtocart_form').validation('isValid')) {
                Preview(this);
            }
        },

        addConfigure: function () {
            var productData = {};
            if (window.checkout.bssGiftCardData) {
                productData = window.checkout.bssGiftCardData;
            }
            if (productData) {
                if (productData.bss_giftcard_amount) {
                    this.selectAmount(productData.bss_giftcard_amount);
                }
                if (productData.bss_giftcard_amount_dynamic) {
                    this.changeAmount(productData.bss_giftcard_amount_dynamic);
                }
                if (productData.bss_giftcard_template) {
                    this.selectedTemplate(productData.bss_giftcard_template);
                }
                if (productData.bss_giftcard_selected_image) {
                    this.isSelectedImage(productData.bss_giftcard_selected_image);
                }
                if (productData.bss_giftcard_sender_name) {
                    this.senderName(productData.bss_giftcard_sender_name);
                }
                if (productData.bss_giftcard_recipient_name) {
                    this.recipientName(productData.bss_giftcard_recipient_name);
                }
                if (productData.bss_giftcard_sender_email) {
                    this.senderEmail(productData.bss_giftcard_sender_email);
                }
                if (productData.bss_giftcard_recipient_email) {
                    this.recipientEmail(productData.bss_giftcard_recipient_email);
                }
                if (productData.bss_giftcard_message_email) {
                    this.messageValue(productData.bss_giftcard_message_email);
                }
                if (productData.bss_giftcard_delivery_date) {
                    this.deliveryDate(productData.bss_giftcard_delivery_date);
                }
                if (productData.bss_giftcard_timezone) {
                    this.timeZone(productData.bss_giftcard_timezone);
                }
            }
        },

        addOwlCarousel: function ()
        {
            var imgSelect = this.isSelectedImage();
            $('.bss-giftcard-template-images-ul').owlCarousel({
                loop:false,
                margin:10,
                nav:true,
                navText: [
                    '<div class="bss_owl_arr bss_owl_arr-prev"><span>&#8249;</span></div>',
                    '<div class="bss_owl_arr bss_owl_arr-next"><span>&#8250;</div>'
                ]
            });
            if (imgSelect) {
                this.selectImage(this, imgSelect);
            }
        }
    });
});

