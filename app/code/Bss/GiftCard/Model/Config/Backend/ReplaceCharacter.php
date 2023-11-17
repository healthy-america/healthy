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
 * @package    Bss_OrderDeliveryDate
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\GiftCard\Model\Config\Backend;

use Magento\Framework\App\Config\Value;
use Magento\Framework\Exception\LocalizedException;

class ReplaceCharacter extends \Magento\Framework\App\Config\Value
{
    /**
     * Save
     *
     * @return Value
     * @throws LocalizedException
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        $condition = "/[0-9]+/";
        if (preg_match($condition, $value)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __("Config Replace Hidden Character not allow input number ")
            );
        }
        return parent::beforeSave();
    }
}
