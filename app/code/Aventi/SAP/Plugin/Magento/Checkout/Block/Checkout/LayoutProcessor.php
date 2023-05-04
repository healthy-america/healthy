<?php

namespace Aventi\SAP\Plugin\Magento\Checkout\Block\Checkout;

/**
 * Class LayoutProcessor
 *
 * @package Aventi\SAP\Plugin\Magento\Checkout\Block\Checkout
 */
class LayoutProcessor
{
    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        $result,
        $jsLayout
    ) {
        $result['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['shipping-address-fieldset']['children']['vat_id']['validation'] = [
            'required-entry' => true,
            'validate-number' => true,
            'min_text_length' => 8,
            'max_text_length' => 13
        ];

        $result['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['shipping-address-fieldset']['children']['vat_id']['sortOrder'] = 50;

        return $result;
    }
}
