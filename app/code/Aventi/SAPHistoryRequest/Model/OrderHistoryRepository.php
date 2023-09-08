<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAPHistoryRequest\Model;

use Aventi\SAPHistoryRequest\Api\Data\OrderHistoryInterface;
use Aventi\SAPHistoryRequest\Api\Data\OrderHistoryInterfaceFactory;
use Aventi\SAPHistoryRequest\Api\Data\OrderHistorySearchResultsInterfaceFactory;
use Aventi\SAPHistoryRequest\Api\OrderHistoryRepositoryInterface;
use Aventi\SAPHistoryRequest\Model\ResourceModel\OrderHistory as ResourceOrderHistory;
use Aventi\SAPHistoryRequest\Model\ResourceModel\OrderHistory\CollectionFactory as OrderHistoryCollectionFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class OrderHistoryRepository implements OrderHistoryRepositoryInterface
{

    /**
     * @var ResourceOrderHistory
     */
    protected $resource;

    /**
     * @var OrderHistoryInterfaceFactory
     */
    protected $orderHistoryFactory;

    /**
     * @var OrderHistory
     */
    protected $searchResultsFactory;

    /**
     * @var CollectionProcessorInterface
     */
    protected $collectionProcessor;

    /**
     * @var OrderHistoryCollectionFactory
     */
    protected $orderHistoryCollectionFactory;


    /**
     * @param ResourceOrderHistory $resource
     * @param OrderHistoryInterfaceFactory $orderHistoryFactory
     * @param OrderHistoryCollectionFactory $orderHistoryCollectionFactory
     * @param OrderHistorySearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceOrderHistory $resource,
        OrderHistoryInterfaceFactory $orderHistoryFactory,
        OrderHistoryCollectionFactory $orderHistoryCollectionFactory,
        OrderHistorySearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->resource = $resource;
        $this->orderHistoryFactory = $orderHistoryFactory;
        $this->orderHistoryCollectionFactory = $orderHistoryCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->collectionProcessor = $collectionProcessor;
    }

    /**
     * @inheritDoc
     */
    public function save(OrderHistoryInterface $orderHistory)
    {
        try {
            $this->resource->save($orderHistory);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the orderHistory: %1',
                $exception->getMessage()
            ));
        }
        return $orderHistory;
    }

    /**
     * @inheritDoc
     */
    public function get($orderHistoryId)
    {
        $orderHistory = $this->orderHistoryFactory->create();
        $this->resource->load($orderHistory, $orderHistoryId);
        if (!$orderHistory->getId()) {
            throw new NoSuchEntityException(__('OrderHistory with id "%1" does not exist.', $orderHistoryId));
        }
        return $orderHistory;
    }

    /**
     * @inheritDoc
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->orderHistoryCollectionFactory->create();
        
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
    public function delete(OrderHistoryInterface $orderHistory)
    {
        try {
            $orderHistoryModel = $this->orderHistoryFactory->create();
            $this->resource->load($orderHistoryModel, $orderHistory->getOrderhistoryId());
            $this->resource->delete($orderHistoryModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the OrderHistory: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById($orderHistoryId)
    {
        return $this->delete($this->get($orderHistoryId));
    }
}

