<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Model\Integration\Check\Product;

use Aventi\SAP\Model\Integration\Check\Check as AbstractCheck;

class CheckFields extends AbstractCheck
{
    public function getCurrentData($data)
    {
        $currentData = [
            'name' => $data->name,
            'tax_class_id' => $data->tax_class_id,
            'status' =>  $data->status,
            'mgs_brand' => $data->mgs_brand,
            //'long_description' => $data->long_description,
            'short_description' => $data->short_description,
        ];

        return $currentData;
    }

    public function getHeadData($item, $data)
    {
        $headData = [
            'name' => $item->getData('name') ?? '',
            'tax_class_id' => $item->getData('tax_class_id') ?? '',
            'status' => (int)$item->getData('status') ?? '',
            'mgs_brand' => $item->getData('mgs_brand') ?? '',
            //'long_description' => $item->getDescription() ?? '',
            'short_description' => $item->getShortDescription() ?? ''
        ];

        return $headData;
    }

    /**
     * Checks categories product and retrieves differences.
     * @param object $data
     * @param object $product
     * @return array|false returns <b>FALSE</b> if there aren't
     * differences, otherwise returns an array containing the data.
     */
    public function checkCategories(object $data, object $product)
    {
        $arrayCatWs = [];
        $arrayCatProd = [];
        if (is_array($data->category_ids)) {
            $arrayCatWs = array_values($data->category_ids);
        }
        if (is_array($product->getCategoryIds())) {
            $arrayCatProd = array_values($product->getCategoryIds());
        }
        $categoryDiff = array_diff($arrayCatWs, $arrayCatProd);

        if (empty($categoryDiff)) {
            return false;
        }
        return $categoryDiff;
    }
}
