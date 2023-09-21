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
        var defaultOption = '<option value="">Por favor seleccione una ciudad.</option>',
            cityInput = $("[name*='city']").val();

        $(document).ready(function (){
            addCitySelect();
            addCurrentCities(1);
        });

        $(document).on('change', "[name*='country_id']", function () {
            resetCitySelect();
        });

        $(document).on('change', "[name*='region_id']", function () {
            addCurrentCities(2);
        });

        function addCitySelect() {
            let city = $("[name*='city']"),
                selectCity = city.replaceWith("<select class='required-entry' name='city' id='city'>") + '</select>';
            $('#city').append(defaultOption);
            $('#city').change(function(e) {
                let postcode = $("[name*='postcode']");
                let postalCode = $(this).find(":selected").data('postal');
                postcode.val(postalCode).trigger('changed');
            });
        }

        function resetCitySelect() {
            let cities = $('#city');
            let postcode = $("[name*='postcode']");
            cities.find('option')
                .remove()
                .end()
                .append(defaultOption);
            postcode.val('').trigger('changed');
        }

        function addCurrentCities(flag) {
            var _country_id = $("[name*='country_id']").val(),
                _region_id = $("[name*='region_id']").val(),
                cities = $('#city'),
                postcode = $("[name*='postcode']"),
                _this = flag === 1 ? cityInput : '';
            cities.attr('disabled', 'disabled');
            resetCitySelect();
            if (_region_id === undefined || _region_id === '') {
                return;
            }
            $.ajax({
                url: BASE_URL + 'citydropdown/index/index',
                type: "post",
                dataType: "json",
                data: {region_id: _region_id},
                cache: false
            }).done(function (json) {
                $.each(json, function (i, attribute) {
                    let selected = attribute.name == _this ?'selected': '';
                    cities.append(
                        "<option data-postal='" +
                        attribute.postalCode +
                        "' value='" +
                        attribute.name +
                        "' "+
                        selected
                        +" >" + capitalize(attribute.name) + "</option>");
                    if (selected === 'selected' && flag === 1) {
                        postcode.val(attribute.postalCode).trigger('changed');
                    }
                })
                cities.removeAttr("disabled");
            })
        }

        function capitalize(str) {
            const lower = str.toLowerCase();
            return str.charAt(0).toUpperCase() + lower.slice(1);
        }
    };
});
