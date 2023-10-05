<?php

declare(strict_types=1);

namespace Aventi\AventiTheme\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class CustomerIdentificationTypeOptions implements OptionSourceInterface
{
    /**
     * Gets options.
     *
     * @return array[]
     */
    public function toOptionArray()
    {
        $options = [
            ['value' => '', 'label' => '---' . __('Identification type') . '---'],
            ['value' => 'CC', 'label' => __('Identification card')],
            ['value' => 'CE', 'label' => __("Foreigner's identification card")],
            ['value' => 'RUT', 'label' => __('RUT')]
        ];

        return $options;
    }
}
