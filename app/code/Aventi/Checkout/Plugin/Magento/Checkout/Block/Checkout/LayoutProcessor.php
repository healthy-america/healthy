<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\Checkout\Plugin\Magento\Checkout\Block\Checkout;

use Aventi\AventiTheme\Model\Config\Source\CustomerIdentificationTypeOptions;

class LayoutProcessor
{
    /**
     * @var CustomerIdentificationTypeOptions
     */
    private CustomerIdentificationTypeOptions $customerIdentificationTypeOptions;

    /**
     * @param CustomerIdentificationTypeOptions $customerIdentificationTypeOptions
     */
    public function __construct(CustomerIdentificationTypeOptions $customerIdentificationTypeOptions)
    {
        $this->customerIdentificationTypeOptions = $customerIdentificationTypeOptions;
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

        $customerIdentificationTypeOptions = $this->customerIdentificationTypeOptions->toOptionArray();

        $shippingForm['fax'] = [
            'component' => 'Magento_Ui/js/form/element/select',
            'config' => [
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/select',
                'id' => 'fax',
            ],
            'dataScope' => 'shippingAddress.fax',
            'label' => __('Identification type'),
            'provider' => 'checkoutProvider',
            'visible' => true,
            'sortOrder' => 49,
            'validation' => [],
            'options' => $customerIdentificationTypeOptions,
        ];

        //Your plugin code
        return $result;
    }
}
