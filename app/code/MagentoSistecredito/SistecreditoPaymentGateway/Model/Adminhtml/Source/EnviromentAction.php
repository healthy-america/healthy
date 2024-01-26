<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MagentoSistecredito\SistecreditoPaymentGateway\Model\Adminhtml\Source;

use Magento\Payment\Model\Method\AbstractMethod;

/**
 * Class PaymentAction
 */
class EnviromentAction implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        return [
            [
                'value' => "Development",
                'label' => 'Development'
            ],
            [
                'value' => "Qa",
                'label' => 'Qa'
            ],
            [
                'value' => "Staging",
                'label' => 'Staging'
            ],
            [
                'value' => "Production",
                'label' => 'Production'
            ],
        ];
    }
}
