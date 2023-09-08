<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAPHistoryRequest\Api\Data;

interface OrderHistoryInterface
{

    const ORDERHISTORY_ID = 'orderhistory_id';
    const UPDATED_AT = 'updated_at';
    const CREATED_AT = 'created_at';
    const JSON_RESPONSE = 'json_response';
    const JSON_BODY = 'json_body';
    const PARENT_ID = 'parent_id';
    const INCREMENT_ID = 'increment_id';

    /**
     * Get orderhistory_id
     * @return string|null
     */
    public function getOrderhistoryId();

    /**
     * Set orderhistory_id
     * @param string $orderhistoryId
     * @return $this
     */
    public function setOrderhistoryId($orderhistoryId);

    /**
     * Get parent_id
     * @return string|null
     */
    public function getParentId();

    /**
     * Set parent_id
     * @param string $parentId
     * @return $this
     */
    public function setParentId($parentId);

    /**
     * Get incrementId
     * @return string|null
     */
    public function getIncrementId();

    /**
     * Set parent_id
     * @param string $incrementId
     * @return $this
     */
    public function setIncrementId($incrementId);

    /**
     * Get json_body
     * @return string|null
     */
    public function getJsonBody();

    /**
     * Set json_body
     * @param string $jsonBody
     * @return $this
     */
    public function setJsonBody($jsonBody);

    /**
     * Get json_response
     * @return string|null
     */
    public function getJsonResponse();

    /**
     * Set json_response
     * @param string $jsonResponse
     * @return $this
     */
    public function setJsonResponse($jsonResponse);

    /**
     * Get created_at
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created_at
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Get updated_at
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set updated_at
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);
}

