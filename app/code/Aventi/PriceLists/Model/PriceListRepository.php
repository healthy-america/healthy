<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\PriceLists\Model;

use Aventi\PriceLists\Api\Data\PriceListInterface;
use Aventi\PriceLists\Api\Data\PriceListInterfaceFactory;
use Aventi\PriceLists\Api\Data\PriceListSearchResultsInterfaceFactory;
use Aventi\PriceLists\Api\PriceListRepositoryInterface;
use Aventi\PriceLists\Model\ResourceModel\PriceList as ResourcePriceList;
use Aventi\PriceLists\Model\ResourceModel\PriceList\CollectionFactory as PriceListCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class PriceListRepository
 * @package Aventi\PriceLists\Model
 */
class PriceListRepository implements PriceListRepositoryInterface
{

    /**
     * @var ResourcePriceList
     */
    protected $resource;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var PriceListInterfaceFactory
     */
    protected $priceListFactory;

    /**
     * @var PriceListCollectionFactory
     */
    protected $priceListCollectionFactory;

    /**
     * @var PriceList
     */
    protected $searchResultsFactory;

    /**
     * @param ResourcePriceList $resource
     * @param PriceListInterfaceFactory $priceListFactory
     * @param PriceListCollectionFactory $priceListCollectionFactory
     * @param PriceListSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourcePriceList $resource,
        PriceListInterfaceFactory $priceListFactory,
        PriceListCollectionFactory $priceListCollectionFactory,
        PriceListSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->priceListFactory = $priceListFactory;
        $this->priceListCollectionFactory = $priceListCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritDoc
     */
    public function save(PriceListInterface $priceList)
    {
        try {
            $this->resource->save($priceList);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the priceList: %1',
                $exception->getMessage()
            ));
        }
        return $priceList;
    }

    /**
     * @inheritDoc
     */
    public function get($priceListId)
    {
        $priceList = $this->priceListFactory->create();
        $this->resource->load($priceList, $priceListId);
        if (!$priceList->getId()) {
            throw new NoSuchEntityException(__('PriceList with id "%1" does not exist.', $priceListId));
        }
        return $priceList;
    }

    /**
     * @inheritDoc
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->priceListCollectionFactory->create();

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
    public function delete(PriceListInterface $priceList)
    {
        try {
            $priceListModel = $this->priceListFactory->create();
            $this->resource->load($priceListModel, $priceList->getEntityId());
            $this->resource->delete($priceListModel);
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
    public function deleteById($priceListId)
    {
        return $this->delete($this->get($priceListId));
    }

    /**
     * {@inheritdoc}
     */
    public function getByName($priceListName)
    {
        $priceList = $this->priceListFactory->create();
        $this->resource->load($priceList, $priceListName, "name");
        if (!$priceList->getId()) {
            throw new NoSuchEntityException(__('PriceList with name "%1" does not exist.', $priceListName));
        }
        return $priceList;
    }
}
