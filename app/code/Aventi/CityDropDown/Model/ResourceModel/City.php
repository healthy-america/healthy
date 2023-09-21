<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\CityDropDown\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

class City extends AbstractDb
{
    protected $_isPkAutoIncrement = false;

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        $this->_init(\Aventi\CityDropDown\Model\City::TABLE, \Aventi\CityDropDown\Api\Data\CityInterface::CITY_ID);
    }
}
