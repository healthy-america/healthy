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

namespace Bss\GiftCard\Model\Template\Image;

use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class config
 * Bss\GiftCard\Model\Template\Image
 */
class Config extends \Magento\Catalog\Model\Product\Media\Config
{
    public const PATH_BASE_IMAGE = 'bss/giftcard/image';

    /**
     * Filesystem directory path of product images relatively to media folder
     *
     * @return string
     */
    public function getBaseMediaPathAddition()
    {
        return self::PATH_BASE_IMAGE;
    }

    /**
     * Web-based directory path of product images relatively to media folder
     *
     * @return string
     */
    public function getBaseMediaUrlAddition()
    {
        return self::PATH_BASE_IMAGE;
    }

    /**
     * Get base media path
     *
     * @return string
     */
    public function getBaseMediaPath()
    {
        return self::PATH_BASE_IMAGE;
    }

    /**
     * Get base media url
     *
     * @return string
     * @throws NoSuchEntityException
     */
    public function getBaseMediaUrl()
    {
        return $this->storeManager->getStore()
                ->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . self::PATH_BASE_IMAGE;
    }

    /**
     * Get tmp media url stat
     *
     * @param string $file
     * @return string
     */
    public function getTmpMediaUrlStat($file)
    {
        return 'tmp/' . $this->getBaseMediaUrlAddition() . '/' . $this->_prepareFile($file);
    }
}
