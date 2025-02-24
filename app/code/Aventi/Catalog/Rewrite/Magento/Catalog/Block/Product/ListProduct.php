<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\Catalog\Rewrite\Magento\Catalog\Block\Product;

use Magento\Catalog\Model\Product;

class ListProduct extends \Magento\Catalog\Block\Product\ListProduct
{
    /**
     * Get bundle options
     *
     * @param Product $product
     * @return array
     */
    public function getBundleOptions(Product $product): array
    {
        $options = $product->getTypeInstance()
            ->getSelectionsCollection($product->getTypeInstance()
                ->getOptionsIds($product), $product);
        $optionsValues = [];
        foreach ($options as $option) {
            if (isset($optionsValues[$option->getOptionId()])) {
                continue;
            }
            $optionsValues[$option->getOptionId()] = (int)$option->getSelectionId();
        }

        return $optionsValues;
    }
}
