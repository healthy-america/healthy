<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Model\Integration\Generator;

class Stock
{
    public static function getStock(int $init = 0, int $num = 50): array
    {
        $data = [];
        for ($i = $init; $i < $num; $i++) {
            $data[] = [
                'ItemCode' => 'SKUTEST' . str_pad((string)($i + 1), 10, '0', STR_PAD_LEFT),
                'Stock' => rand(0, 200),
                'WhsCode' => 'default'
            ];
        }
        return $data;
    }
}
