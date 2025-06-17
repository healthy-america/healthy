<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\PriceLists\Model;

use Magento\Framework\Model\AbstractModel;
use Aventi\PriceLists\Api\Data\PriceListCustomersInterface;
use Aventi\PriceLists\Api\Data\PriceListCustomersInterfaceFactory;

/**
 * Class PriceListCustomers
 * @package Aventi\PriceLists\Model
 */
class PriceListCustomers extends AbstractModel implements PriceListCustomersInterface
{

    const TABLE = 'avpricelists_customers';

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(ResourceModel\PriceListCustomers::class);
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
    public function getCustomerId()
    {
        return $this->getData(self::PRICE_LIST_CUSTOMER_ID);
    }

    /**
     * @inheritDoc
     */
    public function setCustomerId($pricelistCustomerId)
    {
        return $this->setData(self::PRICE_LIST_CUSTOMER_ID, $pricelistCustomerId);
    }
}
