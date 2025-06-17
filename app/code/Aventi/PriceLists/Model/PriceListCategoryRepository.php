<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\PriceLists\Model;

use Aventi\PriceLists\Api\Data\PriceListCategoryInterface;
use Aventi\PriceLists\Api\Data\PriceListCategoryInterfaceFactory;
use Aventi\PriceLists\Api\Data\PriceListCategorySearchResultsInterfaceFactory;
use Aventi\PriceLists\Api\PriceListCategoryRepositoryInterface;
use Aventi\PriceLists\Model\ResourceModel\PriceListCategory as ResourcePriceListCategory;
use Aventi\PriceLists\Model\ResourceModel\PriceListCategory\CollectionFactory as PriceListCategoryCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class PriceListCategoryRepository
 * @package Aventi\PriceLists\Model
 */
class PriceListCategoryRepository implements PriceListCategoryRepositoryInterface
{
    /**
     * @var ResourcePriceListCategory
     */
    protected $resource;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var PriceListCategoryInterfaceFactory
     */
    protected $priceListCategoryFactory;

    /**
     * @var PriceListCategoryCollectionFactory
     */
    protected $priceListCategoryCollectionFactory;

    /**
     * @var PriceListCategory
     */
    protected $searchResultsFactory;

    /**
     * @param ResourcePriceListCategory $resource
     * @param PriceListCategoryInterfaceFactory $priceListCategoryFactory
     * @param PriceListCategoryCollectionFactory $priceListCategoryCollectionFactory
     * @param PriceListCategorySearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourcePriceListCategory $resource,
        PriceListCategoryInterfaceFactory $priceListCategoryFactory,
        PriceListCategoryCollectionFactory $priceListCategoryCollectionFactory,
        PriceListCategorySearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->priceListCategoryFactory = $priceListCategoryFactory;
        $this->priceListCategoryCollectionFactory = $priceListCategoryCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritDoc
     */
    public function save(PriceListCategoryInterface $priceListCategory)
    {
        try {
            $this->resource->save($priceListCategory);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the priceListProducts: %1',
                $exception->getMessage()
            ));
        }
        return $priceListCategory;
    }

    /**
     * @inheritDoc
     */
    public function get($priceListCategoryId)
    {
        $priceListCategory = $this->priceListCategoryFactory->create();
        $this->resource->load($priceListCategory, $priceListCategoryId);
        if (!$priceListCategory->getId()) {
            throw new NoSuchEntityException(__('PriceListCategory with id "%1" does not exist.', $priceListCategoryId));
        }
        return $priceListCategory;
    }

    /**
     * @inheritDoc
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->priceListCategoryCollectionFactory->create();

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
    public function delete(PriceListCategoryInterface $priceListCategory)
    {
        try {
            $priceListCategoryModel = $this->priceListCategoryFactory->create();
            $this->resource->load($priceListCategoryModel, $priceListCategory->getEntityId());
            $this->resource->delete($priceListCategoryModel);
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
    public function deleteById($priceListCategoryId)
    {
        return $this->delete($this->get($priceListCategoryId));
    }
}
