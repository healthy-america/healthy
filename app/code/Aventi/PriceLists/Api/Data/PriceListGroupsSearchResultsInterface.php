<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\PriceLists\Api\Data;

interface PriceListGroupsSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get PriceListCustomers list.
     * @return PriceListGroupsInterface[]
     */
    public function getItems();

    /**
     * Set price_list_id list.
     * @param PriceListGroupsInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
