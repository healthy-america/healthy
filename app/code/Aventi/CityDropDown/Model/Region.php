<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\CityDropDown\Model;

class Region
{
    const REGION_TABLE = 'directory_country_region';

    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    private \Magento\Directory\Model\RegionFactory $_regionFactory;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private \Magento\Framework\App\ResourceConnection $_resourceConnection;

    /**
     * @param \Magento\Directory\Model\RegionFactory $regionFactory
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     */
    public function __construct(
        \Magento\Directory\Model\RegionFactory  $regionFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection

    ) {
        $this->_regionFactory = $regionFactory;
        $this->_resourceConnection = $resourceConnection;
    }

    /**
     * @param $regionId
     * @return \Magento\Directory\Model\Region|null
     */
    public function getRegion($regionId): ?\Magento\Directory\Model\Region
    {
        try {
            return $this->_regionFactory->create()->load($regionId);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Create region by array fields
     *
     * @param array $fields
     * @return \Magento\Directory\Model\Region
     * @throws \Exception
     */
    public function create(array $fields): \Magento\Directory\Model\Region
    {
        $region = $this->_regionFactory->create();
        $region->setCountryId($fields['country_id']);
        $region->setCode($fields['code']);
        $region->setDefaultName($fields['default_name']);
        $region->save();
        return $region;
    }
    /**
     * Get the id by fields
     *
     * @param $fields
     * @return string
     */
    public function getIdByFields(array $fields): string
    {
        $connection = $this->_resourceConnection->getConnection();
        $tableName = $this->_resourceConnection->getTableName(self::REGION_TABLE);
        $selectQry = $connection->select()->from($tableName);
        foreach ($fields as $key => $value){
            $selectQry->where($key.' = ?', $value);
        }
        $regions = $connection->fetchAll($selectQry);
        return $regions ?  $regions[0]['region_id'] : '';
    }
}
