<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\Checkout\Plugin\Magento\Checkout\Block\Checkout;

class LayoutProcessor
{

    public function afterProcess(
        \Magento\Checkout\Block\Checkout\LayoutProcessor $subject,
        $result,
        $jsLayout
    ) {
        
        $shippingForm = &$result['components']['checkout']['children']['steps']['children']['shipping-step']['children']
        ['shippingAddress']['children']['shipping-address-fieldset']['children'];
        $shippingForm['firstname']['placeholder'] = __('Nombre y Apellido');
        $shippingForm['lastname']['placeholder'] = __('Apellido');    
        $shippingForm['company']['placeholder'] = __('Identificación');    
        $shippingForm['vat_id']['placeholder'] = __('Identificación');    
        $shippingForm['street']['children'][0]['placeholder'] = __('Dirección');    
        $shippingForm['city']['placeholder'] = __('Ciudad');
        $shippingForm['postcode']['placeholder'] = __('Código postal');
        $shippingForm['telephone']['placeholder'] = __('Telefono');


        //Your plugin code
        return $result;
    }
}
