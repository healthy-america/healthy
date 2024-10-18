<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Model\Integration\Save;

use Magento\Catalog\Api\Data\ProductInterface;

interface Save
{
    /*
     * @param object params => {itemInterface => val, itemRepositoryInterface => val, checkData => val,....}
     */
    public function saveFields(ProductInterface $item, array $checkData);
}
