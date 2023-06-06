<?php
/**
 * This file is part of the Klarna Core module
 *
 * (c) Klarna Bank AB (publ)
 *
 * For the full copyright and license information, please view the NOTICE
 * and LICENSE files that were distributed with this source code.
 */

namespace Bancolombia\Wompi\Model\System\Config;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Returning the values for the api mode
 */
class Mode implements OptionSourceInterface
{
    /**
     * Return array of options as value-label pairs
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        return[
            [
                'value' => '1',
                'label' => __('Sandbox')
            ],
            [
                'value' => '0',
                'label' => __('Producción')
            ]
        ];
    }
}
