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
            ['value' => '', 'label' => __('---Identification type---')],
            ['value' => 'C.C', 'label' => __('C.C')],
            ['value' => 'C.E', 'label' => __('C.E')],
        ];

        return $options;
    }
}
