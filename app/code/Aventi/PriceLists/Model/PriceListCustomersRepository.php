<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\PriceLists\Model;

use Aventi\PriceLists\Api\Data\PriceListCustomersInterface;
use Aventi\PriceLists\Api\Data\PriceListCustomersInterfaceFactory;
use Aventi\PriceLists\Api\Data\PriceListCustomersSearchResultsInterfaceFactory;
use Aventi\PriceLists\Api\PriceListCustomersRepositoryInterface;
use Aventi\PriceLists\Model\ResourceModel\PriceListCustomers as ResourcePriceListCustomers;
use Aventi\PriceLists\Model\ResourceModel\PriceListCustomers\CollectionFactory as PriceListCustomersCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class PriceListCustomersRepository
 * @package Aventi\PriceLists\Model
 */
class PriceListCustomersRepository implements PriceListCustomersRepositoryInterface
{

    /**
     * @var ResourcePriceListCustomers
     */
    protected $resource;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var PriceListCustomersInterfaceFactory
     */
    protected $priceListCustomersFactory;

    /**
     * @var PriceListCustomersCollectionFactory
     */
    protected $priceListCustomersCollectionFactory;

    /**
     * @var PriceListCustomers
     */
    protected $searchResultsFactory;

    /**
     * @param ResourcePriceListCustomers $resource
     * @param PriceListCustomersInterfaceFactory $priceListCustomersFactory
     * @param PriceListCustomersCollectionFactory $priceListCustomersCollectionFactory
     * @param PriceListCustomersSearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourcePriceListCustomers $resource,
        PriceListCustomersInterfaceFactory $priceListCustomersFactory,
        PriceListCustomersCollectionFactory $priceListCustomersCollectionFactory,
        PriceListCustomersSearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->priceListCustomersFactory = $priceListCustomersFactory;
        $this->priceListCustomersCollectionFactory = $priceListCustomersCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritDoc
     */
    public function save(PriceListCustomersInterface $priceListCustomers)
    {
        try {
            $this->resource->save($priceListCustomers);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the priceListProducts: %1',
                $exception->getMessage()
            ));
        }
        return $priceListCustomers;
    }

    /**
     * @inheritDoc
     */
    public function get($pricelistcustomersId)
    {
        $priceListCustomers = $this->priceListCustomersFactory->create();
        $this->resource->load($priceListCustomers, $pricelistcustomersId);
        if (!$priceListCustomers->getId()) {
            throw new NoSuchEntityException(
                __(
                    'PriceListCustomers with id "%1" does not exist.',
                    $pricelistcustomersId
                )
            );
        }
        return $priceListCustomers;
    }

    /**
     * @inheritDoc
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->priceListCustomersCollectionFactory->create();

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
    public function delete(PriceListCustomersInterface $priceListCustomers)
    {
        try {
            $priceListCustomersModel = $this->priceListCustomersFactory->create();
            $this->resource->load($priceListCustomersModel, $priceListCustomers->getEntityId());
            $this->resource->delete($priceListCustomersModel);
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
    public function deleteById($priceListCustomersId)
    {
        return $this->delete($this->get($priceListCustomersId));
    }
}
