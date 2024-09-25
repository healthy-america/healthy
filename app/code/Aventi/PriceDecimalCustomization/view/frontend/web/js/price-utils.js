/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'mage/utils/wrapper'
], function (wrapper) {
    'use strict';

    return function (priceUtils) {
        priceUtils.formatPriceLocale = wrapper.wrapSuper(priceUtils.formatPriceLocale, function (amount, format, isShowSign) {
            format.pattern = '$ %s';
            return this._super(amount, format, isShowSign);
        });

        return priceUtils;
    };
});
