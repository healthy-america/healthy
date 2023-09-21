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
        var firstInningFlag = 1,
            defaultValueCityFlag = 1,
            addressId = '';

        var defaultOption = '<option value="">Por favor seleccione una ciudad.</option>',
            inputCityHtml = '',
            regionSelect = "body [name*='region_id']",
            regionSelectOption = "body [name*='region_id'] option",
            cityInput = "body [name*='city']",
            citySelect = 'body #city',
            citySelectOption = "body #city option",
            inputPostcode = "body [name*='postcode']",
            citydropdownCityGet = $citydropdownCityGet.substring(0, $citydropdownCityGet.length - 1);

        $(document).on('change', "[name*='country_id']", function (e) {
            $(inputPostcode).val('').trigger('change');
            //setTimeout(addCityInput(), 500);
            addCityInput();
            firstInningFlag = 2;
        });

        $(document).on('change', "[name*='region_id']", function (e) {
            let region_id = $(regionSelect).val();
            if ($(regionSelectOption).length > 1) {
                saveAddressId();
                addCitySelect();
                addCurrentCities(region_id);
            }
        });


        function saveAddressId() {
            if (defaultValueCityFlag === 1) {
                addressId = $('[name="entity_id"]').val();
                defaultValueCityFlag = 2;
            }
        }

        function addCitySelect() {
            let city = $(cityInput),
                selectCity = '';
            if (!city.is('select')) {
                inputCityHtml = city[0].outerHTML;
                selectCity = city.replaceWith("<select class='required-entry select admin__control-select' name='city' id='city'>") + '</select>';
                $(citySelect).append(defaultOption);
                $(citySelect).change(function(e){
                    let post = $(e.target).find('option:selected').attr('data-postal');
                    $(inputPostcode).val(post).trigger('change');
                });
            }
        }

        function addCityInput() {
            let city = $(citySelect),
                cityVal = '';
            if ($(regionSelectOption).length > 1) {
                addCitySelect();
            } else if (inputCityHtml !== '' && $(regionSelectOption).length < 2) {
                cityVal = city.val();
                city.replaceWith(inputCityHtml);
                $(cityInput).val(cityVal).trigger('changed');
            }
        }

        function addCurrentCities(_region_id) {
            var cities = $(citySelect),
                postcode = $(inputPostcode),
                link = citydropdownCityGet,
                _data = getDataAjax(_region_id, addressId);
            cities.attr('disabled', 'disabled');
            resetCitySelect();
            if (defaultValueCityFlag === 3 && (_region_id === '' || _region_id === undefined)) {
                cities.removeAttr('disabled');
                return;
            }
            $.ajax({
                url: link,
                data: _data,
                type: 'POST',
                dataType: 'json'
            }).done(function (json) {
                var city = '',
                    _cities = [];
                if (json['error'] === undefined) {
                    _cities = json['cities'] !== undefined ? json.cities : json;
                    city = json['address'] !== undefined && json.address.id !== -1 ? capitalize(json.address.city) : '';
                    $.each(_cities, function (i, attribute) {
                        let name = capitalize(attribute.name),
                            selected = '';
                        if (defaultValueCityFlag === 2 && name === city) {
                            selected = 'selected';
                            defaultValueCityFlag = 3;
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

        function getDataAjax(_region_id, addressId) {
            if (_region_id === undefined || _region_id === '')
                return {
                    form_key: window.FORM_KEY,
                    ajax: 1,
                    region_id: _region_id,
                    address_id: addressId
                };
            else {
                return {
                    form_key: window.FORM_KEY,
                    ajax: 1,
                    region_id: _region_id
                };
            }
        }
    };
});
