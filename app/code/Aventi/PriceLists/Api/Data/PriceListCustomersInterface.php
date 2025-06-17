<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\PriceLists\Api\Data;

interface PriceListCustomersInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    const PRICE_LIST_ENTITY_ID = 'entity_id';
    const PRICE_LIST_ID = 'price_list_id';
    const PRICE_LIST_CUSTOMER_ID = 'customer_id';

    /**
     * Get entity_id
     * @return string|null
     */
    public function getEntityId();

    /**
     * Set entity_id
     * @param string $entityId
     * @return PriceListCustomersInterface
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
     * @return PriceListCustomersInterface
     */
    public function setPriceListId($priceListId);

    /**
     * Get customer_id
     * @return string|null
     */
    public function getCustomerId();

    /**
     * Set customer_id
     * @param string $pricelistCustomerId
     * @return PriceListCustomersInterface
     */
    public function setCustomerId($pricelistCustomerId);
}
