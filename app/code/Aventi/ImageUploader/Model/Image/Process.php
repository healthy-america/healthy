<?php

namespace Aventi\ImageUploader\Model\Image;

use Aventi\Imagen\Model\RenameImages;
use Aventi\ImageUploader\Helper\Data;
use Aventi\ImageUploader\Model\Image;
use Aventi\ImageUploader\Model\ResourceModel\Image\CollectionFactory as CollectionImage;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Gallery\EntryFactory;
use Magento\Catalog\Model\Product\Gallery\GalleryManagement;
use Magento\Catalog\Model\Product\Gallery\Processor;
use Magento\Catalog\Model\Product\Gallery\ReadHandler;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Gallery;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\ImageContentFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\StateException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Output\OutputInterface;

class Process
{

    /**
     * @var Data
     */
    private $data;
    /**
     * @var ImageRepository
     */
    private $imageRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var FilterBuilder
     */
    private $filterBuilder;
    /**
     * @var ReadHandler
     */
    private $readHandler;
    /**
     * @var Gallery
     */
    private $gallery;
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var OutputInterface
     */
    private $output = null;
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;
    /**
     * @var Image
     */
    private $imagen;
    /**
     * @var DirectoryList
     */
    private $directoryList;
    /**
     * @var Filesystem
     */
    private $filesystem;
    /**
     * @var CollectionFactory
     */
    private $productCollectionFactory;

    private $mediaDirectory;
    /**
     * @var File
     */
    private $_file;
    /**
     * @var CollectionImage
     */
    private $_collectionImage;

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
     * @var RenameImages
     */
    private $renameImages;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * Process constructor.
     * @param DirectoryList $directoryList
     * @param Filesystem $filesystem
     * @param Data $data
     * @param ImageRepository $imageRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param ProductRepositoryInterface $productRepository
     * @param ReadHandler $readHandler
     * @param Gallery $gallery
     * @param LoggerInterface $logger
     * @param Image $imagen
     * @param Processor $imageProcessor
     * @param CollectionFactory $productCollectionFactory
     * @param File $file
     * @param StoreManagerInterface $storeManager
     * @throws FileSystemException
     */
    public function __construct(
        EntryFactory $mediaGalleryEntryFactory,
        GalleryManagement $mediaGalleryManagement,
        ImageContentFactory $imageContentFactory,
        DirectoryList $directoryList,
        Filesystem $filesystem,
        Data $data,
        ImageRepository $imageRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        ProductRepositoryInterface $productRepository,
        ReadHandler $readHandler,
        Gallery $gallery,
        LoggerInterface $logger,
        Image $imagen,
        Processor $imageProcessor,
        CollectionFactory $productCollectionFactory,
        File $file,
        CollectionImage $_collectionImage,
        RenameImages $renameImages,
        StoreManagerInterface $storeManager
    ) {
        $this->mediaGalleryEntryFactory = $mediaGalleryEntryFactory;
        $this->mediaGalleryManagement = $mediaGalleryManagement;
        $this->imageContentFactory = $imageContentFactory;
        $this->data = $data;
        $this->imageRepository = $imageRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->readHandler = $readHandler;
        $this->gallery = $gallery;
        $this->logger = $logger;
        $this->productRepository = $productRepository;
        $this->imagen = $imagen;
        $this->directoryList = $directoryList;
        $this->filesystem = $filesystem;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $this->imageProcessor = $imageProcessor;
        $this->_file = $file;
        $this->_collectionImage = $_collectionImage;
        $this->renameImages = $renameImages;
        $this->storeManager = $storeManager;
    }

    /**
     * @return null
     */
    public function getOutput()
    {
        return $this->output;
    }

    /**
     * @param OutputInterface $output
     */
    public function setOutput(OutputInterface $output)
    {
        $this->output = $output;
    }

    /**
     * Process the image update of products
     *
     * @throws FileSystemException
     * @throws LocalizedException
     * @throws \Exception
     */
    public function update()
    {
        try {
            $this->renameImages->process();
        } catch (\Exception $e) {
            // error
        }

        $sizeMb = 2;
        $sizeMb = ($sizeMb*1024000);
        $mediaPath = $this->directoryList->getPath(DirectoryList::MEDIA);
        $pathImages = $mediaPath . '/imageUploader/images';
        if (empty($pathImages)) {
            return true;
        }
        $resume = [
            'total' => 0,
            'completed' => 0 ,
            'noFound' => 0 ,
            'NoProcessing' => 0
        ];

        /**
         * Assumed images are named [sku].[ext]
         */
        $imageCollection = $this->_collectionImage->create();
        foreach ($imageCollection as $item) {
            $imageRepo = $this->imagen->load($item->getId());
            $imageBase = str_replace("imageUploader/images/", "", $item->getPath());
            if (file_exists($this->directoryList->getPath(DirectoryList::MEDIA) . "/" . $item->getPath())) {
                $sizeImg = $this->getFileSize($this->directoryList->getPath(DirectoryList::MEDIA) . "/" . $item->getPath());
                $resume['total'] += 1;
                if ($sizeImg <= $sizeMb) {
                    $index = explode("_", $imageBase);
                    $sku = $index[0];
                    if (preg_match("/_/", $imageBase)) {
                        $index = explode(".", $index[1]);
                        $index = $index[0];
                        if ($index[0] != 0) {
                            try {
                                $product = $this->productRepository->get($sku);
                                $routeImage = $this->directoryList->getPath(DirectoryList::MEDIA) . "/" . $item->getPath();
                                $type = ($index == 1 || $index == '1') ? ['image', 'small_image', 'thumbnail'] : [];
                                $save = $this->processMediaGalleryEntry(
                                    $product,
                                    $routeImage,
                                    $index,
                                    $type
                                );
                                if ($index == 1 || $index == '1') {
                                    $this->setTypes($sku, $type);
                                }
                                if ($save) {
                                    $item->delete();
                                    $resume['completed'] += 1;
                                } else {
                                    $resume['NoProcessing'] += 1;
                                }
                            } catch (\Exception $e) {
                                $this->writeIn("Error: " . $e->getMessage());
                                $imageRepo->setDetails("Error: " . $e->getMessage());
                                $imageRepo->save();
                                $resume['NoProcessing'] += 1;
                            }
                        } else {
                            $resume['NoProcessing'] += 1;
                            $this->logger->error("La imagen: " . $imageBase . ", No tiene el formato requerido despues del Guion bajo '_' 'SKU_1.jpg'");
                            $imageRepo->setDetails("La imagen: " . $imageBase . ", No tiene el formato requerido despues del Guion bajo '_' 'SKU_1.jpg'");
                            $imageRepo->save();
                        }
                    } else {
                        $resume['NoProcessing'] += 1;
                        $this->logger->error("La imagen: " . $sku . "## , No tiene el formato requerido en el modulo 'SKU##1.jpg'");
                        $imageRepo->setDetails("La imagen: " . $sku . "## , No tiene el formato requerido despues de los 2 numerales '##' 'SKU##1.jpg'");
                        $imageRepo->save();
                    }
                } else {
                    $resume['NoProcessing'] += 1;
                    $this->logger->error("La imagen: " . $imageBase . ", Supera el tamaño permitido, Permitido: " . ($sizeMb / 1024000) . " Mb, Tamaño que viene: " . round(($sizeImg / 1024000), 2) . " Mb ");
                    $imageRepo->setDetails("La imagen: " . $imageBase . ", Supera el tamaño permitido, Permitido: " . ($sizeMb / 1024000) . " Mb, Tamaño que viene: " . round(($sizeImg / 1024000), 2) . " Mb ");
                    $imageRepo->save();
                }
            } else {
                $resume['NoProcessing'] += 1;
                $this->logger->error("La imagen: " . $imageBase . ", No existe en el servidor");
                $imageRepo->setDetails("La imagen: " . $imageBase . ", No existe en el servidor");
                $imageRepo->save();
            }
        }
        $this->resumen(array_values($resume));
        return $resume;
    }

    /**
     * @throws NoSuchEntityException
     */
    public function deleteProductPictures($sku, $separator)
    {
        $separator = (explode('.', $separator))[0];
        $product = $this->productRepository->get($sku);
        $this->readHandler->execute($product);
        $imageName = $sku . $separator;
        // Unset existing images
        $images = $product->getMediaGalleryImages();
        $product->setMediaGalleryEntries([]);
        $this->productRepository->save($product);
        foreach ($images as $image) {
            if (str_contains($image->getData("file"), $imageName)) {
                $this->gallery->deleteGallery($image->getValueId());
                if ($this->_file->isExists($image->getData("path"))) {
                    $this->_file->deleteFile($image->getData("path"));
                }
            }
        }
    }

    /**
     * @throws NoSuchEntityException
     */
    public function deleteAllProductPictures($product)
    {
        try {
            $this->readHandler->execute($product);
            // Unset existing images
            $images = $product->getMediaGalleryImages();
            $product->setMediaGalleryEntries([]);
            $this->productRepository->save($product);
            foreach ($images as $image) {
                if ($image->getData("file")) {
                    $this->gallery->deleteGallery($image->getValueId());
                    if ($this->_file->isExists($image->getData("path"))) {
                        $this->_file->deleteFile($image->getData("path"));
                    }
                }
            }
        } catch (\Exception $e) {
        }
    }

    /**
     * Delete the product images by position:
     * example: 3_1.jpg this method will erase all images that begin
     *          with 3_1 of the product with id = 3
     *
     * @param $product
     * @param $separator
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
                    if ($this->_file->isExists($routeFile)) {
                        $this->_file->deleteFile($routeFile);
                    }
                    $this->imageProcessor->removeImage($product, $routeFile);
                }
            }

            $this->productRepository->save($product);
        } catch (\Exception $e) {
            //Image no delete
        }
    }

    public function getFileSize($file)
    {
        $fileSize = $this->mediaDirectory->stat($file)['size'];
        //$readableSize = $this->convertToReadableSize($fileSize);
        return $fileSize;
    }

    public function convertToReadableSize($size)
    {
        $base = log($size) / log(1024);
        $suffix = ["", " KB", " MB", " GB", " TB"];
        $f_base = floor($base);
        return round(pow(1024, $base - floor($base)), 1) . $suffix[$f_base];
    }

    /**
     * Uploads images Configurable Products.
     * @return bool|int[]
     * @throws LocalizedException
     */
    public function updateImgConfigurable()
    {
        $sizeMb = 2;
        $sizeMb = ($sizeMb*1024000);
        $pathImages = $this->data->getPathImage();
        if (empty($pathImages)) {
            return true;
        }

        $this->writeIn(__('Path origin the data ') . $pathImages);
        $resume = [
            'total' => 0,
            'completed' => 0 ,
            'noFound' => 0 ,
            'NoProcessing' => 0
        ];

        $files_array = [];

        $dirs = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($pathImages));
        foreach ($dirs as $dir) {
            if ($dir->isDir()) {
                continue;
            }

            if (in_array(trim(pathinfo($dir)['extension']), ['jpg', 'png', 'gif'])) {
                $sizeImg = $this->getFileSize(pathinfo($dir)['dirname'] . "/" . pathinfo($dir)['basename']);
                $resume['total'] += 1;
                if ($sizeImg<= $sizeMb) {
                    $imageFileName = explode('_', trim(pathinfo($dir)['filename']));
                    $imageExtension = trim(pathinfo($dir)['extension']);
                    $imageBase = substr($imageFileName[0], 0, 8) . "." . $imageExtension;
                    if ($imageFileName[1] == "1" && !array_key_exists($imageBase, $files_array)) {
                        $files_array[$imageBase] = $dir->getPathName();
                    }
                } else {
                    $resume['NoProcessing'] += 1;
                }
            }
        }
        ksort($files_array);

        foreach ($files_array as $key => $value) {
            if (!$this->imageRegister($key)) {
                $this->writeIn("PASO REGISTER y va con key: $key y value: $value");
                $response = $this->updateConfigurable($key, $value);

                $this->writeIn("PASO response: " . $response);
                if ($response) {
                    $resume['completed'] += 1;
                }
                $resume['noFound'] += 1;
            } else {
                $resume['NoProcessing'] += 1;
            }
        }
        $this->resumen(array_values($resume));
        return $resume;
    }

    /**
     * print data
     *
     * @param $message
     */
    public function writeIn($message)
    {
        $output = $this->getOutput();
        if ($output) {
            $output->writeln($message);
        }
    }

    /**
     * Find the imagen
     *
     * @author Carlos Hernan Aguilar Hurado <caguilar@aventi.co>
     * @date 28/04/20
     * @param $name
     * @return bool
     * @throws LocalizedException
     */
    private function imageRegister($name)
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter(
            'image',
            $name,
            'eq'
        )->create();
        $items = $this->imageRepository->getList($searchCriteria);
        return ($items->getTotalCount() > 0) ? true : false;
    }

    /**
     * Print the resume
     *
     * @param array $data
     */
    private function resumen($data = [])
    {
        $output = $this->getOutput();
        if ($output) {
            $table = new table($output);
            $table
                ->setHeaders(['Total', 'Complete', 'Product no found', 'No processing'])
                ->setRows([$data]);
            $table->render();
        }
    }

    /**
     * @param string $imageFileName
     * @param $dir
     * @return bool
     */
    private function updateConfigurable(string $imageFileName, $dir): bool
    {
        $baseImageFileName = explode('_', $imageFileName);
        $skuConfigurable = (string) substr($baseImageFileName[0], 0, 8);
        try {
            $configurable = $this->productRepository->get($skuConfigurable, true, null, false);
            $baseImage  = $configurable->getData('image');
            if ($baseImage) {
                return false;
            } else {
                $configurable->addImageToMediaGallery($dir, [
                    'image',
                    'small_image',
                    'thumbnail'
                ], false, false);
                $this->productRepository->save($configurable);
                $this->imagen->setData(['image' => $imageFileName]);
                $this->imagen->save();
                $this->imagen->setId(null);
                return true;
            }
        } catch (NoSuchEntityException | \Exception $e) {
            $this->writeIn("EERRROORR: " . $e->getMessage());
            $this->logger->debug($e->getMessage());
            return false;
        }
    }

    /**
     * @param $product
     * @param string $filePath
     * @param int $index
     * @param array $types
     * @throws NoSuchEntityException
     */
    public function processMediaGalleryEntry(
        $product,
        string $filePath,
        int $index,
        array $types = []
    ) : bool
    {
        if ($index === 1) {
            $this->deleteAllProductPictures($product);
        } else {
            $this->deleteProductPicture($product, $index);
        }

        $sku = $product->getSku();
        $entry = $this->mediaGalleryEntryFactory->create();

        $entry->setFile($filePath);
        $entry->setMediaType('image');
        $entry->setDisabled(false);
        $entry->setTypes($types);
        $imageContent = $this->imageContentFactory->create();
        $imageContent
            ->setType(mime_content_type($filePath))
            ->setName($product->getId() . '_' . $index)
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
