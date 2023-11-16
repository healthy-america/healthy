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

namespace Bss\GiftCard\Model\ResourceModel\Pattern;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class collection
 *
 * Bss\GiftCard\Model\ResourceModel\Pattern
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'pattern_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Bss\GiftCard\Model\Pattern::class,
            \Bss\GiftCard\Model\ResourceModel\Pattern::class
        );
    }

    /**
     * Filter visiable
     *
     * @return Collection
     */
    public function filterVisiable()
    {
        return $this->addFieldToFilter('pattern_code_qty_max', ['gt' => 0]);
    }
}
