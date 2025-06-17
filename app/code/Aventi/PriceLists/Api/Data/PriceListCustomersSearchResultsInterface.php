<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\PriceLists\Api\Data;

interface PriceListCustomersSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get PriceListCustomers list.
     * @return PriceListCustomersInterface[]
     */
    public function getItems();

    /**
     * Set price_list_id list.
     * @param PriceListCustomersInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
