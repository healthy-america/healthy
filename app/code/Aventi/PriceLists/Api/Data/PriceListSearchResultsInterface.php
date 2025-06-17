<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\PriceLists\Api\Data;

interface PriceListSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get PriceList list.
     * @return PriceListInterface[]
     */
    public function getItems();

    /**
     * Set name list.
     * @param PriceListInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
