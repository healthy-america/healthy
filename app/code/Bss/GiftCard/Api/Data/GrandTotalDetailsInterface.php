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

namespace Bss\GiftCard\Api\Data;

/**
 * Interface GrandTotalDetailsInterface
 * Bss\GiftCard\Api\Data
 */
interface GrandTotalDetailsInterface
{
    /**
     * Get gift card amount value
     *
     * @return float|string
     */
    public function getAmount();

    /**
     * Set amount
     *
     * @param string|float $amount
     * @return $this
     */
    public function setAmount($amount);

    /**
     * Get gift card title
     *
     * @return string
     */
    public function getTitle();

    /**
     * Set title
     *
     * @param string $title
     * @return $this
     */
    public function setTitle($title);
}
