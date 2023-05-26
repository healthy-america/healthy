<?php

namespace Aventi\ImageUploader\Controller\Adminhtml\Images;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class TempUpload extends \Magento\Backend\App\Action
{

    /**
     *
     * @var UploaderFactory
     */
    protected $uploaderFactory;

    /**
     * @var Filesystem\Directory\WriteInterface
     */
    protected $mediaDirectory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    public function __construct(
        Context $context,
        UploaderFactory $uploaderFactory,
        Filesystem $filesystem,
        LoggerInterface $logger,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->uploaderFactory = $uploaderFactory;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $this->logger = $logger;
        $this->storeManager = $storeManager;
    }

    public function execute()
    {
        $jsonResult = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        try {
            $fileUploader = $this->uploaderFactory->create(['fileId' => 'image']);
            $fileUploader->setAllowedExtensions(['jpg', 'jpeg', 'png']);
            $fileUploader->setAllowRenameFiles(true);
            $fileUploader->setAllowCreateFolders(true);
            $fileUploader->setFilesDispersion(false);
            $fileUploader->validateFile();
            $result = $fileUploader->save($this->mediaDirectory->getAbsolutePath('tmp/imageUploader/images'));
            $this->logger->info("rst: " . json_encode($result['file']));
            if (str_contains($result['file'], '_')) {
                $result['url'] = $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
                    . 'tmp/imageUploader/images/' . ltrim(str_replace('\\', '/', $result['file']), '/');
                return $jsonResult->setData($result);
            }
            return $jsonResult->setData(['errorcode' => 0, 'error' => __('The image %1 does not have the necessary format to be processed', $result['file'])]);
        } catch (LocalizedException $e) {
            return $jsonResult->setData(['errorcode' => 0, 'error' => $e->getMessage()]);
        } catch (\Exception $e) {
            error_log($e->getMessage());
            error_log($e->getTraceAsString());
            return $jsonResult->setData(['errorcode' => 0, 'error' => __('An error occurred, please try again later.')]);
        }
    }
}
