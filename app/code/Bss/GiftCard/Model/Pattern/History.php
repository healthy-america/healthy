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

namespace Bss\GiftCard\Model\Pattern;

use Bss\GiftCard\Model\ResourceModel\Pattern\History as ResourceModelHistory;
use Magento\Framework\Model\AbstractModel;

/**
 * Class history
 *
 * Bss\GiftCard\Model\Pattern
 */
class History extends AbstractModel
{
    /**
     * Construct
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(ResourceModelHistory::class);
    }
}
