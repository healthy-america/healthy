<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\CityDropDown\Plugin\Magento\Checkout\Block\Checkout;

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
        if (isset($result['components']['checkout']['children']['steps']['children']['billing-step']['children']['payment']
            ['children']['payments-list']['children'])) {
            $billingForm = &$result['components']['checkout']['children']['steps']['children']['billing-step']
            ['children']['payment']['children']['payments-list']['children'];
            $this->setCityInBilling($billingForm);
        }

        if (isset($result['components']['checkout']['children']['steps']['children']['billing-step']['children']['billingAddress']
            ['children']['billing-address-fieldset']['children'])) {
            $billingForm = &$result['components']['checkout']['children']['steps']['children']['billing-step']['children']['billingAddress']
            ['children']['billing-address-fieldset']['children'];
            $this->setCityInOSBilling($billingForm);
        }

        return $result;
    }

    /**
     * @param $result
     * @return void
     */
    private function setCityInOSBilling(&$result): void
    {
        $result['city_id'] = $this->getCitySelect('billingAddress', '');
        $result['city'] = $this->getInputCity('billingAddress', '');
        $result['postcode']['filterBy'] =  [
            'target' => '${ $.provider }:${ $.parentScope }.city_id',
            'field' => 'city_id'
        ];
        $result['postcode']['component'] =  'Aventi_CityDropDown/js/form/element/post-code';
        $result['postcode']['visible'] = true;
        //$this->changeOrderForm($result);
    }

    /**
     * @param $result
     * @return void
     */
    private function setCityInBilling(&$result): void
    {
        foreach ($result as $paymentGroup => $groupConfig) {
            if (isset($groupConfig['component']) && strpos( $groupConfig['component'], 'js/view/billing-address')) {
                $paymentMethodCode = str_replace('-form', '', $paymentGroup);
                $result[$paymentGroup]['children']['form-fields']['children']['city_id'] = $this->getCitySelect('billingAddress', $paymentMethodCode);
                $result[$paymentGroup]['children']['form-fields']['children']['city'] = $this->getInputCity('billingAddress', $paymentMethodCode);
                $result[$paymentGroup]['children']['form-fields']['children']['postcode']['filterBy'] =  [
                    'target' => '${ $.provider }:${ $.parentScope }.city_id',
                    'field' => 'city_id'
                ];
                $result[$paymentGroup]['children']['form-fields']['children']['postcode']['component'] =  'Aventi_CityDropDown/js/form/element/post-code';
                $result[$paymentGroup]['children']['form-fields']['children']['postcode']['visible'] = false;
            }
            //$this->changeOrderForm($result[$paymentGroup]['children']['form-fields']['children']);
        }
    }

    /**
     * @param $result
     * @return void
     */
    private function changeOrderForm(&$result): void
    {
        $field = 'sortOrder';
        $result['firstname'][$field] = 10;
        $result['lastname'][$field] = 15;
        $result['company'][$field] = 85;
        $result['telephone'][$field] = 90;
        $result['street'][$field] = 95;
        $result['country_id'][$field] = 100;
        $result['region_id'][$field] = 105;
        $result['region'][$field] = 110;
        $result['city_id'][$field] = 115;
        $result['city'][$field] = 120;
        $result['postcode'][$field] = 125;
    }

    /**
     * @param $form
     * @param $paymentMethodCode
     * @return array
     */
    private function getCitySelect($form, $paymentMethodCode): array
    {
        return [
            'component' => 'Aventi_CityDropDown/js/form/element/city-select',
            'config' => [
                'customScope' => $form.$paymentMethodCode,
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/select',
                'id' => 'city_id'
            ],
            'label' => 'Ciudad',
            'value' => '',
            'dataScope' => $form.$paymentMethodCode.'.city_id',
            'provider' => 'checkoutProvider',
            'sortOrder' => 115,
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
    }

    /**
     * @param $form
     * @param $paymentMethodCode
     * @return array
     */
    private function getInputCity($form, $paymentMethodCode): array
    {
        return [
            'component' => 'Aventi_CityDropDown/js/form/element/city',
            'config' => [
                'customScope' => $form .$paymentMethodCode,
                'template' => 'ui/form/field',
                'elementTmpl' => 'ui/form/element/input',
                'id' => 'city'
            ],
            'label' => 'Ciudad',
            'value' => '',
            'dataScope' => $form.$paymentMethodCode.'.city',
            'provider' => 'checkoutProvider',
            'sortOrder' => 120,
            'customEntry' => null,
            'visible' => false,
            'cacheable' => true,
            'disabled' => 'disabled',
            'filterBy' => [
                'target' => '${ $.provider }:${ $.parentScope }.city_id',
                'field' => 'city_id'
            ],
            'validation' => [
                'required-entry' => true
            ],
            'id' => 'city'
        ];
    }
}
