<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_GiftCard
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\GiftCard\Block\Adminhtml\Template\Helper\Form\Image;

use Bss\GiftCard\Block\DataProviders\ImageUploadConfig;
use Bss\GiftCard\Model\ResourceModel\Template\Image as ImageResourceModel;
use Bss\GiftCard\Model\Template\Image\Config as ImageConfig;
use Magento\Backend\Block\Media\Uploader;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget;
use Magento\Catalog\Model\Product\Media\Config;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\View\Element\AbstractBlock;

/**
 * Class content
 *
 * Bss\GiftCard\Block\Adminhtml\Template\Helper\Form\Image
 */
class Content extends Widget
{
    /**
     * @var string
     */
    protected $_template = 'template/helper/image.phtml';

    /**
     * @var Config
     */
    private $mediaConfig;

    /**
     * @var EncoderInterface
     */
    private $jsonEncoder;

    /**
     * @var ImageResourceModel
     */
    private $imageResourceModel;

    /**
     * @var \Magento\Catalog\Helper\Image
     */
    private $imageHelper;

    /**
     * @var ImageUploadConfig
     */
    private $imageUploadConfigDataProvider;

    /**
     * @param Context $context
     * @param EncoderInterface $jsonEncoder
     * @param ImageConfig $mediaConfig
     * @param ImageResourceModel $imageResourceModel
     * @param ImageUploadConfig $imageUploadConfigDataProvider
     * @param array $data
     */
    public function __construct(
        Context $context,
        EncoderInterface $jsonEncoder,
        ImageConfig $mediaConfig,
        ImageResourceModel $imageResourceModel,
        ImageUploadConfig $imageUploadConfigDataProvider,
        array $data = []
    ) {
        $this->jsonEncoder = $jsonEncoder;
        $this->mediaConfig = $mediaConfig;
        $this->imageResourceModel = $imageResourceModel;
        $this->imageUploadConfigDataProvider = $imageUploadConfigDataProvider;
        parent::__construct(
            $context,
            $data
        );
    }

    /**
     * Prepare layout
     *
     * @return AbstractBlock
     */
    protected function _prepareLayout()
    {
        $this->addChild(
            'uploader',
            Uploader::class,
            ['image_upload_config_data' => $this->imageUploadConfigDataProvider]
        );

        $this->getUploader()->getConfig()->setUrl(
            $this->_urlBuilder->getUrl('giftcard/template/imageupload')
        )->setFileField(
            'image'
        )->setFilters(
            [
                'images' => [
                    'label' => __('Images (.gif, .jpg, .png)'),
                    'files' => ['*.gif', '*.jpg', '*.jpeg', '*.png'],
                ],
            ]
        );

        return parent::_prepareLayout();
    }

    /**
     * Get json object name
     *
     * @return string
     */
    public function getJsObjectName()
    {
        return $this->getHtmlId() . 'JsObject';
    }

    /**
     * Get uploader
     *
     * Retrieve uploader block
     */
    public function getUploader()
    {
        return $this->getChildBlock('uploader');
    }

    /**
     * Get image json
     *
     * @return string
     */
    public function getImagesJson()
    {
        $templateId = $this->getRequest()->getParam('id');
        if ($templateId) {
            $images = $this->imageResourceModel->loadDataByTemplate($templateId);
            if (!empty($images)) {
                $mediaDir = $this->_filesystem->getDirectoryRead(DirectoryList::MEDIA);
                foreach ($images as &$image) {
                    $image['file'] = $image['value'];
                    $image['url'] = $this->mediaConfig->getTmpMediaUrl($image['value']);
                    try {
                        $fileHandler = $mediaDir->stat($this->mediaConfig->getTmpMediaUrlStat($image['value']));
                        $image['size'] = $fileHandler['size'];
                    } catch (FileSystemException $e) {
                        $image['url'] = $this->getImageHelper()->getDefaultPlaceholderUrl('small_image');
                        $image['size'] = 0;
                        $this->_logger->warning($e);
                    }
                }

                return $this->jsonEncoder->encode($images);
            }
        }
        return '[]';
    }

    /**
     * Get image helper
     *
     * @return \Magento\Catalog\Helper\Image
     */
    private function getImageHelper()
    {
        if ($this->imageHelper === null) {
            $this->imageHelper = \Magento\Framework\App\ObjectManager::getInstance()
                ->get(\Magento\Catalog\Helper\Image::class);
        }
        return $this->imageHelper;
    }

    /**
     * Retrieve uploader block html
     *
     * @return string
     */
    public function getUploaderHtml()
    {
        return $this->getChildHtml('uploader');
    }
}
