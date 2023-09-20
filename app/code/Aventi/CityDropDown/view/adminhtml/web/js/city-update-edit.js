/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/utils/wrapper',
    'mage/template',
    'mage/validation',
    'underscore',
    'jquery/ui'
], function ($) {
    'use strict';

    return function () {
        var firstInningFlag = 1;

        $(document).on('change', "[name*='country_id']", function () {
            if (firstInningFlag === 1) {
                let cityId = $("[name*='city_id']").val();
                if (cityId !== undefined && cityId !== '') {
                    loadDefaultValuesInCity(cityId);
                }
                firstInningFlag++;
            } else {
                $("[name*='state_id']").val('').trigger('change');
            }
        });

        $(document).on('change', "[name*='state_id']", function (e) {
            $("[name*='region_id']").val(e.target.value).trigger('change');
        });

        function loadDefaultValuesInCity(cityId) {
            var citydropdownCityData = window.citydropdownCityData;
            citydropdownCityData = citydropdownCityData.substring(0, citydropdownCityData.length - 1);
            $.ajax({
                url: citydropdownCityData,
                data:{
                    form_key: window.FORM_KEY,
                    ajax: 1,
                    city_id: cityId
                },
                type: 'POST',
                dataType: 'json'
            }).done(function (response) {
                if (response.city_id !== undefined && response.city_id !== '') {
                    $("[name*='country_id']").val(response.country_id).trigger('change');
                    $("[name*='state_id']").val(response.region_id).trigger('change');
                }
            }).fail(function (error) {
                console.error(error);
            })
        }
    };
});
