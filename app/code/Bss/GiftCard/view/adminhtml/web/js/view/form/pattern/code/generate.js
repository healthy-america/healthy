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
    'jquery',
    'Magento_Ui/js/modal/alert',
    'mage/translate',
    'Bss_GiftCard/js/action/pattern/code/reload-codelist',
    'uiRegistry'
], function (Component, $, alert, $t, Codelist, registry) {
    'use strict';

    return Component.extend({

        generatePatternCodes: function (obj) {
            var qty = parseInt(registry.get('index = add_code_qty').value()),
                id = parseInt(registry.get('index = pattern_id').value()),
                amount = parseFloat(registry.get('index = add_code_amount').value()),
                expiry = registry.get('index = add_code_expiry').value();
            if (!id) {
                alert({
                    title: $t('Error'),
                    content: $t('Please save pattern')
                });
            } else if (!qty || qty <= 0 || !amount) {
                alert({
                    title: $t('Error'),
                    content: $t('Please enter qty, amount field')
                });
            } else {
                Codelist(id, qty, amount, obj, expiry);
            }
        }
    });
});

