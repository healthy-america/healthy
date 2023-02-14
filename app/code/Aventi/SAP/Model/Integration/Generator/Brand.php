<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Model\Integration\Generator;

class Brand
{
    public static function getBrands(int $init = 0, int $num = 10): array
    {
        $data = [];
        for ($i = $init; $i < $num; $i++) {
            $data[] = [
                'FirmName' => 'BRAND ' . ($i + 1),
                'FirmCode' => 'BRAND' . str_pad((string)($i + 1), 5, '0', STR_PAD_LEFT)
            ];
        }
        return $data;
    }

    /**
     * @throws \Exception
     */
    public static function getRandomBrand(int $init = 1, int $num = 10): array
    {
        $data = [];
        $i = random_int($init, $num);
        $data['FirmName'] = 'BRAND ' . $i;
        $data['FirmCode'] = 'BRAND' . str_pad((string)$i, 5, '0', STR_PAD_LEFT);
        return $data;
    }
}
