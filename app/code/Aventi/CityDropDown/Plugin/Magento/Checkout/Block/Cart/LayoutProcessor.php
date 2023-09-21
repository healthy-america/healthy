<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\CityDropDown\Plugin\Magento\Checkout\Block\Cart;

class LayoutProcessor
{
    public function afterProcess(
        \Magento\Checkout\Block\Cart\LayoutProcessor $subject,
        $result,
        $jsLayout
    ) {
        $formShipping = &$result['components']['block-summary']['children']['block-shipping']['children']['address-fieldsets']['children'];

        $formShipping['country_id']['validation'] = [
            'required-entry' => true
        ];

        $formShipping['region_id']['validation'] = [
            'required-entry' => true
        ];

        $formShipping['city_id'] = [
            'component' => 'Aventi_CityDropDown/js/form/element/city-select',
            'config' => [
                "customScope" => "shippingAddress",
                "template" => "ui/form/field",
                "elementTmpl" => "ui/form/element/select",
                'id' => 'city_id'
            ],
            'label' => "Ciudad",
            'value' => '',
            'dataScope' => 'shippingAddress.city_id',
            'provider' => 'checkoutProvider',
            'sortOrder' => 145,
            "required" => true,
            'customEntry' => null,
            'visible' => true,
            'cacheable' => true,
            'options' => [
            ],
            'filterBy' => [
                'target' => '${ $.provider }:${ $.parentScope }.region_id',
                'field' => 'region_id'
            ],
            'validation' => [
                'required-entry' => true
            ],
            'id' => 'city_id'
        ];

        $formShipping['city'] = [
            'component' => 'Aventi_CityDropDown/js/form/element/city',
            'config' => [
                "customScope" => "shippingAddress",
                "template" => "ui/form/field",
                "elementTmpl" => "ui/form/element/input",
                'id' => 'city'
            ],
            'label' => 'Ciudad',
            'value' => '',
            'dataScope' => 'shippingAddress.city',
            'provider' => 'checkoutProvider',
            'sortOrder' => 240,
            'customEntry' => null,
            'visible' => false,
            'disabled' => 'disabled',
            'cacheable' => true,
            'filterBy' => [
                'target' => '${ $.provider }:${ $.parentScope }.city_id',
                'field' => 'city_id'
            ],
            'validation' => [
                'required-entry' => true
            ],
            'id' => 'city'
        ];

        $formShipping['postcode']['filterBy'] = [
            'target' => '${ $.provider }:${ $.parentScope }.city_id',
            'field' => 'city_id'
        ];
        $formShipping['postcode']['component'] = 'Aventi_CityDropDown/js/form/element/post-code';
        $formShipping['postcode']['validation'] = [
            'required-entry' => true
        ];
        $formShipping['postcode']['sortOrder'] = 250;
        $formShipping['postcode']['disabled'] = 'disabled';

        return $result;
    }
}
