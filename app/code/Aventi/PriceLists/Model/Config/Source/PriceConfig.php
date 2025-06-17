<?php

namespace Aventi\PriceLists\Model\Config\Source;

class PriceConfig implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * {@inheritdoc}
     *
     * @codeCoverageIgnore
     */
    public function toOptionArray()
    {
        return [
            ['value' => 'min', 'label' => __('Min')],
            ['value' => 'max', 'label' => __('Max')],
            ['value' => 'min_greater_zero', 'label' => __('Minimal greater zero')],
        ];
    }
}
