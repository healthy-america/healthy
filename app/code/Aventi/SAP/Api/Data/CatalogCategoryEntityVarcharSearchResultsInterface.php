<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Api\Data;

interface CatalogCategoryEntityVarcharSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get catalog_category_entity_varchar list.
     * @return \Aventi\SAP\Api\Data\CatalogCategoryEntityVarcharInterface[]
     */
    public function getItems();

    /**
     * Set value_id list.
     * @param \Aventi\SAP\Api\Data\CatalogCategoryEntityVarcharInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
