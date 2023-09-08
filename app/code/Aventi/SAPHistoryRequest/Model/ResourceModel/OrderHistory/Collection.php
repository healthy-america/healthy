<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAPHistoryRequest\Model\ResourceModel\OrderHistory;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * @inheritDoc
     */
    protected $_idFieldName = 'orderhistory_id';

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            \Aventi\SAPHistoryRequest\Model\OrderHistory::class,
            \Aventi\SAPHistoryRequest\Model\ResourceModel\OrderHistory::class
        );
    }
}

