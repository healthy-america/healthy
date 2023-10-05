<?php

declare(strict_types=1);

namespace Aventi\AventiTheme\Model\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

class CustomerTypeOptions implements OptionSourceInterface
{
    /**
     * Gets options.
     *
     * @return array[]
     */
    public function toOptionArray()
    {
        $options = [
            ['value' => '', 'label' => '---' . __('Customer type') . '---'],
            ['value' => 'Natural', 'label' => __('Natural')],
            ['value' => 'Legal', 'label' => __('Legal')],
        ];

        return $options;
    }
}
