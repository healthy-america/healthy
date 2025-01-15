<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\Checkout\Plugin\Magento\Checkout\Block\Checkout;

use Aventi\AventiTheme\Model\Config\Source\CustomerTypeOptions;
use Aventi\Checkout\Model\Source\Config\TributaryInformation;
use Magento\Checkout\Block\Checkout\LayoutProcessor as Source;

class LayoutProcessor
{
    protected const FIELDS_DELIMITATION  = [
        "telephone" => 20,
        "postcode" => 20,
        "firstname" => 50,
        "lastname" => 50,
        "vat_id" => 32
    ];
    /**
     * Constructor
     *
     * @param CustomerTypeOptions $customerTypeOptions
     * @param TributaryInformation $tributaryInformationOptions
     */
    public function __construct(
        private CustomerTypeOptions $customerTypeOptions,
        private TributaryInformation $tributaryInformationOptions
    ) {
    }

    /**
     * @param Source $subject
     * @param $result
     * @param $jsLayout
     * @return mixed
     */
    public function afterProcess(
        Source $subject,
        $result,
        $jsLayout
    ) {
        $shippingForm = &$result['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['shipping-address-fieldset']['children'];
        $shippingForm['firstname']['placeholder'] = __('Nombre');
        $shippingForm['lastname']['placeholder'] = __('Apellido');
        $shippingForm['vat_id']['placeholder'] = __('Identificación');
        $shippingForm['street']['children'][0]['placeholder'] = __('Dirección');
        $shippingForm['city']['placeholder'] = __('Ciudad');
        $shippingForm['postcode']['placeholder'] = __('Código postal');
        $shippingForm['telephone']['placeholder'] = __('Telefono');

        $customerTypeOptions = $this->customerTypeOptions->toOptionArray();

        $shippingForm['suffix'] = [
            'component' => 'Aventi_Checkout/js/form/element/document-type',
            'config' => [
                "customScope" => "shippingAddress",
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/select',
                'id' => 'suffix',
            ],
            'dataScope' => 'shippingAddress.suffix',
            'label' => __('Customer type'),
            'provider' => 'checkoutProvider',
            'sortOrder' => 48,
            'validation' => ['required-entry' => true],
            'caption' => __('Please select an option'),
            'options' => $customerTypeOptions
        ];

        $shippingForm['fax'] = [
            'component' => 'Aventi_Checkout/js/form/element/document-type',
            'config' => [
                "customScope" => "shippingAddress",
                'template' => 'ui/form/field',
                'elementTmpl' => 'Aventi_Checkout/form/element/document-type',
                'id' => 'fax',
            ],
            "required" => true,
            'dataScope' => 'shippingAddress.fax',
            'label' => __('Identification type'),
            'provider' => 'checkoutProvider',
            'sortOrder' => 49,
            'validation' => ['required-entry' => true],
        ];

        $shippingForm['company'] = [
            'component' => 'Magento_Ui/js/form/element/select',
            'config' => [
                "customScope" => "shippingAddress",
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/select',
                'id' => 'company',
            ],
            'dataScope' => 'shippingAddress.company',
            'label' => __('Tributary information') . "*",
            'provider' => 'checkoutProvider',
            'sortOrder' => 49,
            'options' => $this->tributaryInformationOptions->getAllOptions()
        ];

        // Restrict street address length
        foreach ($shippingForm['street']['children'] as $key => &$line) {
            $line['validation']['max_text_length'] = 100;
            if ($key === 1) {
                $line['placeholder'] = __('Address complement (Neighborhood, apartment, etc)');
            }
        }

        $result['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['shipping-address-fieldset']['children']['postcode']['visible'] = false;

        // Add document validation
        $result['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['shipping-address-fieldset']['children']['vat_id']['validation'] = [
            'required-entry' => true,
            'pattern' => '^[0-9]+(-[0-9])?$'
        ];

        foreach ($shippingForm as $field => $value) {
            if (array_key_exists($field, self::FIELDS_DELIMITATION)) {
                $shippingForm[$field]['validation']['max_text_length'] = self::FIELDS_DELIMITATION[$field];
            }
        }

        return $result;
    }
}
