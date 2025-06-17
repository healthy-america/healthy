<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\PriceLists\Api\Data;

interface PriceListCategoryInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    const PRICE_LIST_CATEGORY_ENTITY_ID = 'entity_id';
    const PRICE_LIST_ID = 'price_list_id';
    const PRICE_LIST_CATEGORY_ID = 'category_id';
    const PRICE_LIST_CATEGORY_PRICE = 'category_price';
    const PRICE_LIST_CATEGORY_RULE_TYPE= 'category_rule_type';

    /**
     * Get entity_id
     * @return string|null
     */
    public function getEntityId();

    /**
     * Set entity_id
     * @param string $entityId
     * @return PriceListCategoryInterface
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
     * @return PriceListCategoryInterface
     */
    public function setPriceListId($priceListId);

    /**
     * Get category_id
     * @return string|null
     */
    public function getCategoryId();

    /**
     * Set category_id
     * @param string $priceListCategoryId
     * @return PriceListCategoryInterface
     */
    public function setCategoryId($priceListCategoryId);

    /**
     * Get category_price
     * @return string|null
     */
    public function getCategoryPrice();

    /**
     * Set category_price
     * @param string $priceListCategoryPrice
     * @return PriceListCategoryInterface
     */
    public function setCategoryPrice($priceListCategoryPrice);

    /**
     * Get category_rule_type
     * @return string|null
     */
    public function getCategoryRuleType();

    /**
     * Set category_rule_type
     * @param string $priceListCategoryRuleType
     * @return PriceListCategoryInterface
     */
    public function setCategoryRuleType($priceListCategoryRuleType);
}
