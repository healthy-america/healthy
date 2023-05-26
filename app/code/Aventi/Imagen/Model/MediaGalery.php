<?php

namespace Aventi\Imagen\Model;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Gallery\EntryFactory;
use Magento\Catalog\Model\Product\Gallery\GalleryManagement;
use Magento\Catalog\Model\Product\Gallery\Processor;
use Magento\Catalog\Model\Product\Gallery\ReadHandler;
use Magento\Catalog\Model\ResourceModel\Product\Gallery;
use Magento\Framework\Api\ImageContentFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Store\Model\StoreManagerInterface;

class MediaGalery
{
    /**
     * @var EntryFactory
     */
    private $mediaGalleryEntryFactory;

    /**
     * @var GalleryManagement
     */
    private $mediaGalleryManagement;

    /**
     * @var ImageContentFactory
     */
    private $imageContentFactory;

    /**
     * @var ReadHandler
     */
    private $readHandler;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    /**
     * @var Gallery
     */
    private $gallery;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var Csv
     */
    private $file;

    /**
     * @var Processor
     */
    private $imageProcessor;
    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var MediaDirectory
     */
    private $mediaDirectory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param EntryFactory $mediaGalleryEntryFactory
     * @param GalleryManagement $mediaGalleryManagement
     * @param ImageContentFactory $imageContentFactory
     * @param ReadHandler $readHandler
     * @param DirectoryList $directoryList
     * @param Gallery $gallery
     * @param ProductRepositoryInterface $productRepository
     * @param Csv $file
     * @param Processor $imageProcessor
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        EntryFactory $mediaGalleryEntryFactory,
        GalleryManagement $mediaGalleryManagement,
        ImageContentFactory $imageContentFactory,
        ReadHandler $readHandler,
        DirectoryList $directoryList,
        Gallery $gallery,
        ProductRepositoryInterface $productRepository,
        File $file,
        Processor $imageProcessor,
        StoreManagerInterface $storeManager,
        Filesystem $filesystem
    ) {
        $this->mediaGalleryEntryFactory = $mediaGalleryEntryFactory;
        $this->mediaGalleryManagement = $mediaGalleryManagement;
        $this->imageContentFactory = $imageContentFactory;
        $this->readHandler = $readHandler;
        $this->directoryList = $directoryList;
        $this->gallery = $gallery;
        $this->productRepository = $productRepository;
        $this->file = $file;
        $this->imageProcessor = $imageProcessor;
        $this->storeManager = $storeManager;
        $this->filesystem = $filesystem;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
    }

    /**
     * @param $product
     * @param string $filePath
     * @param int $index
     * @param string $name
     * @param array $types
     * @return bool
     */
    public function processMediaGalleryEntry(
        $product,
        string $filePath,
        int $index,
        string $name,
        array $types = []
    ) : bool
    {
        $sku = $product->getSku();
        if ($index === 1) {
            try {
                $this->readHandler->execute($product);
                // Unset existing images
                $images = $product->getMediaGalleryImages();
                $product->setMediaGalleryEntries([]);
                $this->productRepository->save($product);
                foreach ($images as $image) {
                    if ($image->getData("file")) {
                        $this->gallery->deleteGallery($image->getValueId());
                        if ($this->file->isExists($image->getData("path"))) {
                            $this->file->deleteFile($image->getData("path"));
                        }
                    }
                }
            } catch (\Exception $e) {
            }
        } else {
            $this->deleteProductPicture($product, $index);
        }

        $entry = $this->mediaGalleryEntryFactory->create();

        $entry->setFile($filePath);
        $entry->setMediaType('image');
        $entry->setDisabled(false);
        $entry->setTypes($types);

        $imageContent = $this->imageContentFactory->create();
        $imageContent
            ->setType(mime_content_type($filePath))
            ->setName($name)
            ->setBase64EncodedData(base64_encode(file_get_contents($filePath)));

        $entry->setContent($imageContent);

        try {
            $this->mediaGalleryManagement->create($sku, $entry);
            return true;
        } catch (InputException|NoSuchEntityException|StateException $e) {
            return false;
        }
    }

    /**
     * Delete the product images by position:
     * example: 3_1.jpg this method will erase all images that begin
     *          with 3_1 of the product with id = 3
     *
     * @param $product
     * @param $index
     * @return void
     */
    public function deleteProductPicture($product, $index): void
    {
        try {
            $this->readHandler->execute($product);

            $imageName = $product->getId() . "_" . $index;

            $images = $product->getMediaGalleryEntries();

            if (empty($images)) {
                return;
            }

            foreach ($images as $key => $image) {
                $routeFile = $this->directoryList->getPath(DirectoryList::MEDIA) . "/catalog/product" . $image->getFile();
                if (str_contains($image->getData("file"), $imageName)) {
                    unset($images[$key]);
                    $this->gallery->deleteGallery($image->getId());
                    if ($this->file->isExists($routeFile)) {
                        $this->file->deleteFile($routeFile);
                    }
                    $this->imageProcessor->removeImage($product, $routeFile);
                }
            }

            $this->productRepository->save($product);
        } catch (\Exception $e) {
            //Image no delete
        }
    }

    /**
     * @param $file
     * @return mixed
     */
    public function getFileSize($file)
    {
        return $this->mediaDirectory->stat($file)['size'];
    }

    /**
     * @param string $sku
     * @param array $type
     */
    public function setTypes(
        string $sku,
        array $type
    ) {

        $product = $this->productRepository->get($sku);
        $existingMediaGalleryEntries = $product->getMediaGalleryEntries();
        foreach ($existingMediaGalleryEntries as $entry) {
            $product->setStoreId(0);
            $this->storeManager->setCurrentStore(0);
            $file = $entry->getFile();
            $index = explode("_", $file);
            $index = explode(".", $index[1]);
            if ($index[0] != 1 || $index[0] != '1') {
                continue;
            }
            $entry->setTypes($type);
            $this->productRepository->save($product);
        }

    }
}
