<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Model\Integration\Save\Product;

use Aventi\SAP\Logger\Logger;
use Aventi\SAP\Model\Integration\Save\Save as InterfaceSave;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Framework\Exception\LocalizedException;

class Save implements InterfaceSave
{
    /**
     * @param Logger $logger
     */
    public function __construct(
        private Logger $logger
    ) {
    }

    /**
     * Saves the fields with the values to update. The fields can be the Product or Price.
     *
     * @param ProductInterface $item The source (Product or Price) to update.
     * @param array $checkData The data previously checked with the fields.
     */
    public function saveFields(ProductInterface $item, array $checkData): void
    {
        foreach ($checkData as $key => $field) {
            $item->setData($key, $field);
            try {
                $item->getResource()->saveAttribute($item, $key);
            } catch (LocalizedException $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }
}
