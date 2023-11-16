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

namespace Bss\GiftCard\Model\ResourceModel\Template;

use Bss\GiftCard\Model\Template\Image\Config;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\UrlInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class image
 *
 * Bss\GiftCard\Model\ResourceModel\Template
 */
class Image extends AbstractDb
{
    /**
     * Custom directory relative to the "media" folder
     */
    public const DIRECTORY = 'bss/giftcard';

    public const BASE_IMG_WIDTH = 700;

    public const BASE_IMG_HEIGHT = 560;

    public const THUMBNAIL_IMG_WIDTH = 148;

    public const THUMBNAIL_IMG_HEIGHT = 111;

    /**
     * @var Filesystem
     */
    private $filesystem;

    /**
     * @var \Magento\Framework\Image\Factory
     */
    private $imageFactory;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var Config
     */
    private $imageConfig;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var Filesystem\Io\File
     */
    protected $file;

    /**
     * Image constructor.
     * @param Context $context
     * @param Config $imageConfig
     * @param Filesystem $filesystem
     * @param AdapterFactory $imageFactory
     * @param StoreManagerInterface $storeManager
     * @param DataObjectFactory $dataObjectFactory
     * @param Filesystem\Io\File $file
     */
    public function __construct(
        Context $context,
        Config $imageConfig,
        Filesystem $filesystem,
        AdapterFactory $imageFactory,
        StoreManagerInterface $storeManager,
        DataObjectFactory $dataObjectFactory,
        \Magento\Framework\Filesystem\Io\File $file
    ) {
        parent::__construct(
            $context
        );
        $this->imageConfig = $imageConfig;
        $this->filesystem = $filesystem;
        $this->imageFactory = $imageFactory;
        $this->storeManager = $storeManager;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->file = $file;
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('bss_giftcard_template_images', 'value_id');
    }

    /**
     * Insert image
     *
     * @param mixed $items
     * @param int $templateId
     * @throws LocalizedException
     */
    public function insertImage($items, $templateId)
    {
        foreach ($items as $imageId => $item) {
            if (is_numeric($imageId)) {
                $this->updateImage($item, $imageId);
            } else {
                if (!$item['removed']) {
                    $data = [
                        'position' => $item['position'],
                        'template_id' => $templateId,
                        'value' => $item['file'],
                        'label' => $item['label']
                    ];
                    $dataObject = $this->dataObjectFactory->create();
                    $data = $this->_prepareDataForTable(
                        $dataObject->setData($data),
                        $this->getMainTable()
                    );
                    $this->getConnection()->insert($this->getMainTable(), $data);
                }
            }
        }
    }

    /**
     * Update image
     *
     * @param array $data
     * @param int $imageId
     * @throws LocalizedException
     */
    private function updateImage($data, $imageId)
    {
        if ($data['removed']) {
            $this->getConnection()->delete(
                $this->getMainTable(),
                ['value_id = ?' => $imageId]
            );
        } else {
            $bind = [
                'label' => $data['label'],
                'position' => $data['position']
            ];

            $where = ['value_id = ?' => $imageId];
            $this->getConnection()->update($this->getMainTable(), $bind, $where);
        }
    }

    /**
     * Load by template
     *
     * @param int $templateId
     * @return array
     * @throws FileSystemException
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws \Zend_Db_Statement_Exception
     */
    public function loadByTemplate($templateId)
    {
        $data = [];
        $images = $this->loadDataByTemplate($templateId);
        if (!empty($images)) {
            foreach ($images as $image) {
                $data[] = [
                    'id' => $image['value_id'],
                    'position' => $image['position'],
                    'url' => $this->resize(
                        $image['value'],
                        self::BASE_IMG_WIDTH,
                        self::BASE_IMG_HEIGHT
                    ),
                    'thumbnail' => $this->resize(
                        $image['value'],
                        self::THUMBNAIL_IMG_WIDTH,
                        self::THUMBNAIL_IMG_HEIGHT
                    ),
                    'alt' => $image['label']
                ];
            }
        }
        return $data;
    }

    /**
     * Load data by template
     *
     * @param int $templateId
     * @return array
     * @throws LocalizedException
     * @throws \Zend_Db_Statement_Exception
     */
    public function loadDataByTemplate($templateId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getMainTable()
        )->where(
            'template_id =?',
            $templateId
        );

        $images = [];
        $query = $connection->query($select);
        while ($row = $query->fetch()) {
            $images[] = $row;
        }

        return $this->sortImagesByPosition($images);
    }

    /**
     * Get image
     *
     * @param int $imgId
     * @return \Magento\Framework\DataObject|bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Db_Statement_Exception
     */
    public function getById($imgId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getMainTable()
        )->where(
            'value_id =?',
            $imgId
        );
        $query = $connection->query($select);
        $image = $query->fetch();
        return $image ? $this->dataObjectFactory->create(['data' => $image]) : false;
    }

    /**
     * Sort images array by position key
     *
     * @param array $images
     * @return array
     */
    private function sortImagesByPosition($images)
    {
        if (is_array($images) && !empty($images)) {
            usort($images, function ($imageA, $imageB) {
                return ($imageA['position'] < $imageB['position']) ? -1 : 1;
            });
        }
        return $images;
    }

    /**
     * Resize
     *
     * @param string $image
     * @param int|null $width
     * @param int|null $height
     * @return string
     * @throws FileSystemException
     * @throws NoSuchEntityException
     */
    public function resize($image, $width = null, $height = null)
    {
        $mediaFolder = self::DIRECTORY;
        $mediaDirectory = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $path = 'tmp/' . $mediaFolder . '/image';

        if ($width !== null) {
            $path .= '/' . $width . 'x';
            if ($height !== null) {
                $path .= $height;
            }
        }

        $tmpPath = $this->imageConfig->getTmpMediaUrlStat($image);
        $absolutePath = $mediaDirectory->getAbsolutePath() . $tmpPath;
        $imageResized = $mediaDirectory->getAbsolutePath($path) . $image;
        $filename = $path . $image;
        if (!$mediaDirectory->isFile($filename) && $this->file->fileExists($absolutePath)) {
            $imageResize = $this->imageFactory->create();
            $imageResize->open($absolutePath);
            $imageResize->constrainOnly(true);
            $imageResize->keepTransparency(true);
            $imageResize->keepFrame(true);
            $imageResize->keepAspectRatio(true);
            $imageResize->backgroundColor([255, 255, 255]);
            $imageResize->resize($width, $height);
            $imageResize->save($imageResized);
        }

        return $this->storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $path . $image;
    }
}
