<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAPHistoryRequest\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

interface OrderHistoryRepositoryInterface
{

    /**
     * Save OrderHistory
     * @param \Aventi\SAPHistoryRequest\Api\Data\OrderHistoryInterface $orderHistory
     * @return \Aventi\SAPHistoryRequest\Api\Data\OrderHistoryInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Aventi\SAPHistoryRequest\Api\Data\OrderHistoryInterface $orderHistory
    );

    /**
     * Retrieve OrderHistory
     * @param string $orderhistoryId
     * @return \Aventi\SAPHistoryRequest\Api\Data\OrderHistoryInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get($orderhistoryId);

    /**
     * Retrieve OrderHistory matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Aventi\SAPHistoryRequest\Api\Data\OrderHistorySearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete OrderHistory
     * @param \Aventi\SAPHistoryRequest\Api\Data\OrderHistoryInterface $orderHistory
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Aventi\SAPHistoryRequest\Api\Data\OrderHistoryInterface $orderHistory
    );

    /**
     * Delete OrderHistory by ID
     * @param string $orderhistoryId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($orderhistoryId);
}

