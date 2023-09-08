<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAPHistoryRequest\Api\Data;

interface OrderHistorySearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{

    /**
     * Get OrderHistory list.
     * @return \Aventi\SAPHistoryRequest\Api\Data\OrderHistoryInterface[]
     */
    public function getItems();

    /**
     * Set parent_id list.
     * @param \Aventi\SAPHistoryRequest\Api\Data\OrderHistoryInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

