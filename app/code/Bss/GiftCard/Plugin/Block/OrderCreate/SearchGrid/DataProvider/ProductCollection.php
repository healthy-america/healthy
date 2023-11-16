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
 * @copyright  Copyright (c) Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\GiftCard\Plugin\Block\OrderCreate\SearchGrid\DataProvider;

use Bss\GiftCard\Model\Product\Type\GiftCard;
use Magento\Catalog\Model\ResourceModel\Product\Collection;
use Magento\Sales\Block\Adminhtml\Order\Create\Search\Grid\DataProvider\ProductCollection as SearchProductCollection;

class ProductCollection
{
    /**
     * Get collection fgr store
     *
     * @param SearchProductCollection $subject
     * @param Collection $collection
     * @return Collection
     */
    public function afterGetCollectionForStore(
        SearchProductCollection $subject,
        $collection
    ) {
        $collection->addAttributeToFilter(
            'type_id',
            ['neq' => GiftCard::BSS_GIFT_CARD]
        );

        return $collection;
    }
}
