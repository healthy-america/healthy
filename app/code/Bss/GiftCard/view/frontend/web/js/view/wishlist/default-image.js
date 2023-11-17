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
    'jquery'
], function ($) {
    'use strict';

    /**
     * @inheritDoc
     */
    $.widget('bss.wishlistDefaultImage', {
        options: {
            productInfoElem: '.product-item-info',
            productImageElem: '.product-image-photo'
        },
        /**
         * @return {*}
         * @private
         */
        _create: function () {
            var productInfo = this.element.closest(this.options.productInfoElem),
                src = this.element.attr('src');
            var productImage = productInfo.find(this.options.productImageElem);
            if (productImage.length) {
                productImage.attr('src', src);
                productImage.trigger('contentUpdate');
            }
            return this._super();
        }
    });

    return $.bss.wishlistDefaultImage;
});
