<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\Checkout\Model\Source\Config;

use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;
use Magento\Eav\Model\Entity\Attribute\Source\SourceInterface;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * @class DocTypeOptions
 */
class TributaryInformation extends AbstractSource implements OptionSourceInterface, SourceInterface
{
    /**
     * @return array[]
     */
    public function getAllOptions(): array
    {
        return [
            ['value' =>  'ZZ', 'label' => __('Do not apply')],
            ['value' =>  '01', 'label' => __('IVA')],
            ['value' =>  '04', 'label' => __('INC')],
            ['value' =>  'ZA', 'label' => __('IVA e INC')]
        ];
    }
}
