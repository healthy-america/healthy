<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Model\Integration\Generator;

class Product
{
    public static function getProducts(int $init = 0, int $num = 50): array
    {
        $data = [];

        for ($i = $init; $i < $num; $i++) {
            $product = [
                'Sku' => 'SKUTEST' . str_pad((string)($i + 1), 10, '0', STR_PAD_LEFT),
                'Name' => 'PRODUCT ' . ($i + 1),
                'Tax' => 'IVA GE10',
                'frozenFor' => 'N',
                'Description' => 'Categoria 200',
                'LongDescription' => 'Long Description',
            ];
            $categories = \Aventi\SAP\Model\Integration\Generator\Category::getRandomCategory();
            $brand = \Aventi\SAP\Model\Integration\Generator\Brand::getRandomBrand();

            $product =  $product + $brand + $categories;

            $data[] = $product;
         }

        return $data;
    }
}
