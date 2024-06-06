/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    './utils',
    'jquery/validate'
], function($, utils) {
    'use strict';

    return function (validator) {
        validator.addRule(
            'document-validation',
            function (value, param) {

                if($('select[name="fax"]').val() === 'RUT'){
                    return utils.isEmpty(value) || new RegExp(param).test(value);
                }else{
                    return utils.isEmpty(value) || value.length <= 10
                }
            },
            $.mage.__('Invalid format.')
        );
        return validator;
    }
});
