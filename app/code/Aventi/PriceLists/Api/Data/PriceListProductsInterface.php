<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\PriceLists\Api\Data;

interface PriceListProductsInterface extends \Magento\Framework\Api\ExtensibleDataInterface
{

    const PRICE_LIST_ENTITY_ID = 'entity_id';
    const PRICE_LIST_ID = 'price_list_id';
    const PRICE_LIST_PRODUCT_ID = 'product_id';
    const PRICE_LIST_PRODUCT_PRICE = 'product_price';
    const PRICE_LIST_PRODUCT_PRICE_SUG = 'product_price_sug';
    const PRICE_LIST_PRODUCT_RULE_TYPE= 'product_rule_type';

    /**
     * Get entity_id
     * @return string|null
     */
    public function getEntityId();

    /**
     * Set entity_id
     * @param string entity_id
     * @return PriceListProductsInterface
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
     * @return PriceListProductsInterface
     */
    public function setPriceListId($priceListId);

    /**
     * Get product_id
     * @return string|null
     */
    public function getProductId();

    /**
     * Set product_id
     * @param string $priceListProductId
     * @return PriceListProductsInterface
     */
    public function setProductId($priceListProductId);

    /**
     * Get product_price
     * @return string|null
     */
    public function getProductPrice();

    /**
     * Set product_price
     * @param string $priceListProductPrice
     * @return PriceListProductsInterface
     */
    public function setProductPrice($priceListProductPrice);

    /**
     * Get product_price
     * @return string|null
     */
    public function getProductPriceSug();

    /**
     * Set product_price
     * @param string $priceListProductPriceSug
     * @return PriceListProductsInterface
     */
    public function setProductPriceSug($priceListProductPriceSug);

    /**
     * Get product_rule_type
     * @return string|null
     */
    public function getProductRuleType();

    /**
     * Set product_rule_type
     * @param string $priceListProductRuleType
     * @return PriceListProductsInterface
     */
    public function setProductRuleType($priceListProductRuleType);
}
