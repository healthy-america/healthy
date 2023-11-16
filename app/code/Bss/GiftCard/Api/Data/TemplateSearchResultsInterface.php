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
 * @copyright  Copyright (c) 2020 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\GiftCard\Api\Data;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface TemplateSearchResultsInterface
 *
 * Bss\GiftCard\Api\Data
 */
interface TemplateSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get items
     *
     * @return TemplateInterface[]
     */
    public function getItems();

    /**
     * Set items
     *
     * @param TemplateInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}
