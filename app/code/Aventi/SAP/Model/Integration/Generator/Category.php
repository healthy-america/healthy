<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Model\Integration\Generator;

class Category
{
    public static function getCategories(int $iterations = 75): array
    {
        $data = [];
        $flag1 = $flag2  = $flag3 = 0;
        $id1 = $id2 = 1;

        for ($i = 0; $i < $iterations; $i++) {
            $data[] = [
                'Cod_Categoria_1' => str_pad((string)$id1, 5, '0', STR_PAD_LEFT),
                'Des_Categoria_1' => 'Categoria ' . $id1,
                'Cod_Categoria_2' => str_pad((string)$id2, 5, '0', STR_PAD_LEFT),
                'Des_Categoria_2' => 'Categoria ' . $id2,
                'Cod_Categoria_3' => str_pad((string)($i + 1), 5, '0', STR_PAD_LEFT),
                'Des_Categoria_3' => 'Categoria ' . ($i + 1)
            ];

            $flag1++;
            $flag2++;
            $flag3++;

            if ($flag1 >= 25) {
                $flag1 = 0;
                $id1++;
            }

            if ($flag2 >= 5) {
                $flag2 = 0;
                $id2++;
            }
        }

        return $data;
    }

    /**
     * @throws \Exception
     */
    public static function getRandomCategory(): array
    {
        $data = [];

        $id1 = random_int(1, 3);
        $id2 = random_int(1, 15);
        $i = random_int(1, 75);

        $data['Cod_Categoria_1'] = str_pad((string)$id1, 5, '0', STR_PAD_LEFT);
        $data['Des_Categoria_1'] = 'Categoria ' . $id1;
        $data['Cod_Categoria_2'] = str_pad((string)$id2, 5, '0', STR_PAD_LEFT);
        $data['Des_Categoria_2'] = 'Categoria ' . $id2;
        $data['Cod_Categoria_3'] = str_pad((string)($i + 1), 5, '0', STR_PAD_LEFT);
        $data['Des_Categoria_3'] = 'Categoria ' . ($i + 1);

        return $data;
    }
}
