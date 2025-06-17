<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\PriceLists\Api;

use Aventi\PriceLists\Api\Data\PriceListInterface;
use Aventi\PriceLists\Api\Data\PriceListSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

interface PriceListRepositoryInterface
{

    /**
     * Save PriceList
     * @param PriceListInterface $priceList
     * @return PriceListInterface
     * @throws LocalizedException
     */
    public function save(
        PriceListInterface $priceList
    );

    /**
     * Retrieve PriceList by name
     * @param string $priceListName
     * @return PriceListInterface
     * @throws LocalizedException
     */
    public function getByName($priceListName);

    /**
     * Retrieve PriceList
     * @param string $pricelistId
     * @return PriceListInterface
     * @throws LocalizedException
     */
    public function get($pricelistId);

    /**
     * Retrieve PriceList matching the specified criteria.
     * @param SearchCriteriaInterface $searchCriteria
     * @return PriceListSearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(
        SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete PriceList
     * @param PriceListInterface $priceList
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(
        PriceListInterface $priceList
    );

    /**
     * Delete PriceList by ID
     * @param string $pricelistId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById($pricelistId);
}
