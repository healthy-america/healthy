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
    'underscore'
], function ($, _) {
    'use strict';

    /**
     * @inheritDoc
     */
    $.widget('bss.wishlistConfigurePrice', {
        /**
         * @private
         */
        _create: function () {
            var self = this;
            this._super();
            var itemId = this.options.item_id,
                amount = this.options.amount,
                wishlistItem = $('#item_' + itemId);
            if (undefined !== amount && wishlistItem.length) {
                wishlistItem.find('.price-configured_price p').empty().html(amount);
            }
        }
    });

    return $.bss.wishlistConfigurePrice;
});
