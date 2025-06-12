<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Model\Integration\Check\Product;

use Aventi\SAP\Model\Integration\Check\Check as AbstractCheck;

class CheckStock extends AbstractCheck
{
    public function getCurrentData($data)
    {
        $currentData = [
            'qty' => $data->qty
        ];
        return $currentData;
    }

    public function getHeadData($item)
    {
        $headData = [
            'qty' => $item->getQuantity()
        ];
        return $headData;
    }
}
