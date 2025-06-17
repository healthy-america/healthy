<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\PriceLists\Model;

use Aventi\PriceLists\Api\Data\PriceListGroupsInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class PriceListGroups
 * @package Aventi\PriceLists\Model
 */
class PriceListGroups extends AbstractModel implements PriceListGroupsInterface
{
    const TABLE = 'avpricelists_customergroups';

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(ResourceModel\PriceListGroups::class);
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
    public function setEntityId($entityId)
    {
        return $this->setData(self::PRICE_LIST_ENTITY_ID, $entityId);
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
    public function getCustomerGroupId()
    {
        return $this->getData(self::PRICE_LIST_CUSTOMER_GROUP_ID);
    }

    /**
     * @inheritDoc
     */
    public function setCustomerGroupId($pricelistCustomerGroupId)
    {
        return $this->setData(self::PRICE_LIST_CUSTOMER_GROUP_ID, $pricelistCustomerGroupId);
    }
}
