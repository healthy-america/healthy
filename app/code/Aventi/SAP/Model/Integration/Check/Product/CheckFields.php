<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Model\Integration\Check\Product;

use Aventi\SAP\Model\Integration\Check\Check as AbstractCheck;
use Aventi\SAP\Helper\Data;

class CheckFields extends AbstractCheck
{
    public function __construct(
        private Data $dataHelper
    ) {
    }

    /**
     * GetCurrentData
     *
     * @param object $data
     * @return array
     */
    public function getCurrentData($data): array
    {
        return [
            'name' => $data->name,
            'tax_class_id' => $data->tax_class_id,
            'status' => $data->status,
            'mgs_brand' => $data->mgs_brand,
            'description' => $data->description
            // 'website_code' => $data->website_code,
        ];
    }

    /**
     * GetHeadData
     *
     * @param $item
     * @param $data
     * @return array
     */
    public function getHeadData($item, $data): array
    {
        return [
            'name' => $item->getData('name') ?? '',
            'tax_class_id' => $item->getData('tax_class_id') ?? '',
            'status' => (int)$item->getData('status') ?? '',
            'mgs_brand' => $item->getData('mgs_brand') ?? '',
            'description' => $item->getDescription() ?? ''
            // 'website_code' => $this->dataHelper->getWebsiteName(implode(',', $item->getWebsiteIds())) ?? ''
        ];
    }

    /**
     * Checks categories product and retrieves differences.
     *
     * @param object $data
     * @param object $product
     * @return array|false returns FALSE if there aren't differences, otherwise returns an array containing the data.
     */
    public function checkCategories(object $data, object $product): bool|array
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
