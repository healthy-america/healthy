/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'mage/url',
    'mage/utils/wrapper',
    'mage/template',
    'mage/validation',
    'underscore',
    'jquery/ui'
], function ($, url) {
    'use strict';

    return function () {

        var defaultOption = '<option value="">Por favor seleccione una ciudad.</option>',
            defaultCityValue = '',
            inputCityHtml = '',
            regionSelect = "body [name*='region_id']",
            regionSelectOption = "body [name*='region_id'] option",
            cityInput = "body [name*='city']",
            citySelect = 'body #city',
            citySelectOption = "body #city option",
            inputPostcode = "body [name*='postcode']",
            citydropdownCityGet = $citydropdownCityGet.substring(0, $citydropdownCityGet.length - 1);

        $(document).ready(function(){
            defaultCityValue = capitalize($(cityInput).val());
            addTypeCityInput($(regionSelectOption).length);
            addCurrentCities($(regionSelect).val(), 1);
        });
        
        $(document).on('change', "[name*='country_id']", function (e) {
            $(inputPostcode).val('').trigger('change');
            addTypeCityInput($(regionSelectOption).length);
        });

        $(document).on('change', "[name*='region_id']", function (e) {
            if ($(regionSelectOption).length > 1) {
                addCurrentCities(e.target.value, 2);
            }
        });

        function addTypeCityInput(lengthRegion) {
            let city = $(cityInput),
                selectCity = '',
                cityVal = '';
            if (!city.is('select') && lengthRegion > 1) {
                inputCityHtml = city[0].outerHTML;
                selectCity = city.replaceWith("<select class='required-entry select admin__control-select' name='city' id='city'>") + '</select>';
                $(citySelect).append(defaultOption);
                $(citySelect).change(function(e){
                    let post = $(e.target).find('option:selected').attr('data-postal');
                    $(inputPostcode).val(post).trigger('change');
                });
            } else if (inputCityHtml !== '') {
                cityVal = $(citySelect).val();
                $(citySelect).replaceWith(inputCityHtml);
                $(cityInput).val(cityVal).trigger('change');
            }
        }

        function addCurrentCities(_region_id, flag) {
            var cities = $(citySelect),
                postcode = $(inputPostcode),
                link = citydropdownCityGet;
            cities.attr('disabled', 'disabled');
            resetCitySelect();
            $.ajax({
                url: link,
                data: {
                    form_key: window.FORM_KEY,
                    ajax: 1,
                    region_id: _region_id
                },
                type: 'POST',
                dataType: 'json'
            }).done(function (json) {
                if (json['error'] === undefined) {
                    $.each(json, function (i, attribute) {
                        let name = capitalize(attribute.name),
                            selected = '';
                        if (flag === 1 && name === defaultCityValue) {
                            selected = 'selected';
                        }
                        cities.append(
                            "<option data-postal='" +
                            attribute.postalCode +
                            "' value='" +
                            name +
                            "' "+
                            selected
                            +" >" + name + "</option>");
                        if (selected === 'selected') {
                            postcode.val(attribute.postalCode).trigger('change');
                        }
                    });
                }
                cities.removeAttr("disabled");
            });
        }

        function resetCitySelect() {
            let cities = $(citySelect);
            let postcode = $(inputPostcode);
            if ($(citySelectOption).length > 1) {
                cities.find('option')
                    .remove()
                    .end()
                    .append(defaultOption);
                postcode.val('').trigger('change');
            }
        }

        function capitalize(str) {
            const lower = str.toLowerCase();
            return str.charAt(0).toUpperCase() + lower.slice(1);
        }
    };
});
