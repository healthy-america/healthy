<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\PriceLists\Api;

use Aventi\PriceLists\Api\Data\PriceListCustomersInterface;
use Aventi\PriceLists\Api\Data\PriceListCustomersSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

interface PriceListCustomersRepositoryInterface
{

    /**
     * Save PriceListCustomers
     * @param PriceListCustomersInterface $priceListCustomers
     * @return PriceListCustomersInterface
     * @throws LocalizedException
     */
    public function save(
        PriceListCustomersInterface $priceListCustomers
    );

    /**
     * Retrieve PriceListCustomers
     * @param string $pricelistcustomersId
     * @return PriceListCustomersInterface
     * @throws LocalizedException
     */
    public function get($pricelistcustomersId);

    /**
     * Retrieve PriceListCustomers matching the specified criteria.
     * @param SearchCriteriaInterface $searchCriteria
     * @return PriceListCustomersSearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(
        SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete PriceListCustomers
     * @param PriceListCustomersInterface $priceListCustomers
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(
        PriceListCustomersInterface $priceListCustomers
    );

    /**
     * Delete PriceListCustomers by ID
     * @param string $priceListCustomersId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById($priceListCustomersId);
}
