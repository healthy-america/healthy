<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\CityDropDown\Model;

use Aventi\CityDropDown\Api\Data\CityInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\App\ResourceConnection;

class CityRepository implements \Aventi\CityDropDown\Api\CityRepositoryInterface
{
    /**
     * @var \Aventi\CityDropDown\Api\Data\CitySearchResultsInterfaceFactory
     */
    private \Aventi\CityDropDown\Api\Data\CitySearchResultsInterfaceFactory $_searchResultsFactory;

    /**
     * @var \Aventi\CityDropDown\Model\ResourceModel\City\CollectionFactory
     */
    private \Aventi\CityDropDown\Model\ResourceModel\City\CollectionFactory $_cityCollectionFactory;

    /**
     * @var \Aventi\CityDropDown\Api\Data\CityInterfaceFactory
     */
    private \Aventi\CityDropDown\Api\Data\CityInterfaceFactory $_cityFactory;

    /**
     * @var \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface
     */
    private \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface $_collectionProcessor;

    /**
     * @var \Aventi\CityDropDown\Model\ResourceModel\City
     */
    private \Aventi\CityDropDown\Model\ResourceModel\City $_resource;

    /**
     * @var \Aventi\CityDropDown\Model\GeoName
     */
    private \Aventi\CityDropDown\Model\GeoName $geoName;

    /**
     * @param ResourceModel\City $resource
     * @param \Aventi\CityDropDown\Api\Data\CityInterfaceFactory $cityFactory
     * @param ResourceModel\City\CollectionFactory $cityCollectionFactory
     * @param \Aventi\CityDropDown\Api\Data\CitySearchResultsInterfaceFactory $searchResultsFactory
     * @param \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface $collectionProcessor
     * @param \Aventi\CityDropDown\Model\GeoName $geoName
     */
    public function __construct(
        \Aventi\CityDropDown\Model\ResourceModel\City $resource,
        \Aventi\CityDropDown\Api\Data\CityInterfaceFactory $cityFactory,
        \Aventi\CityDropDown\Model\ResourceModel\City\CollectionFactory $cityCollectionFactory,
        \Aventi\CityDropDown\Api\Data\CitySearchResultsInterfaceFactory $searchResultsFactory,
        \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface $collectionProcessor,
        \Aventi\CityDropDown\Model\GeoName $geoName
    ) {
        $this->_resource = $resource;
        $this->_cityFactory = $cityFactory;
        $this->_cityCollectionFactory = $cityCollectionFactory;
        $this->_searchResultsFactory = $searchResultsFactory;
        $this->_collectionProcessor = $collectionProcessor;
        $this->geoName = $geoName;
    }

    /**
     * @inheritDoc
     */
    public function save(CityInterface $city): CityInterface
    {
        try {
            //get country code, region name, postcode, province, longitude, latitude,
            $dataGeoname = $this->geoName->getDataGeoname($city);
            if ($dataGeoname) {
                $city = $this->geoName->saveGeoname($city, $dataGeoname);
            } else {
                $city->setLatitude(0);
                $city->setLongitude(0);
                $city->setProvince('');
            }
            $this->_resource->save($city);
        } catch (\Exception $exception) {
            throw new CouldNotSaveException(__(
                'Could not save the city: %1',
                $exception->getMessage()
            ));
        }
        return $city;
    }

    /**
     * @inheritDoc
     */
    public function get(string $cityId): CityInterface
    {
        $city = $this->_cityFactory->create();
        $this->_resource->load($city, $cityId);
        if (!$city->getId()) {
            throw new NoSuchEntityException(__('City with id "%1" does not exist.', $cityId));
        }
        return $city;
    }

    /**
     * @inheritDoc
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $criteria
    )
    {
        $collection = $this->_cityCollectionFactory->create();

        $this->_collectionProcessor->process($criteria, $collection);

        $searchResults = $this->_searchResultsFactory->create();
        $searchResults->setSearchCriteria($criteria);

        $items = [];
        foreach ($collection as $model) {
            $items[] = $model;
        }

        $searchResults->setItems($items);
        $searchResults->setTotalCount($collection->getSize());
        return $searchResults;
    }

    /**
     * @inheritDoc
     */
    public function delete(CityInterface $city): bool
    {
        try {
            $cityModel = $this->_cityFactory->create();
            $this->_resource->load($cityModel, $city->getCityId());
            $this->_resource->delete($cityModel);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__(
                'Could not delete the City: %1',
                $exception->getMessage()
            ));
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function deleteById(string $cityId): bool
    {
        return $this->delete($this->get($cityId));
    }


    /**
     * @inheritDoc
     */
    public function getIdByFields(array $fields): int
    {
        $cityId = -1;
        $collection = $this->_cityCollectionFactory->create();
        foreach ($fields as $key => $value) {
            $collection->addFieldToFilter($key, ['eq' => $value]);
        }
        $item = $collection->getFirstItem();
        if ($item->getData()) {
            $cityId =  $item->getCityId();
        }
        return (int)$cityId;
    }

    /**
     * @inheritDoc
     */
    public function getByFields(array $fields)
    {
        $city = null;
        $collection = $this->_cityCollectionFactory->create();
        foreach ($fields as $key => $value) {
            $collection->addFieldToFilter($key, ['eq' => $value]);
        }
        $item = $collection->getFirstItem();
        if ($item->getData()) {
            $city =  $item;
        }
        return $city;
    }
}
