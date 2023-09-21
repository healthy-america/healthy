<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\Checkout\Plugin\Magento\Checkout\Block\Checkout;

class LayoutProcessor
{
    /**
     * @param \Magento\Checkout\Block\Checkout\LayoutProcessor $subject
     * @param $result
     * @param $jsLayout
     * @return mixed
     */
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        $result,
        $jsLayout
    ) {

        $shippingForm = &$result['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['shipping-address-fieldset']['children'];
        $shippingForm['firstname']['placeholder'] = __('Nombre');
        $shippingForm['lastname']['placeholder'] = __('Apellido');
        $shippingForm['company']['placeholder'] = __('Empresa');
        $shippingForm['vat_id']['placeholder'] = __('Identificación');
        $shippingForm['street']['children'][0]['placeholder'] = __('Dirección');
        $shippingForm['city']['placeholder'] = __('Ciudad');
        $shippingForm['postcode']['placeholder'] = __('Código postal');
        $shippingForm['telephone']['placeholder'] = __('Telefono');

        $customOptions = [
            [
                'value' => 'default',
                'label' => '---Identification type---',
            ],
            [
                'value' => 'C.C',
                'label' => 'C.C',
            ],
            [
                'value' => 'C.E',
                'label' => 'C.E',
            ],
        ];

        $shippingForm['middlename'] = [
            'component' => 'Magento_Ui/js/form/element/select',
            'config' => [
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/select',
                'id' => 'middlename',
            ],
            'dataScope' => 'shippingAddress.middlename',
            'label' => __('Identification type'),
            'provider' => 'checkoutProvider',
            'visible' => true,
            'sortOrder' => 49,
            'validation' => [],
            'options' => $customOptions,
        ];

        //Your plugin code
        return $result;
    }
}
