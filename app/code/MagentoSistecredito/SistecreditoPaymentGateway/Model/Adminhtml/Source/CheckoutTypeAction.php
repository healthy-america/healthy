<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MagentoSistecredito\SistecreditoPaymentGateway\Model\Adminhtml\Source;

use Magento\Payment\Model\Method\AbstractMethod;



class CheckoutTypeAction implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => "false",
                'label' => 'On Same Site'
            ],
            [
                'value' => "true",
                'label' => 'Redirect'
            ]
        ];
    }
}
