<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAPHistoryRequest\Model;

use Aventi\SAPHistoryRequest\Api\Data\OrderHistoryInterface;
use Magento\Framework\Model\AbstractModel;

class OrderHistory extends AbstractModel implements OrderHistoryInterface
{
    const TABLE = 'sales_order_sap_history_request';

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(\Aventi\SAPHistoryRequest\Model\ResourceModel\OrderHistory::class);
    }

    /**
     * @inheritDoc
     */
    public function getOrderhistoryId()
    {
        return $this->getData(self::ORDERHISTORY_ID);
    }

    /**
     * @inheritDoc
     */
    public function setOrderhistoryId($orderhistoryId)
    {
        return $this->setData(self::ORDERHISTORY_ID, $orderhistoryId);
    }

    /**
     * @inheritDoc
     */
    public function getParentId()
    {
        return $this->getData(self::PARENT_ID);
    }

    /**
     * @inheritDoc
     */
    public function setParentId($parentId)
    {
        return $this->setData(self::PARENT_ID, $parentId);
    }

    /**
     * @inheritDoc
     */
    public function getIncrementId()
    {
        return $this->getData(self::INCREMENT_ID);
    }

    /**
     * @inheritDoc
     */
    public function setIncrementId($incrementId)
    {
        return $this->setData(self::INCREMENT_ID, $incrementId);
    }

    /**
     * @inheritDoc
     */
    public function getJsonBody()
    {
        return $this->getData(self::JSON_BODY);
    }

    /**
     * @inheritDoc
     */
    public function setJsonBody($jsonBody)
    {
        return $this->setData(self::JSON_BODY, $jsonBody);
    }

    /**
     * @inheritDoc
     */
    public function getJsonResponse()
    {
        return $this->getData(self::JSON_RESPONSE);
    }

    /**
     * @inheritDoc
     */
    public function setJsonResponse($jsonResponse)
    {
        return $this->setData(self::JSON_RESPONSE, $jsonResponse);
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @inheritDoc
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}

