<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\CityDropDown\Model\ResourceModel\City;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

class Collection extends AbstractCollection
{

    /**
     * @inheritDoc
     */
    protected $_idFieldName = \Aventi\CityDropDown\Api\Data\CityInterface::CITY_ID;

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(
            \Aventi\CityDropDown\Model\City::class,
            \Aventi\CityDropDown\Model\ResourceModel\City::class
        );
    }

    protected function _initSelect()
    {
        parent::_initSelect();

        $this->getSelect()->join(
            ['region' => $this->getTable('directory_country_region')],
            'region.region_id = main_table.region_id',
            ['region_name' => 'region.default_name']
        );
        $this->addFilterToMap('region_name', 'region.default_name');
    }
}
