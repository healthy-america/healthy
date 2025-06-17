<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\PriceLists\Model;

use Aventi\PriceLists\Api\Data\PriceListGroupsInterface;
use Aventi\PriceLists\Api\Data\PriceListGroupsInterfaceFactory;
use Aventi\PriceLists\Api\Data\PriceListGroupsSearchResultsInterfaceFactory;
use Aventi\PriceLists\Api\PriceListGroupsRepositoryInterface;
use Aventi\PriceLists\Model\ResourceModel\PriceListGroups as ResourcePriceListGroups;
use Aventi\PriceLists\Model\ResourceModel\PriceListGroups\CollectionFactory as PriceListGroupsCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class PriceListGroupsRepository
 * @package Aventi\PriceLists\Model
 */
class PriceListGroupsRepository implements PriceListGroupsRepositoryInterface
{

    /**
     * @var ResourcePriceListGroups
     */
    protected $resource;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var PriceListGroupsInterfaceFactory
     */
    protected $priceListGroupsFactory;

    /**
     * @var PriceListGroupsCollectionFactory
     */
    protected $priceListGroupsCollectionFactory;

    /**
     * @var PriceListGroups
     */
    protected $searchResultsFactory;

    /**
     * @param ResourcePriceListGroups $resource
     * @param PriceListGroupsInterfaceFactory $priceListGroupsFactory
     * @param PriceListGroupsCollectionFactory $priceListGroupsCollectionFactory
     * @param PriceListGroupsSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourcePriceListGroups $resource,
        PriceListGroupsInterfaceFactory $priceListGroupsFactory,
        PriceListGroupsCollectionFactory $priceListGroupsCollectionFactory,
        PriceListGroupsSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->priceListGroupsFactory = $priceListGroupsFactory;
        $this->priceListGroupsCollectionFactory = $priceListGroupsCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritDoc
     */
    public function save(PriceListGroupsInterface $priceListGroups)
    {
        try {
            $this->resource->save($priceListGroups);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the priceListProducts: %1',
                $exception->getMessage()
            ));
        }
        return $priceListGroups;
    }

    /**
     * @inheritDoc
     */
    public function get($pricelistgroupsId)
    {
        $priceListGroups = $this->priceListGroupsFactory->create();
        $this->resource->load($priceListGroups, $pricelistgroupsId);
        if (!$priceListGroups->getId()) {
            throw new NoSuchEntityException(
                __(
                    'PriceListGroups with id "%1" does not exist.',
                    $pricelistgroupsId
                )
            );
        }
        return $priceListGroups;
    }

    /**
     * @inheritDoc
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->priceListGroupsCollectionFactory->create();

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
    public function delete(PriceListGroupsInterface $priceListGroups)
    {
        try {
            $priceListGroupsModel = $this->priceListGroupsFactory->create();
            $this->resource->load($priceListGroupsModel, $priceListGroups->getEntityId());
            $this->resource->delete($priceListGroupsModel);
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
    public function deleteById($pricelistgroupsId)
    {
        return $this->delete($this->get($pricelistgroupsId));
    }
}
