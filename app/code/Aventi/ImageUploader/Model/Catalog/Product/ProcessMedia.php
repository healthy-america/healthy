<?php
/**
 * Aventi Solutions
 * Julian D Amaya <jamaya@aventi.co>
 * 2022
 *
 */

namespace Aventi\ImageUploader\Model\Catalog\Product;

use Aventi\ImageUploader\Model\Image;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Filesystem\Driver\File;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ProcessMedia
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     * @param Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $_productCollection
     */
    private $_productCollection;

    /**
     * @param File $_fileRepository
     */
    private $_fileRepository;

    /**
     * @param DirectoryList $_directoryList
     */
    private $_directoryList;

    /**
     * @param ResourceConnection $_resourceConnection
     */
    private $_resourceConnection;

    /**
     * @var LoggerInterface $logger
     */
    protected $logger;

    /**
     * @var Image
     */
    private $imagen;

    /**
     * @param CollectionFactory $_productCollection
     * @param File $_fileRepository
     * @param DirectoryList $_directoryList
     * @param ResourceConnection $_resourceConnection
     * @param LoggerInterface $logger
     * @param Image $imagen
     */
    public function __construct(
        CollectionFactory $_productCollection,
        File $_fileRepository,
        DirectoryList $_directoryList,
        ResourceConnection $_resourceConnection,
        LoggerInterface $logger,
        Image $imagen
    ) {
        $this->_productCollection = $_productCollection;
        $this->_fileRepository = $_fileRepository;
        $this->_directoryList = $_directoryList;
        $this->_resourceConnection = $_resourceConnection;
        $this->logger = $logger;
        $this->imagen = $imagen;
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function processAllProducts()
    {
        /** @var Collection $collection */
        $collection = $this->_productCollection->create();
        $collection->addAttributeToSelect('*');
        $collection->addMediaGalleryData();
        $products = $collection->getItems();

        $arrProds = [];
        $catalogPath = $this->_directoryList->getPath(DirectoryList::MEDIA) . '/catalog/product';

        $connection = $this->_resourceConnection->getConnection(ResourceConnection::DEFAULT_CONNECTION);

        foreach ($products as $product) {
            $i=2;
            $primaryImg = 1; //1=available to change, 2=cannot change (flag)
            $requireAdd = 0;

            $arrProd['id'] = $product->getId();
            $arrProd['name'] = $product->getName();
            $arrProd['base'] = 0;

            $images = $product->getMediaGalleryEntries();
            $arrItems = [];
            foreach ($images as $image) {
                if ($image->getMediaType()=='image') {
                    $oldName = $image->getFile();
                    $extension = explode(".", $oldName);
                    $extension = end($extension);
                    if ($primaryImg != 2) {
                        $primaryImg = count($image->getTypes()) > 0 ? 1 : 0;
                        if ($primaryImg) {
                            $newName = $product->getId() . "_1";
                            $primaryImg=2;
                            $arrProd['base'] = 1;
                        } else {
                            $newName = $product->getId() . "_" . $i;
                            $i++;
                        }
                    } else {
                        $newName = $product->getId() . "_" . $i;
                        $i++;
                    }
                    $newName = $newName . "." . $extension;
                    $newName = "/" . $newName[0] . "/" . $newName[1] . "/" . $newName;
                    if ($oldName!=$newName) {
                        try {
                            if (!$this->_fileRepository->isExists($catalogPath . '/' . $newName[1] . '/' . $newName[3])) {
                                $this->_fileRepository->createDirectory($catalogPath . '/' . $newName[1] . '/' . $newName[3]);
                            }
                            $this->_fileRepository->copy($catalogPath . $oldName, $catalogPath . $newName);
                            $requireAdd = 1;
                            @$connection->update(
                                @$this->_resourceConnection->getTableName('catalog_product_entity_media_gallery'),
                                ['value' => $newName],
                                ['value_id = ?' => $image->getId()]
                            );
                            @$connection->query(
                                @$connection->update(
                                    @$this->_resourceConnection->getTableName('catalog_product_entity_varchar'),
                                    ['value' => $newName],
                                    ['value = ?' => $oldName]
                                )
                            );
                        } catch (\Exception $e) {
                            $this->logger->debug('Error Catch: ' . $e->getMessage());
                            continue;
                        }
                    }
                    $arrItems[] = [
                        'oldName' => $oldName,
                        'newName' => $newName
                    ];
                }
            }

            if ($primaryImg!=2 && count($arrItems)>0) {
                /*$product->setSmallImage($arrItems[0]['newName'])
                    ->setThumbnail($arrItems[0]['newName'])
                    ->setImage($arrItems[0]['newName'])
                    ->save();*/
                $arrProd['items'] = $arrItems;
                $arrProds[] = $arrProd;
            } elseif ($requireAdd) {
                $arrProd['items'] = $arrItems;
                $arrProds[] = $arrProd;
            }
        }
        $this->logger->debug(json_encode($arrProds));
        return $arrProds;
    }

    /**
     * @return OutputInterface|null
     */
    private function getOutput(): ?OutputInterface
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
}
