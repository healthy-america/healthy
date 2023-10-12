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
            ['value' => '', 'label' => __('Please select an option')],
            ['value' => 'CC', 'label' => __('Identification card')],
            ['value' => 'CE', 'label' => __("Foreigner ID")],
            ['value' => 'RUT', 'label' => __('RUT')]
        ];

        return $options;
    }
}
