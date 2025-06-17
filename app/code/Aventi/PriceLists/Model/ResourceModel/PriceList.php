<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\PriceLists\Model\ResourceModel;

use Aventi\PriceLists\Api\Data\PriceListInterface;

class PriceList extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    protected $_idFieldName = 'entity_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Aventi\PriceLists\Model\PriceList::TABLE,
            PriceListInterface::ENTITY_ID
        );
    }
}
