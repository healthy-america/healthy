<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Aventi\CityDropDown\Model\Source;

class Status implements \Magento\Framework\Data\OptionSourceInterface
{

    public function toOptionArray(): array
    {
        return [
            ['value' => 0, 'label' => __('Inactive')],
            ['value' => 1, 'label' => __('Active')]
        ];
    }
}
