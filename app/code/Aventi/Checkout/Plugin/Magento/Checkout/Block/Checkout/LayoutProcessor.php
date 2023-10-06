<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\Checkout\Plugin\Magento\Checkout\Block\Checkout;

use Aventi\AventiTheme\Model\Config\Source\CustomerTypeOptions;

class LayoutProcessor
{
    /**
     * @var CustomerTypeOptions
     */
    private CustomerTypeOptions $customerTypeOptions;


    /**
     * @param CustomerTypeOptions $customerTypeOptions
     */
    public function __construct(
        CustomerTypeOptions $customerTypeOptions
    ) {
        $this->customerTypeOptions = $customerTypeOptions;
    }

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

        $customerTypeOptions = $this->customerTypeOptions->toOptionArray();

        $shippingForm['suffix'] = [
            'component' => 'Aventi_Checkout/js/form/element/document-type',
            'config' => [
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/select',
                'id' => 'suffix',
            ],
            'dataScope' => 'shippingAddress.suffix',
            'label' => __('Customer type'),
            'provider' => 'checkoutProvider',
            'visible' => true,
            'sortOrder' => 48,
            'validation' => [],
            'caption' => __('Please select an option'),
            'options' => $customerTypeOptions
        ];

        $shippingForm['fax'] = [
            'component' => 'Aventi_Checkout/js/form/element/document-type',
            'config' => [
                'template' => 'ui/form/field',
                'elementTmpl' => 'Aventi_Checkout/form/element/document-type',
                'id' => 'fax',
            ],
            'dataScope' => 'shippingAddress.fax',
            'label' => __('Identification type'),
            'provider' => 'checkoutProvider',
            'sortOrder' => 49,
            'validation' => []
        ];

        return $result;
    }
}
