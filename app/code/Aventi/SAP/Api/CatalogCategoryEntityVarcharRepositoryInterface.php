<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface CatalogCategoryEntityVarcharRepositoryInterface
{

    /**
     * Save catalog_category_entity_varchar
     * @param \Aventi\SAP\Api\Data\CatalogCategoryEntityVarcharInterface $catalogCategoryEntityVarchar
     * @return \Aventi\SAP\Api\Data\CatalogCategoryEntityVarcharInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(\Aventi\SAP\Api\Data\CatalogCategoryEntityVarcharInterface $catalogCategoryEntityVarchar);

    /**
     * Retrieve catalog_category_entity_varchar
     * @param string $catalogCategoryEntityVarcharId
     * @return \Aventi\SAP\Api\Data\CatalogCategoryEntityVarcharInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($catalogCategoryEntityVarcharId);

    /**
     * Retrieve catalog_category_entity_varchar matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Aventi\SAP\Api\Data\CatalogCategoryEntityVarcharSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete catalog_category_entity_varchar
     * @param \Aventi\SAP\Api\Data\CatalogCategoryEntityVarcharInterface $catalogCategoryEntityVarchar
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(\Aventi\SAP\Api\Data\CatalogCategoryEntityVarcharInterface $catalogCategoryEntityVarchar);

    /**
     * Delete catalog_category_entity_varchar by ID
     * @param string $catalogCategoryEntityVarcharId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($catalogCategoryEntityVarcharId);
}
