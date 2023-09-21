<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\CityDropDown\Model\Options;

class City implements \Magento\Framework\Data\OptionSourceInterface
{

    /**
     * @return array[]
     */
    public function toOptionArray(): array
    {
        return [
            [
                'label' => __('Please select'),
                'value' => ''
            ]
        ];
    }
}
