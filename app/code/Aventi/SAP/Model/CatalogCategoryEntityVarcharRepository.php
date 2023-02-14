<?php
/**
 * Copyright © Aventi All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Model;

use Aventi\SAP\Api\CatalogCategoryEntityVarcharRepositoryInterface;
use Aventi\SAP\Api\Data\CatalogCategoryEntityVarcharInterfaceFactory;
use Aventi\SAP\Api\Data\CatalogCategoryEntityVarcharSearchResultsInterfaceFactory;
use Aventi\SAP\Model\ResourceModel\CatalogCategoryEntityVarchar as ResourceCatalogCategoryEntityVarchar;
use Aventi\SAP\Model\ResourceModel\CatalogCategoryEntityVarchar\CollectionFactory as
    CatalogCategoryEntityVarcharCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;

class CatalogCategoryEntityVarcharRepository implements CatalogCategoryEntityVarcharRepositoryInterface
{
    /**
     * @var CatalogCategoryEntityVarcharInterfaceFactory
     */
    protected $dataCatalogCategoryEntityVarcharFactory;

    /**
     * @var ResourceCatalogCategoryEntityVarchar
     */
    protected $resource;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var CatalogCategoryEntityVarcharCollectionFactory
     */
    protected $catalogCategoryEntityVarcharCollectionFactory;

    /**
     * @var DataObjectProcessor
     */
    protected $dataObjectProcessor;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var ExtensibleDataObjectConverter
     */
    protected $extensibleDataObjectConverter;

    /**
     * @var CatalogCategoryEntityVarcharSearchResultsInterfaceFactory
     */
    protected $searchResultsFactory;

    /**
     * @var JoinProcessorInterface
     */
    protected $extensionAttributesJoinProcessor;

    /**
     * @var CollectionProcessorInterface
     */
    private $collectionProcessor;

    /**
     * @var CatalogCategoryEntityVarcharFactory
     */
    protected $catalogCategoryEntityVarcharFactory;

    /**
     * @param ResourceCatalogCategoryEntityVarchar $resource
     * @param CatalogCategoryEntityVarcharFactory $catalogCategoryEntityVarcharFactory
     * @param CatalogCategoryEntityVarcharInterfaceFactory $dataCatalogCategoryEntityVarcharFactory
     * @param CatalogCategoryEntityVarcharCollectionFactory $catalogCategoryEntityVarcharCollectionFactory
     * @param CatalogCategoryEntityVarcharSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceCatalogCategoryEntityVarchar $resource,
        CatalogCategoryEntityVarcharFactory $catalogCategoryEntityVarcharFactory,
        CatalogCategoryEntityVarcharInterfaceFactory $dataCatalogCategoryEntityVarcharFactory,
        CatalogCategoryEntityVarcharCollectionFactory $catalogCategoryEntityVarcharCollectionFactory,
        CatalogCategoryEntityVarcharSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->catalogCategoryEntityVarcharFactory = $catalogCategoryEntityVarcharFactory;
        $this->catalogCategoryEntityVarcharCollectionFactory = $catalogCategoryEntityVarcharCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataCatalogCategoryEntityVarcharFactory = $dataCatalogCategoryEntityVarcharFactory;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function save(\Aventi\SAP\Api\Data\CatalogCategoryEntityVarcharInterface $catalogCategoryEntityVarchar)
    {
        /* if (empty($catalogCategoryEntityVarchar->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $catalogCategoryEntityVarchar->setStoreId($storeId);
        } */

        $catalogCategoryEntityVarcharData = $this->extensibleDataObjectConverter->toNestedArray(
            $catalogCategoryEntityVarchar,
            [],
            \Aventi\SAP\Api\Data\CatalogCategoryEntityVarcharInterface::class
        );

        $catalogCategoryEntityVarcharModel = $this->catalogCategoryEntityVarcharFactory->create()
            ->setData($catalogCategoryEntityVarcharData);

        try {
            $this->resource->save($catalogCategoryEntityVarcharModel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the catalogCategoryEntityVarchar: %1',
                $exception->getMessage()
            ));
        }
        return $catalogCategoryEntityVarcharModel->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function get($catalogCategoryEntityVarcharId)
    {
        $catalogCategoryEntityVarchar = $this->catalogCategoryEntityVarcharFactory->create();
        $this->resource->load($catalogCategoryEntityVarchar, $catalogCategoryEntityVarcharId);
        if (!$catalogCategoryEntityVarchar->getId()) {
            throw new NoSuchEntityException(
                __('catalog_category_entity_varchar with id "%1" does not exist.', $catalogCategoryEntityVarcharId)
            );
        }
        return $catalogCategoryEntityVarchar->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $criteria)
    {
        $collection = $this->catalogCategoryEntityVarcharCollectionFactory->create();

        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \Aventi\SAP\Api\Data\CatalogCategoryEntityVarcharInterface::class
        );

        $this->collectionProcessor->process($criteria, $collection);

        $searchResults = $this->searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $items = [];
        foreach ($collection as $model) {
            $items[] = $model->getDataModel();
        }

        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * {@inheritdoc}
     */
    public function delete(\Aventi\SAP\Api\Data\CatalogCategoryEntityVarcharInterface $catalogCategoryEntityVarchar)
    {
        try {
            $catalogCategoryEntityVarcharModel = $this->catalogCategoryEntityVarcharFactory->create();
            $this->resource->load(
                $catalogCategoryEntityVarcharModel,
                $catalogCategoryEntityVarchar->getValueId()
            );
            $this->resource->delete($catalogCategoryEntityVarcharModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the catalog_category_entity_varchar: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($catalogCategoryEntityVarcharId)
    {
        return $this->delete($this->get($catalogCategoryEntityVarcharId));
    }
}
