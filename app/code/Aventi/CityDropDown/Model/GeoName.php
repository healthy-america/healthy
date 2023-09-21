<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\CityDropDown\Model;

use Aventi\CityDropDown\Api\Data\CityInterface;
use Magento\Framework\App\ResourceConnection;

class GeoName
{

    /**
     * @var \Magento\Directory\Model\RegionFactory
     */
    private \Magento\Directory\Model\RegionFactory $regionFactory;

    /**
     * @var ResourceConnection
     */
    private ResourceConnection $resourceConnection;

    public function __construct(
        \Magento\Directory\Model\RegionFactory $regionFactory,
        ResourceConnection $resourceConnection
    ) {
        $this->regionFactory = $regionFactory;
        $this->resourceConnection = $resourceConnection;
    }

    public function getDataGeoname(CityInterface $city)
    {
        $data = null;

        if (!$city->getLatitude() || !$city->getLongitude() || !$city->getProvince()) {
            return $data;
        }

        /** @var \Magento\Directory\Model\Region $region */
        $region =  $this->regionFactory->create()->load($city->getRegionId());

        if ($region) {
            $data = [
                'country_code' => $region->getCountryId(),
                'postcode' => $city->getPostcode(),
                'city' =>  ucfirst(strtolower($city->getName() ?? '')),
                'region' => $region->getName(),
                'province' => $city->getProvince(),
                'latitude' => $city->getLatitude(),
                'longitude' => $city->getLongitude(),
            ];

            if ($city->getInventoryGeoname()) {
                $data['entity_id'] = $city->getInventoryGeoname();
            }
        }

        return $data;
    }

    public function saveGeoname(CityInterface $city, $data)
    {
        $connection = $this->resourceConnection->getConnection();
        $tableName = $this->resourceConnection->getTableName('inventory_geoname');

        if (array_key_exists('entity_id', $data)) {
            $where = 'entity_id =' . $data['entity_id'];
            unset($data['entity_id']);
            $connection->update($tableName, $data, $where);
        } else {
            $connection->insert($tableName, $data);
            $lastAddedId = $connection->lastInsertId('inventory_geoname');
            $city->setInventoryGeoname($lastAddedId);
        }

        return $city;
    }
}
