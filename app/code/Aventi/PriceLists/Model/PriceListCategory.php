<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\PriceLists\Model;

use Aventi\PriceLists\Api\Data\PriceListCategoryInterface;
use Aventi\PriceLists\Api\Data\PriceListCategoryInterfaceFactory;
use Magento\Framework\Model\AbstractModel;

/**
 * Class PriceListCategory
 * @package Aventi\PriceLists\Model
 */
class PriceListCategory extends AbstractModel implements PriceListCategoryInterface
{

    const TABLE = 'avpricelists_category';

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(ResourceModel\PriceListCategory::class);
    }

    /**
     * @inheritDoc
     */
    public function getEntityId()
    {
        return $this->getData(self::PRICE_LIST_CATEGORY_ENTITY_ID);
    }

    /**
     * @inheritDoc
     */
    public function setEntityId($entityId)
    {
        return $this->setData(self::PRICE_LIST_CATEGORY_ENTITY_ID, $entityId);
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
    public function getCategoryId()
    {
        return $this->getData(self::PRICE_LIST_CATEGORY_ID);
    }

    /**
     * @inheritDoc
     */
    public function setCategoryId($categoryId)
    {
        return $this->setData(self::PRICE_LIST_CATEGORY_ID, $categoryId);
    }

    /**
     * @inheritDoc
     */
    public function getCategoryPrice()
    {
        return $this->getData(self::PRICE_LIST_CATEGORY_PRICE);
    }

    /**
     * @inheritDoc
     */
    public function setCategoryPrice($priceListCategoryPrice)
    {
        return $this->setData(self::PRICE_LIST_CATEGORY_PRICE, $priceListCategoryPrice);
    }

    /**
     * @inheritDoc
     */
    public function getCategoryRuleType()
    {
        return $this->getData(self::PRICE_LIST_CATEGORY_RULE_TYPE);
    }

    /**
     * @inheritDoc
     */
    public function setCategoryRuleType($priceListCategoryRuleType)
    {
        return $this->setData(self::PRICE_LIST_CATEGORY_RULE_TYPE, $priceListCategoryRuleType);
    }
}
