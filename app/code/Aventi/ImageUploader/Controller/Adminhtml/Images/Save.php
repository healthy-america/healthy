<?php

namespace Aventi\ImageUploader\Controller\Adminhtml\Images;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Framework\Validation\ValidationException;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Psr\Log\LoggerInterface;

class Save extends \Magento\Backend\App\Action
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

    public function __construct(
        Context $context,
        LoggerInterface $logger,
        UploaderFactory $uploaderFactory,
        Filesystem $filesystem,
        \Aventi\ImageUploader\Model\ImageFactory $imageFactory
    ) {
        parent::__construct($context);
        $this->logger = $logger;
        $this->uploaderFactory = $uploaderFactory;
        $this->imageFactory = $imageFactory;
        $this->mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
    }

    public function execute()
    {
        try {
            if ($this->getRequest()->getMethod() !== 'POST' || !$this->_formKeyValidator->validate($this->getRequest())) {
                throw new LocalizedException(__('Invalid Request'));
            }

            //validate image
            $fileUploader = null;
            $params = $this->getRequest()->getParams();
            try {
                if (isset($params['image']) && count($params['image'])) {
                    foreach ($params['image'] as $imageId) {
                        if (!file_exists($imageId['tmp_name'])) {
                            $imageId['tmp_name'] = $imageId['path'] . '/' . $imageId['file'];
                            $fileUploader = $this->uploaderFactory->create(['fileId' => $imageId]);
                            $fileUploader->setAllowedExtensions(['jpg', 'jpeg', 'png']);
                            $fileUploader->setAllowRenameFiles(true);
                            $fileUploader->setAllowCreateFolders(true);
                            $fileUploader->validateFile();
                            //upload image
                            $info = $fileUploader->save($this->mediaDirectory->getAbsolutePath('imageUploader/images'));
                            /** @var \Aventi\ImageUploader\Model\Image */
                            $image = $this->imageFactory->create();
                            $image->setPath($this->mediaDirectory->getRelativePath('imageUploader/images') . '/' . $info['file']);
                            $sku = explode("_", $imageId['file']);
                            $sku = isset($sku[0]) ? $sku[0] : "undefined";
                            $image->setSku($sku);
                            $image->save();
                        }
                    }
                }
            } catch (ValidationException $e) {
                throw new LocalizedException(__('Image extension is not supported. Only extensions allowed are jpg, jpeg and png'));
            } catch (\Exception $e) {
                //if an except is thrown, no image has been uploaded
                throw new LocalizedException(__('Image is required'));
            }

            $this->messageManager->addSuccessMessage(__('Image uploaded successfully'));

            return $this->_redirect('*/*/index');
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->_redirect('*/*/upload');
        } catch (\Exception $e) {
            error_log($e->getMessage());
            error_log($e->getTraceAsString());
            $this->messageManager->addErrorMessage(__('An error occurred, please try again later.'));
            return $this->_redirect('*/*/upload');
        }
    }
}
