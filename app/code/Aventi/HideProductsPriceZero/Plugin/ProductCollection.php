<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\HideProductsPriceZero\Plugin;

use Magento\Catalog\Model\ResourceModel\Product\Collection;

class ProductCollection
{
    /**
     * BeforeLoad
     * @param Collection $subject
     */
    public function beforeLoad(
        Collection $subject
    ) {
        $subject->addAttributeToFilter('price', ['gt' => 0]);
    }
}
