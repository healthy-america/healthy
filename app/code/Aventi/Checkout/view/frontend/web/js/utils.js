/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * @api
 */
define(function () {
    'use strict';

    var utils = {
        /**
         * Check if string is empty with trim.
         *
         * @param {String} value
         * @return {Boolean}
         */
        isEmpty: function (value) {
            return value === '' || value == null || value.length === 0 || /^\s+$/.test(value);
        },
    };
    return utils;
})
