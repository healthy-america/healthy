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
    'mage/translate',
    'Magento_Ui/js/form/element/file-uploader',
    'Magento_Ui/js/modal/alert',
    'mage/url'
], function ($, $t, Component, alert, urlBuilder) {
    'use strict';

    return Component.extend({
        /**
         * Initializes file uploader plugin on provided input element.
         *
         * @param {HTMLInputElement} fileInput
         * @returns {FileUploader} Chainable.
         */
        initUploader: function (fileInput) {
            this.$fileInput = fileInput;

            _.extend(this.uploaderConfig, {
                change:     this.onFilesChoosed.bind(this),
                add:        this.onBeforeFileUpload.bind(this),
                done:       this.onFileUploaded.bind(this),
                start:      this.onLoadingStart.bind(this),
                formData: {
                    'form_key': window.FORM_KEY
                }
            });

            $(fileInput).fileupload(this.uploaderConfig);

            return this;
        },

        /**
         * Handler which is invoked prior to the start of a file upload.
         *
         * @param {Event} e - Event object.
         * @param {Object} data - File data that will be uploaded.
         */
        onBeforeFileUpload: function (e, data) {
            var file      = data.files[0],
                allowed   = this.isFileAllowed(file),
                target    = $(e.target),
                patternId = parseInt($('[name="pattern_id"]').val());
            if (!patternId) {
                allowed.message = $t('Please save pattern');
            }

            if (allowed.passed && patternId) {
                target.on('fileuploadsend', function (event, postData) {
                    postData.data.append('param_name', this.paramName);
                    postData.data.append('id', patternId);
                }.bind(data));

                target.fileupload('process', data).done(function () {
                    data.submit();
                });
            } else {
                this.notifyError(allowed.message);
            }
        },

        /**
         * Handler of the file upload complete event.
         *
         * @param {Event} e
         * @param {Object} data
         */
        onFileUploaded: function (e, data) {
            var result = data.result,
                message = result.message;
            $('body').trigger('processStop');
            if (result.status) {
                alert({
                    title: $t('Success'),
                    content: message,
                    actions: {
                        cancel: function () {
                            eval('pattern_code_listJsObject').reload();
                        }
                    }
                });
                if (result.new_items > 0) {
                    $('[name="pattern_code_qty"]').val(parseInt($('[name="pattern_code_qty"]').val()) + result.new_items);
                    $('[name="pattern_code_unused"]').val(parseInt($('[name="pattern_code_unused"]').val()) + result.new_items);
                }
            } else {
                this.notifyError($t('Please check again.'));
            }
        },

        /**
         * Load start event handler.
         */
        onLoadingStart: function () {
            $('body').trigger('processStart');
        },

        sampleCsvUrl: function () {
            return this.uploaderConfig.url.replace('save', 'download');
        }
    });
});

