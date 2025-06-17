<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\PriceLists\Api\Data;

interface PriceListGroupsInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    const PRICE_LIST_ENTITY_ID = 'entity_id';
    const PRICE_LIST_ID = 'price_list_id';
    const PRICE_LIST_CUSTOMER_GROUP_ID = 'customer_group_id';

    /**
     * Get entity_id
     * @return string|null
     */
    public function getEntityId();

    /**
     * Set entity_id
     * @param string $entityId
     * @return PriceListGroupsInterface
     */
    public function setEntityId($entityId);

    /**
     * Get price_list_id
     * @return string|null
     */
    public function getPriceListId();

    /**
     * Set price_list_id
     * @param string $priceListId
     * @return PriceListGroupsInterface
     */
    public function setPriceListId($priceListId);

    /**
     * Get customer_group_id
     * @return string|null
     */
    public function getCustomerGroupId();

    /**
     * Set customer_group_id
     * @param string $pricelistCustomerGroupId
     * @return PriceListGroupsInterface
     */
    public function setCustomerGroupId($pricelistCustomerGroupId);
}
