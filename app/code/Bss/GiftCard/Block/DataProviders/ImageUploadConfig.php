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
namespace Bss\GiftCard\Block\DataProviders;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Bss\GiftCard\Model\Image\UploadResizeConfigInterface;

/**
 * Provides additional data for image uploader
 */
class ImageUploadConfig
{
    /**
     * Get image resize configuration
     *
     * @return int
     */
    public function getIsResizeEnabled()
    {
        return true;
    }
}
