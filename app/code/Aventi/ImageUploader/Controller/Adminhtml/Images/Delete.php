<?php

namespace Aventi\ImageUploader\Controller\Adminhtml\Images;

use Aventi\ImageUploader\Model\ResourceModel\Image\CollectionFactory as CollectionImage;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Driver\File;
use Magento\Framework\Validation\ValidationException;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Psr\Log\LoggerInterface;

class Delete extends \Magento\Backend\App\Action
{
    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     *
     * @var UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * @var \Aventi\ImageUploader\Model\ImageFactory
     */
    protected $imageFactory;

    /**
     * @var Filesystem\Directory\WriteInterface
     */
    protected $mediaDirectory;

    /**
     * @var CollectionImage
     */
    private $_collectionImage;

    /**
     * @var File
     */
    private $_file;

    /**
     * @var DirectoryList
     */
    private $directoryList;

    public function __construct(
        Context $context,
        LoggerInterface $logger,
        UploaderFactory $uploaderFactory,
        Filesystem $filesystem,
        DirectoryList $directoryList,
        \Aventi\ImageUploader\Model\ImageFactory $imageFactory,
        CollectionImage $_collectionImage,
        File $file
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->uploaderFactory = $uploaderFactory;
        $this->imageFactory = $imageFactory;
        $this->directoryList = $directoryList;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $this->_collectionImage = $_collectionImage;
        $this->_file = $file;
    }

    public function execute()
    {
        try {
            if ($this->getRequest()->getMethod() !== 'GET') {
                throw new LocalizedException(__('Invalid Request'));
            }
            $params = $this->getRequest()->getParams();
            try {
                if (isset($params['id'])) {
                    $imageCollection = $this->_collectionImage->create();
                    $imageCollection->addFieldToFilter('image_id', $params['id'])->load();
                    foreach ($imageCollection as $item) {
                        $routeImage = $this->directoryList->getPath(DirectoryList::MEDIA) . "/" . $item->getPath();
                        if ($this->_file->isExists($routeImage)) {
                            $this->_file->deleteFile($routeImage);
                            $item->delete();
                        }
                    }
                }
            } catch (ValidationException $e) {
                throw new LocalizedException(__($e));
            } catch (\Exception $e) {
                //if an except is thrown, no image has been uploaded
                throw new LocalizedException(__($e));
            }
            $this->messageManager->addSuccessMessage(__('Image Removed successfully'));
            return $this->_redirect('*/*/index');
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->_redirect('*/*/index');
        } catch (\Exception $e) {
            error_log($e->getMessage());
            error_log($e->getTraceAsString());
            $this->messageManager->addErrorMessage(__('An error occurred, please try again later.'));
            return $this->_redirect('*/*/index');
        }
    }
}
