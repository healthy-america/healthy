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

namespace Bss\GiftCard\Model\ResourceModel\Pattern\Code;

use Bss\GiftCard\Model\Config\Source\Status;
use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'code_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Bss\GiftCard\Model\Pattern\Code::class,
            \Bss\GiftCard\Model\ResourceModel\Pattern\Code::class
        );
    }

    /**
     * Filter by pattern
     *
     * @param   int $patternId
     * @return  $this
     */
    public function filterByPattern($patternId)
    {
        return $this->addFieldToFilter('pattern_id', $patternId);
    }

    /**
     * Filter by pattern unused
     *
     * @param   int $patternId
     * @return  $this
     */
    public function filterByPatternUnused($patternId)
    {
        $this->filterByPattern($patternId);
        return $this->addFieldToFilter('main_table.status', Status::BSS_GC_STATUS_ACTIVE);
    }

    /**
     * Init select
     *
     * @return void
     */
    public function _initSelect()
    {
        parent::_initSelect();
        $select = $this->getSelect();
        $select->joinLeft(
            ['order_table' => $this->getResource()->getTable('sales_order')],
            'main_table.order_id = order_table.entity_id',
            [
                'customer_email' => 'order_table.customer_email',
                'increment_id' => 'order_table.increment_id'
            ]
        );
    }
}
