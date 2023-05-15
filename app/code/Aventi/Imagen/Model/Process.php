<?php

namespace Aventi\Imagen\Model;

use Aventi\Imagen\Helper\Data;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Gallery\Processor;
use Magento\Catalog\Model\Product\Gallery\ReadHandler;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Catalog\Model\ResourceModel\Product\Gallery;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;
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
     * @var ImagenRepository
     */
    private $imagenRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
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
     * @var Imagen
     */
    private $imagen;
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
     * Process constructor.
     * @param DirectoryList $directoryList
     * @param Filesystem $filesystem
     * @param Data $data
     * @param ImagenRepository $imagenRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param FilterBuilder $filterBuilder
     * @param ProductRepositoryInterface $productRepository
     * @param ReadHandler $readHandler
     * @param Gallery $gallery
     * @param LoggerInterface $logger
     * @param Imagen $imagen
     * @param Processor $imageProcessor
     * @param File $file
     * @throws FileSystemException
     */
    public function __construct(
        DirectoryList $directoryList,
        Filesystem $filesystem,
        Data $data,
        ImagenRepository $imagenRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        FilterBuilder $filterBuilder,
        ProductRepositoryInterface $productRepository,
        ReadHandler $readHandler,
        Gallery $gallery,
        LoggerInterface $logger,
        Imagen $imagen,
        Processor $imageProcessor,
        File $file
    ) {
        $this->data = $data;
        $this->imagenRepository = $imagenRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->filterBuilder = $filterBuilder;
        $this->readHandler = $readHandler;
        $this->gallery = $gallery;
        $this->logger = $logger;
        $this->productRepository = $productRepository;
        $this->imagen = $imagen;
        $this->directoryList = $directoryList;
        $this->filesystem = $filesystem;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(DirectoryList::ROOT);
        $this->imageProcessor = $imageProcessor;
        $this->_file = $file;
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
     * Process the imagen products
     *
     * @throws FileSystemException
     * @throws LocalizedException
     * @throws \Exception
     * @author Carlos Hernan Aguilar Hurado <caguilar@aventi.co>
     * @date 28/04/20
     */
    public function update()
    {
        $resume = [
            'total' => 0,
            'completed' => 0 ,
            'noFound' => 0 ,
            'NoProcessing' => 0
        ];
        $sizeMb = 12;
        $sizeMb = ($sizeMb*1024000);
        $pathImages = $this->data->getPathImage();

        $this->writeIn(__('Path origin the data ') . $pathImages);

        if (empty($pathImages) || !$this->existsFolder($pathImages)) {
            $this->writeIn(__('Path does not exist and there are insufficient permissions to create it.'));
            return true;
        }

        /**
         * Assumed images are named [sku].[ext]
         */
        $dirs = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($pathImages));
        foreach ($dirs as $dir) {
            if ($dir->isDir()) {
                continue;
            }
            if (!in_array(trim(pathinfo($dir)['extension']), ['jpg', 'png', 'gif'])) {
                $resume['NoProcessing'] += 1;
                $this->logger->error("Error: extension");
            }
            $sizeImg = $this->getFileSize(pathinfo($dir)['dirname'] . "/" . pathinfo($dir)['basename']);
            $resume['total'] += 1;
            if ($sizeImg > $sizeMb) {
                $resume['NoProcessing'] += 1;
                $this->logger->error("La imagen: " . $dir . ", Supera el tamaño permitido, Permitido: " . ($sizeMb/1024000) . " Mb, Tamaño que viene: " . round(($sizeImg/1024000), 2) . " Mb ");
                continue;
            }

            $imageFileName = trim(pathinfo($dir)['filename']);
            $imageFileName = explode('_', $imageFileName);
            $imageBase = trim(pathinfo($dir)['basename']);
            if (!preg_match("/[\w]_[0-9][.]/", $imageBase)) {
                $resume['NoProcessing'] += 1;
                $this->logger->error("La imagen: " . $dir . ", No tiene el formato requerido en el modulo 'SKU_1.jpg'");
                continue;
            }
            $index = explode("_", $imageBase);
            $index = $this->deleteEmptyPositions($index);
            $sku = $index[0];
            $index = explode(".", $index[1]);
            $index = $index[0];
            if ($index < 1) {
                $resume['NoProcessing'] += 1;
                $this->logger->error("La imagen: " . $dir . ", No tiene el formato requerido despues del Guion bajo '_' 'SKU_1.jpg'");
                continue;
            }
            $customSku = $sku;
            $collection = $this->productCollectionFactory->create();
            $collection = $collection->addAttributeToSelect(['id', 'sku'])
                ->addAttributeToSort('created_at', 'desc')
                ->addAttributeToFilter('sku', ['like' => $customSku]);
            $exists = count($collection->getData());
            if ($exists) {
                $this->writeIn("Add images in : " . $sku);
                foreach ($collection as $item) {
                    $sku = $item->getSku();
                    $index = $item->getSku();
                    $dir = $dir->getPathName();
                    $product = $this->productRepository->get($item->getSku());
                    if ($index === 1 || $index === '1') {
                        try {
                            if (file_exists($image)) {
                                /** Add image */
                                if (isset($imageFileName[1])) {
                                    if ($imageFileName[1] == "1") {
                                        $product->addImageToMediaGallery($image, [
                                            'image',
                                            'small_image',
                                            'thumbnail'
                                        ], false, false);
                                    } else {
                                        $product->addImageToMediaGallery($image, null, false, false);
                                    }
                                    $this->productRepository->save($product);
                                }

                                $this->imagen->setData(['image' => $imageBase]);
                                $this->imagen->save();
                                $this->imagen->setId(null);
                                $resume['completed'] += 1;
                                //\Magento\Framework\Backup\unlink($directoryTemporal.$imageBase);
                            }
                        } catch (NoSuchEntityException | \Exception $e) {
                            $this->writeIn("Error: " . $e->getMessage());
                            $resume['NoProcessing'] += 1;
                        }
                    } else {
                        $product->addImageToMediaGallery($dir, null, false, false);
                        if ($this->_file->isExists($dir)) {
                            $this->_file->deleteFile($dir);
                        }
                    }
                    $this->productRepository->save($product);
                }
            } else {
                $resume['noFound'] += 1;
                $this->logger->error("El SKU: " . $customSku . ", No existe");
            }
        }

        $this->resumen(array_values($resume));
        return $resume;
    }

    /**
     * @param array $array
     * @return array
     */
    private function deleteEmptyPositions(array $array): array
    {
        $new = [];
        for ($i = 0; $i < count($array); $i++) {
            if ($array[$i] && trim($array[$i]) != "" && strlen($array[$i]) > 0) {
                $new[] = $array[$i];
            }
        }
        return $new;
    }

    /**
     * Create folder if not exists
     *
     * @param string $path
     * @return bool
     */
    private function existsFolder(string $path): bool
    {
        $result = true;
        try {
            if (!is_dir($path)) {
                mkdir($path);
            }
        } catch (\Exception $e) {
            $result = false;
        }
        return $result;
    }

    /**
     * @throws NoSuchEntityException
     */

    public function getFileSize($file)
    {
        return $this->mediaDirectory->stat($file)['size'];
    }

    /**
     * print data
     *
     * @author Carlos Hernan Aguilar Hurado <caguilar@aventi.co>
     * @date 28/04/20
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
     * @throws \Magento\Framework\Exception\LocalizedException
     */

    /**
     * Print the resume
     *
     * @author Carlos Hernan Aguilar Hurado <caguilar@aventi.co>
     * @date 28/04/20
     * @param array $data
     */
    private function resumen($data=[])
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
}
