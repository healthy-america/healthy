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

namespace Bss\GiftCard\Model\ResourceModel\Pattern\History;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class collection
 *
 * Bss\GiftCard\Model\ResourceModel\Pattern
 */
class Collection extends AbstractCollection
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Bss\GiftCard\Model\Pattern\History::class,
            \Bss\GiftCard\Model\ResourceModel\Pattern\History::class
        );
    }

    /**
     * Init select
     *
     * @return  void
     */
    public function _initSelect()
    {
        parent::_initSelect();
        $select = $this->getSelect();
        $select->joinLeft(
            ['order_table' => $this->getResource()->getTable('sales_order')],
            'main_table.quote_id = order_table.quote_id',
            [
                'order_id' => 'order_table.entity_id',
                'increment_id' => 'order_table.increment_id'
            ]
        )->group('history_id');
    }
}
