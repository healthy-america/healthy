<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\PriceLists\Model;

use Aventi\PriceLists\Api\Data\PriceListProductsInterface;
use Aventi\PriceLists\Api\Data\PriceListProductsInterfaceFactory;
use Aventi\PriceLists\Api\Data\PriceListProductsSearchResultsInterfaceFactory;
use Aventi\PriceLists\Api\PriceListProductsRepositoryInterface;
use Aventi\PriceLists\Model\ResourceModel\PriceListProducts as ResourcePriceListProducts;
use Aventi\PriceLists\Model\ResourceModel\PriceListProducts\CollectionFactory as PriceListProductsCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class PriceListProductsRepository
 * @package Aventi\PriceLists\Model
 */
class PriceListProductsRepository implements PriceListProductsRepositoryInterface
{
    /**
     * @var ResourcePriceListProducts
     */
    protected $resource;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var PriceListProductsInterfaceFactory
     */
    protected $priceListProductsFactory;

    /**
     * @var PriceListProductsCollectionFactory
     */
    protected $priceListProductsCollectionFactory;

    /**
     * @var PriceListProducts
     */
    protected $searchResultsFactory;

    /**
     * @param ResourcePriceListProducts $resource
     * @param PriceListProductsInterfaceFactory $priceListProductsFactory
     * @param PriceListProductsCollectionFactory $priceListProductsCollectionFactory
     * @param PriceListProductsSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourcePriceListProducts $resource,
        PriceListProductsInterfaceFactory $priceListProductsFactory,
        PriceListProductsCollectionFactory $priceListProductsCollectionFactory,
        PriceListProductsSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->priceListProductsFactory = $priceListProductsFactory;
        $this->priceListProductsCollectionFactory = $priceListProductsCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritDoc
     */
    public function save(PriceListProductsInterface $priceListProducts)
    {
        try {
            $this->resource->save($priceListProducts);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the priceListProducts: %1',
                $exception->getMessage()
            ));
        }
        return $priceListProducts;
    }

    /**
     * @inheritDoc
     */
    public function get($pricelistproductsId)
    {
        $priceListProducts = $this->priceListProductsFactory->create();
        $this->resource->load($priceListProducts, $pricelistproductsId);
        if (!$priceListProducts->getId()) {
            throw new NoSuchEntityException(
                __(
                    'PriceListProducts with id "%1" does not exist.',
                    $pricelistproductsId
                )
            );
        }
        return $priceListProducts;
    }

    /**
     * @inheritDoc
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->priceListProductsCollectionFactory->create();

        $this->collectionProcessor->process($criteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $items = [];
        foreach ($collection as $model) {
            $items[] = $model;
        }

        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritDoc
     */
    public function delete(PriceListProductsInterface $priceListProducts)
    {
        try {
            $priceListProductsModel = $this->priceListProductsFactory->create();
            $this->resource->load($priceListProductsModel, $priceListProducts->getEntityId());
            $this->resource->delete($priceListProductsModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the PriceList: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($pricelistproductsId)
    {
        return $this->delete($this->get($pricelistproductsId));
    }

}
