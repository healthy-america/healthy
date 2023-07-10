<?php

namespace Aventi\Servientrega\Controller\Adminhtml\Shipment;

use Exception;
use Magento\Backend\App\Action;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\ResourceModel\Order\Shipment\CollectionFactory;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use ZipArchive;

class Label extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::shipment';

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    protected $redirectUrl = '*/*/';

    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    private $fileSystemDir;

    /**
     * Label constructor.
     * @param Action\Context $context
     * @param LoggerInterface $logger
     * @param FileFactory $fileFactory
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param \Magento\Framework\Filesystem\DirectoryList $fileSystemDir
     */
    public function __construct(
        Action\Context $context,
        LoggerInterface $logger,
        FileFactory $fileFactory,
        Filter $filter,
        CollectionFactory $collectionFactory,
        \Magento\Framework\Filesystem\DirectoryList $fileSystemDir
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->fileFactory = $fileFactory;
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->fileSystemDir = $fileSystemDir;
    }

    /**
     * Handles creating process for ZIP archive containing PDF guides
     * from Servientrega Shipments. This is a custom "Mass Action" performance
     * for multiple saving process.
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $zip = new ZipArchive();
        $files_array = [];
        try {
            $pathGuides = $this->fileSystemDir->getPath('pub') . '/servientrega';

            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $dirs = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($pathGuides));


            $zip->open('/tmp/Servientrega_Guides.zip', ZipArchive::CREATE | ZipArchive::OVERWRITE);

            foreach ($dirs as $dir) {
                if ($dir->isDir()) {
                    continue;
                }
                if (in_array(trim(pathinfo($dir)['extension']), ['pdf'])) {
                    $imageFileName = trim(pathinfo($dir)['filename']);
                    $files_array[$imageFileName] = $dir->getPathName();
                }
            }

            $content = $this->getZipFile($collection, $pathGuides, $files_array, $zip);

            return $this->fileFactory->create(
                'Servientrega_Guides.zip',
                $content,
                DirectoryList::SYS_TMP,
                'application/zip'
            );
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath($this->redirectUrl);
        }
    }

    /**
     * Retrieves array zip content file.
     * @param $collection
     * @param $pathGuides
     * @param $files_array
     * @param $zip
     * @return array
     */
    public function getZipFile($collection, $pathGuides, $files_array, $zip): array
    {
        /** @var Shipment $shipment */
        foreach ($collection->getItems() as $shipment) {
            $trackNumbers = $shipment->getOrder()->getTracksCollection();
            foreach ($trackNumbers as $trackNumber) {
                if (array_key_exists($trackNumber->getTrackNumber(), $files_array)) {
                    foreach ($files_array as $key => $value) {
                        if ($key == $trackNumber->getTrackNumber()) {
                            $zip->addFile($value, $key . '.pdf');
                        }
                    }
                }
            }
        }
        $zip->close();

        $content = [];
        $content['type'] = 'filename';
        $content['value'] = '/tmp/Servientrega_Guides.zip';
        $content['rm'] = true;

        return $content;
    }
}
