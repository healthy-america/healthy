<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Model\Integration\Check\Product;

use Aventi\SAP\Model\Integration\Check\Check as AbstractCheck;

class CheckBrand extends AbstractCheck
{
    public function getCurrentData($data)
    {
        $currentData = [
            'name' => $data->name
        ];
        return $currentData;
    }

    public function getHeadData($item)
    {
        $headData = [
            'name' => $item->getData('name')
        ];
        return $headData;
    }
}
