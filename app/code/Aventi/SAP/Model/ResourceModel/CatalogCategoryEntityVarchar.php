<?php
/**
 * Copyright © Aventi All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Model\ResourceModel;

class CatalogCategoryEntityVarchar extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('catalog_category_entity_varchar', 'value_id');
    }
}
