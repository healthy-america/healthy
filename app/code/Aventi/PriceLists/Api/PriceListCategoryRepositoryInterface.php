<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\PriceLists\Api;

use Aventi\PriceLists\Api\Data\PriceListCategoryInterface;
use Aventi\PriceLists\Api\Data\PriceListCategorySearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

interface PriceListCategoryRepositoryInterface
{

    /**
     * Save PriceListCategory
     * @param PriceListCategoryInterface $priceListCategory
     * @return PriceListCategoryInterface
     * @throws LocalizedException
     */
    public function save(
        PriceListCategoryInterface $priceListCategory
    );

    /**
     * Retrieve PriceListCategory
     * @param string $priceListCategoryId
     * @return PriceListCategoryInterface
     * @throws LocalizedException
     */
    public function get($priceListCategoryId);

    /**
     * Retrieve PriceListCategory matching the specified criteria.
     * @param SearchCriteriaInterface $searchCriteria
     * @return PriceListCategorySearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(
        SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete PriceListCategory
     * @param PriceListCategoryInterface $priceListCategory
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(
        PriceListCategoryInterface $priceListCategory
    );

    /**
     * Delete PriceListCategory by ID
     * @param string $priceListCategoryId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById($priceListCategoryId);
}
