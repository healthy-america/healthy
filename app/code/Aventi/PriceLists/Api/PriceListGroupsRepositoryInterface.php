<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\PriceLists\Api;

use Aventi\PriceLists\Api\Data\PriceListGroupsInterface;
use Aventi\PriceLists\Api\Data\PriceListGroupsSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

interface PriceListGroupsRepositoryInterface
{

    /**
     * Save PriceListGroups
     * @param PriceListGroupsInterface $priceListGroups
     * @return PriceListGroupsInterface
     * @throws LocalizedException
     */
    public function save(
        PriceListGroupsInterface $priceListGroups
    );

    /**
     * Retrieve PriceListGroups
     * @param string $pricelistgroupsId
     * @return PriceListGroupsInterface
     * @throws LocalizedException
     */
    public function get($pricelistgroupsId);

    /**
     * Retrieve PriceListGroups matching the specified criteria.
     * @param SearchCriteriaInterface $searchCriteria
     * @return PriceListGroupsSearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(
        SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete PriceListGroups
     * @param PriceListGroupsInterface $priceListGroups
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(
        PriceListGroupsInterface $priceListGroups
    );

    /**
     * Delete PriceListGroups by ID
     * @param string $pricelistgroupsId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById($pricelistgroupsId);
}
