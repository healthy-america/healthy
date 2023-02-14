<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Api\Data;

interface CatalogCategoryEntityVarcharInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{
    const VALUE_ID = 'value_id';
    const STORE_ID = 'store_id';
    const VALUE = 'value';
    const ENTITY_ID = 'entity_id';
    const ATTRIBUTE_ID = 'attribute_id';

    /**
     * Get value_id
     * @return string|null
     */
    public function getValueId();

    /**
     * Set value_id
     * @param string $valueId
     * @return \Aventi\SAP\Api\Data\CatalogCategoryEntityVarcharInterface
     */
    public function setValueId($valueId);

    /**
     * Get attribute_id
     * @return string|null
     */
    public function getAttributeId();

    /**
     * Set attribute_id
     * @param string $attributeId
     * @return \Aventi\SAP\Api\Data\CatalogCategoryEntityVarcharInterface
     */
    public function setAttributeId($attributeId);

    /**
     * Get store_id
     * @return string|null
     */
    public function getStoreId();

    /**
     * Set store_id
     * @param string $storeId
     * @return \Aventi\SAP\Api\Data\CatalogCategoryEntityVarcharInterface
     */
    public function setStoreId($storeId);

    /**
     * Get entity_id
     * @return string|null
     */
    public function getEntityId();

    /**
     * Set entity_id
     * @param string $entityId
     * @return \Aventi\SAP\Api\Data\CatalogCategoryEntityVarcharInterface
     */
    public function setEntityId($entityId);

    /**
     * Get value
     * @return string|null
     */
    public function getValue();

    /**
     * Set value
     * @param string $value
     * @return \Aventi\SAP\Api\Data\CatalogCategoryEntityVarcharInterface
     */
    public function setValue($value);
}
