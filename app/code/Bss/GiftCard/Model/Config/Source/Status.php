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

namespace Bss\GiftCard\Model\Config\Source;

use Magento\Framework\Option\ArrayInterface;

/**
 * Class status
 *
 * Bss\GiftCard\Model\Config\Source
 */
class Status implements ArrayInterface
{
    public const BSS_GC_STATUS_INACTIVE = 0;

    public const BSS_GC_STATUS_ACTIVE = 1;

    public const BSS_GC_STATUS_EXPIRED = 2;

    public const BSS_GC_STATUS_USED = 3;

    public const BSS_GC_STATUS_USED_IN_ORDER = 0;

    public const BSS_GC_STATUS_UPDATE = 1;

    /**
     * To option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::BSS_GC_STATUS_INACTIVE, 'label' => __('Inactive')],
            ['value' => self::BSS_GC_STATUS_ACTIVE, 'label' => __('Active')],
            ['value' => self::BSS_GC_STATUS_EXPIRED, 'label' => __('Expired')],
            ['value' => self::BSS_GC_STATUS_USED, 'label' => __('Used')]
        ];
    }

    /**
     * Get option array
     *
     * @return array
     */
    public function getOptionArray()
    {
        return [
            self::BSS_GC_STATUS_INACTIVE => __('Inactive'),
            self::BSS_GC_STATUS_ACTIVE => __('Active'),
            self::BSS_GC_STATUS_EXPIRED => __('Expired'),
            self::BSS_GC_STATUS_USED => __('Used')
        ];
    }
}
