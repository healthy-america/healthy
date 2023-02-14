<?php
/**
 * Copyright © Aventi All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Model\ResourceModel\CatalogCategoryEntityVarchar;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'value_id';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            \Aventi\SAP\Model\CatalogCategoryEntityVarchar::class,
            \Aventi\SAP\Model\ResourceModel\CatalogCategoryEntityVarchar::class
        );
    }
}
