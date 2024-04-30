<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */


namespace Aventi\SAP\Plugin\Magento\Checkout\Block\Checkout;

use Magento\Checkout\Block\Checkout\LayoutProcessor as Source;

/**
 * Class LayoutProcessor
 *
 * @package Aventi\SAP\Plugin\Magento\Checkout\Block\Checkout
 */
class LayoutProcessor
{
    public function afterProcess(
        Source $subject,
        $result,
        $jsLayout
    ) {

        $result['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['shipping-address-fieldset']['children']['vat_id']['sortOrder'] = 50;

        return $result;
    }
}
