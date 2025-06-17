<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\PriceLists\Api;

use Aventi\PriceLists\Api\Data\PriceListProductsInterface;
use Aventi\PriceLists\Api\Data\PriceListProductsSearchResultsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

interface PriceListProductsRepositoryInterface
{

    /**
     * Save PriceListProducts
     * @param PriceListProductsInterface $priceListProducts
     * @return PriceListProductsInterface
     * @throws LocalizedException
     */
    public function save(
        PriceListProductsInterface $priceListProducts
    );

    /**
     * Retrieve PriceListProducts
     * @param string $pricelistproductsId
     * @return PriceListProductsInterface
     * @throws LocalizedException
     */
    public function get($pricelistproductsId);

    /**
     * Retrieve PriceListProducts matching the specified criteria.
     * @param SearchCriteriaInterface $searchCriteria
     * @return PriceListProductsSearchResultsInterface
     * @throws LocalizedException
     */
    public function getList(
        SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete PriceListProducts
     * @param PriceListProductsInterface $priceListProducts
     * @return bool true on success
     * @throws LocalizedException
     */
    public function delete(
        PriceListProductsInterface $priceListProducts
    );

    /**
     * Delete PriceListProducts by ID
     * @param string $pricelistproductsId
     * @return bool true on success
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function deleteById($pricelistproductsId);
}
