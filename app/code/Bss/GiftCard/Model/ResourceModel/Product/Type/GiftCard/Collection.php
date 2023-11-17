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
namespace Bss\GiftCard\Model\ResourceModel\Product\Type\GiftCard;

use Bss\GiftCard\Model\Product\Type\GiftCard;

class Collection extends \Magento\Catalog\Model\ResourceModel\Product\Collection
{
    /**
     * Get gift card collection
     *
     * @param int|string $storeIds
     * @return $this
     */
    public function getGiftCardCollection($storeIds)
    {
        $this->addStoresFilter($storeIds);
        $this->addAttributeToFilter('type_id', ['eq' => GiftCard::BSS_GIFT_CARD]);
        return $this;
    }

    /**
     * Add store filter
     *
     * @param array|null $storeIds
     * @return $this
     */
    public function addStoresFilter($storeIds)
    {
        if (empty($storeIds)) {
            return $this;
        }
        foreach ($storeIds as $storeId) {
            $this->addStoreFilter($storeId);
        }
        return $this;
    }
}
