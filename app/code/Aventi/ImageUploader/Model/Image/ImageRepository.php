<?php

namespace Aventi\ImageUploader\Model\Image;

use Aventi\ImageUploader\Api\Data\ImageSearchResultsInterfaceFactory;
use Aventi\ImageUploader\Api\ImageRepositoryInterface;
use Aventi\ImageUploader\Model\ResourceModel\Image as ResourceImagen;
use Aventi\ImageUploader\Model\ResourceModel\Image\CollectionFactory as ImageCollectionFactory;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Api\ExtensibleDataObjectConverter;
use Magento\Framework\Api\ExtensionAttribute\JoinProcessorInterface;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Reflection\DataObjectProcessor;
use Magento\Store\Model\StoreManagerInterface;

class ImageRepository implements ImageRepositoryInterface
{
    protected $resource;

    protected $imagenFactory;

    protected $imagenCollectionFactory;

    protected $searchResultsFactory;

    protected $dataObjectHelper;

    protected $dataObjectProcessor;

    protected $extensionAttributesJoinProcessor;

    private $storeManager;

    private $collectionProcessor;

    protected $extensibleDataObjectConverter;

    /**
     * @param ResourceImage $resource
     * @param ImageFactory $imagenFactory
     * @param ImageCollectionFactory $imagenCollectionFactory
     * @param ImageSearchResultsInterfaceFactory $searchResultsFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param DataObjectProcessor $dataObjectProcessor
     * @param StoreManagerInterface $storeManager
     * @param CollectionProcessorInterface $collectionProcessor
     * @param JoinProcessorInterface $extensionAttributesJoinProcessor
     * @param ExtensibleDataObjectConverter $extensibleDataObjectConverter
     */
    public function __construct(
        ResourceImagen $resource,
        ImageFactory $imagenFactory,
        ImageCollectionFactory $imagenCollectionFactory,
        ImageSearchResultsInterfaceFactory $searchResultsFactory,
        DataObjectHelper $dataObjectHelper,
        DataObjectProcessor $dataObjectProcessor,
        StoreManagerInterface $storeManager,
        CollectionProcessorInterface $collectionProcessor,
        JoinProcessorInterface $extensionAttributesJoinProcessor,
        ExtensibleDataObjectConverter $extensibleDataObjectConverter
    ) {
        $this->resource = $resource;
        $this->imagenFactory = $imagenFactory;
        $this->imagenCollectionFactory = $imagenCollectionFactory;
        $this->searchResultsFactory = $searchResultsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->dataObjectProcessor = $dataObjectProcessor;
        $this->storeManager = $storeManager;
        $this->collectionProcessor = $collectionProcessor;
        $this->extensionAttributesJoinProcessor = $extensionAttributesJoinProcessor;
        $this->extensibleDataObjectConverter = $extensibleDataObjectConverter;
    }

    /**
     * {@inheritdoc}
     */
    public function save(
        \Aventi\ImageUploader\Api\Data\ImageInterface $imagen
    ) {
        /* if (empty($imagen->getStoreId())) {
            $storeId = $this->storeManager->getStore()->getId();
            $imagen->setStoreId($storeId);
        } */

        $imagenData = $this->extensibleDataObjectConverter->toNestedArray(
            $imagen,
            [],
            \Aventi\ImageUploader\Api\Data\ImageInterface::class
        );

        $imagenModel = $this->imagenFactory->create()->setData($imagenData);

        try {
            $this->resource->save($imagenModel);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the imagen: %1',
                $exception->getMessage()
            ));
        }
        return $imagenModel->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function get($imagenId)
    {
        $imagen = $this->imagenFactory->create();
        $this->resource->load($imagen, $imagenId);
        if (!$imagen->getId()) {
            throw new NoSuchEntityException(__('Imagen with id "%1" does not exist.', $imagenId));
        }
        return $imagen->getDataModel();
    }

    /**
     * {@inheritdoc}
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    ) {
        $collection = $this->imagenCollectionFactory->create();

        $this->extensionAttributesJoinProcessor->process(
            $collection,
            \Aventi\ImageUploader\Api\Data\ImageInterface::class
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
    public function delete(
        \Aventi\ImageUploader\Api\Data\ImageInterface $imagen
    ) {
        try {
            $imagenModel = $this->imagenFactory->create();
            $this->resource->load($imagenModel, $imagen->getImagenId());
            $this->resource->delete($imagenModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the Imagen: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteById($imagenId)
    {
        return $this->delete($this->get($imagenId));
    }
}
