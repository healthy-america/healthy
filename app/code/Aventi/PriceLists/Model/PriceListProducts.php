<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\PriceLists\Model;

use Magento\Framework\Model\AbstractModel;
use Aventi\PriceLists\Api\Data\PriceListProductsInterfaceFactory;
use Aventi\PriceLists\Api\Data\PriceListProductsInterface;

/**
 * Class PriceListProducts
 * @package Aventi\PriceLists\Model
 */
class PriceListProducts extends AbstractModel implements PriceListProductsInterface
{

    const TABLE = 'avpricelists_products';

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(ResourceModel\PriceListProducts::class);
    }

    /**
     * @inheritDoc
     */
    public function getEntityId()
    {
        return $this->getData(self::PRICE_LIST_ENTITY_ID);
    }

    /**
     * @inheritDoc
     */
    public function setEntityId($entity_id)
    {
        return $this->setData(self::PRICE_LIST_ENTITY_ID, $entity_id);
    }

    /**
     * @inheritDoc
     */
    public function getPriceListId()
    {
        return $this->getData(self::PRICE_LIST_ID);
    }

    /**
     * @inheritDoc
     */
    public function setPriceListId($priceListId)
    {
        return $this->setData(self::PRICE_LIST_ID, $priceListId);
    }

    /**
     * @inheritDoc
     */
    public function getProductId()
    {
        return $this->getData(self::PRICE_LIST_PRODUCT_ID);
    }

    /**
     * @inheritDoc
     */
    public function setProductId($priceListProductId)
    {
        return $this->setData(self::PRICE_LIST_PRODUCT_ID, $priceListProductId);
    }

    /**
     * @inheritDoc
     */
    public function getProductPrice()
    {
        return $this->getData(self::PRICE_LIST_PRODUCT_PRICE);
    }

    /**
     * @inheritDoc
     */
    public function setProductPrice($priceListProductPrice)
    {
        return $this->setData(self::PRICE_LIST_PRODUCT_PRICE, $priceListProductPrice);
    }

    /**
     * @inheritDoc
     */
    public function getProductPriceSug()
    {
        return $this->getData(self::PRICE_LIST_PRODUCT_PRICE_SUG);
    }

    /**
     * @inheritDoc
     */
    public function setProductPriceSug($priceListProductPriceSug)
    {
        return $this->setData(self::PRICE_LIST_PRODUCT_PRICE_SUG, $priceListProductPriceSug);
    }

    /**
     * @inheritDoc
     */
    public function getProductRuleType()
    {
        return $this->getData(self::PRICE_LIST_PRODUCT_RULE_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setProductRuleType($priceListProductRuleType)
    {
        return $this->setData(self::PRICE_LIST_PRODUCT_RULE_TYPE, $priceListProductRuleType);
    }
}
