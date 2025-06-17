<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\PriceLists\Model;

use Aventi\PriceLists\Api\Data\PriceListInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Class PriceList
 * @package Aventi\PriceLists\Model
 */
class PriceList extends AbstractModel implements PriceListInterface
{
    const TABLE  = 'avpricelists_pricelist';

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(ResourceModel\PriceList::class);
    }

    /**
     * @inheritDoc
     */
    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * @inheritDoc
     */
    public function setEntityId($entityId)
    {
        return $this->setData(self::ENTITY_ID, $entityId);
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * @inheritDoc
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * @inheritDoc
     */
    public function getDescription()
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * @inheritDoc
     */
    public function setDescription($description)
    {
        return $this->setData(self::DESCRIPTION, $description);
    }
}
