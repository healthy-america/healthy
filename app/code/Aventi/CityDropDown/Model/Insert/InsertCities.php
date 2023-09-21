<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\CityDropDown\Model\Insert;

use Magento\Framework\Exception\LocalizedException;

class InsertCities
{
    const REGION_TABLE = 'directory_country_region';

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private \Magento\Framework\App\ResourceConnection $_resourceConnection;

    /**
     * @var \Magento\Framework\File\Csv
     */
    private \Magento\Framework\File\Csv $_csv;

    /**
     * @var \Aventi\CityDropDown\Model\CityRepository
     */
    private \Aventi\CityDropDown\Model\CityRepository $_cityRepository;

    /**
     * @var \Aventi\CityDropDown\Api\Data\CityInterfaceFactory
     */
    private \Aventi\CityDropDown\Api\Data\CityInterfaceFactory $_cityInterfaceFactory;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     * @param \Magento\Framework\File\Csv $csv
     * @param \Aventi\CityDropDown\Model\CityRepository $cityRepository
     * @param \Aventi\CityDropDown\Api\Data\CityInterfaceFactory $cityInterfaceFactory
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Framework\File\Csv $csv,
        \Aventi\CityDropDown\Model\CityRepository $cityRepository,
        \Aventi\CityDropDown\Api\Data\CityInterfaceFactory $cityInterfaceFactory
    ) {
        $this->_resourceConnection = $resourceConnection;
        $this->_csv = $csv;
        $this->_cityRepository = $cityRepository;
        $this->_cityInterfaceFactory = $cityInterfaceFactory;
    }

    /**
     * Read city data from a csv and create records in the db
     *
     * @param $folder
     * @return void
     * @throws LocalizedException
     */
    public function insertCities($folder): void
    {
        $cities = $this->importCities($folder);
        $this->createCities($cities);
    }

    /**
     * Insert the cities in the db from a given array
     *
     * @param $cities
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function createCities($cities): void
    {
        foreach ($cities as $city) {
            $regionId = $this->getRegionId($city['region_code']);
            $idCity = $this->getIdCity($city, $regionId);

            if ($regionId === '') {
                continue;
            }

            $_city = $idCity === -1 ? $this->_cityInterfaceFactory->create() : $this->_cityRepository->get((string)$idCity);
            $_city->setName($city['name']);
            $_city->setRegionId($regionId);
            $_city->setPostcode($city['postcode']);
            $this->_cityRepository->save($_city);
        }
    }

    /**
     * Read city data from a csv
     *
     * @param $file
     * @return array|null
     */
    public function importCities($file): ?array
    {
        $cities = null;
        try {
            if (file_exists($file)) {
                $csvData = $this->_csv->getData($file);
                $cities = [];
                foreach ($csvData as $data) {
                    $cities[] = [
                        'name' => $data[0],
                        'region_code' => $data[1],
                        'postcode' => $data[2]
                    ];
                }
            }
        } catch (\Exception $e) {
            $cities = null;
        }
        return $cities;
    }

    /**
     * Get the city id with code, region_id and matching postcode
     *
     * @param $data
     * @param $regionId
     * @return int
     */
    private function getIdCity($data, $regionId): int
    {
        $fields = [
            'name' => $data['name'],
            'main_table.region_id' =>  $regionId,
            'postcode' => $data['postcode']
        ];

        return $this->_cityRepository->getIdByFields($fields);
    }

    /**
     * Get the id of a Co region with your code
     *
     * @param $code
     * @return string
     */
    private function getRegionId($code): string
    {
        $connection = $this->_resourceConnection->getConnection();
        $tableName = $this->_resourceConnection->getTableName(self::REGION_TABLE);
        $selectQry = $connection->select()
            ->from($tableName)
            ->where('country_id = ?', 'CO')
            ->where('code = ?', $code);
        $regions = $connection->fetchAll($selectQry);
        return $regions ? $regions[0]['region_id'] : '';
    }
}
