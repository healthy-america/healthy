<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\CityDropDown\Model;

use Aventi\CityDropDown\Api\Data\CityInterface;
use Magento\Framework\Model\AbstractModel;

class City extends AbstractModel implements CityInterface
{
    const TABLE = 'aventi_citydropdown_city';

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(\Aventi\CityDropDown\Model\ResourceModel\City::class);
    }


    /**
     * @inheritDoc
     */
    public function getCityId(): ?string
    {
        return $this->getData(self::CITY_ID);
    }

    /**
     * @inheritDoc
     */
    public function setCityId(string $cityId): CityInterface
    {
        return $this->setData(self::CITY_ID, $cityId);
    }

    /**
     * @inheritDoc
     */
    public function getName(): ?string
    {
        return $this->getData(self::NAME);
    }

    /**
     * @inheritDoc
     */
    public function setName(string $name): CityInterface
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * @inheritDoc
     */
    public function getRegionId(): ?string
    {
        return $this->getData(self::REGION_ID);
    }

    /**
     * @inheritDoc
     */
    public function setRegionId(string $regionId): CityInterface
    {
        return $this->setData(self::REGION_ID, $regionId);
    }

    /**
     * @inheritDoc
     */
    public function getPostcode(): ?string
    {
        return $this->getData(self::POSTCODE);
    }

    /**
     * @inheritDoc
     */
    public function setPostcode(string $postcode): CityInterface
    {
        return $this->setData(self::POSTCODE, $postcode);
    }

    /**
     * @return string|null
     */
    public function getInactivePaymentDelivery(): ?string
    {
        return $this->getData(self::INACTIVE_PAYMENT_DELIVERY);
    }

    /**
     * @param string $inactivePaymentDelivery
     * @return CityInterface
     */
    public function setInactivePaymentDelivery(string $inactivePaymentDelivery): CityInterface
    {
        return $this->setData(self::INACTIVE_PAYMENT_DELIVERY, $inactivePaymentDelivery);
    }

    /**
     * @return string|null
     */
    public function getAvailable(): ?string
    {
        return $this->getData(self::AVAILABLE);
    }

    /**
     * @param string $available
     * @return CityInterface
     */
    public function setAvailable(string $available): CityInterface
    {
        return $this->SetData(self::AVAILABLE, $available);
    }

    /**
     * @return array|mixed|string|null
     */
    public function getProvince()
    {
        return $this->getData(self::PROVINCE);
    }

    /**
     * @param $province
     * @return CityInterface|City
     */
    public function setProvince($province)
    {
        return $this->SetData(self::PROVINCE, $province);
    }

    /**
     * @return array|mixed|string|null
     */
    public function getLatitude()
    {
        return $this->getData(self::LATITUDE);
    }

    /**
     * @param $latitude
     * @return CityInterface|City
     */
    public function setLatitude($latitude)
    {
        return $this->SetData(self::LATITUDE, $latitude);
    }

    /**
     * @return array|mixed|string|null
     */
    public function getLongitude()
    {
        return $this->getData(self::LONGITUDE);
    }

    /**
     * @param $longitude
     * @return CityInterface|City
     */
    public function setLongitude($longitude)
    {
        return $this->SetData(self::LONGITUDE, $longitude);
    }

    /**
     * @return array|mixed|string|null
     */
    public function getInventoryGeoname()
    {
        return $this->getData(self::INVENTORY_GEONAME);
    }

    /**
     * @param $inventoryGeoname
     * @return CityInterface|City
     */
    public function setInventoryGeoname($inventoryGeoname)
    {
        return $this->SetData(self::INVENTORY_GEONAME, $inventoryGeoname);
    }
}
