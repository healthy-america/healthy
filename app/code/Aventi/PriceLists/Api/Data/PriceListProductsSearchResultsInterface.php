<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\PriceLists\Api\Data;

interface PriceListProductsSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get PriceListProducts list.
     * @return PriceListProductsInterface[]
     */
    public function getItems();

    /**
     * Set price_list_id list.
     * @param PriceListProductsInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
