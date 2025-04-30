/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */

/**
 * advisor model.
 */
define([
    'ko',
    'domReady!'
], function (ko) {
    'use strict';

    var advisor = ko.observable(null);

    return {
        advisor: advisor,

        /**
         * @return {*}
         */
        getAdvisor: function () {
            return advisor;
        },

        /**
         * @param {*} advisorValue
         */
        setAdvisor: function (advisorValue) {
            advisor(advisorValue);
        },
    };
});
