<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\CityDropDown\Api\Data;

interface CityInterface
{

    const POSTCODE = 'postcode';
    const CITY_ID = 'city_id';
    const NAME = 'name';
    const REGION_ID = 'region_id';
    const INACTIVE_PAYMENT_DELIVERY = 'inactive_payment_delivey';
    const AVAILABLE = 'available';

    const PROVINCE = 'province';

    const LATITUDE = 'latitude';

    const LONGITUDE = 'longitude';

    const INVENTORY_GEONAME = 'inventory_geoname';

    /**
     * Get city_id
     * @return string|null
     */
    public function getCityId(): ?string;

    /**
     * Set city_id
     * @param string $cityId
     * @return $this
     */
    public function setCityId(string $cityId): CityInterface;

    /**
     * Get name
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * Set name
     * @param string $name
     * @return $this
     */
    public function setName(string $name): CityInterface;

    /**
     * Get region_id
     * @return string|null
     */
    public function getRegionId(): ?string;

    /**
     * Set region_id
     * @param string $regionId
     * @return $this
     */
    public function setRegionId(string $regionId): CityInterface;

    /**
     * Get postcode
     * @return string|null
     */
    public function getPostcode(): ?string;

    /**
     * Set postcode
     * @param string $postcode
     * @return $this
     */
    public function setPostcode(string $postcode): CityInterface;

    /**
     * Get payment_delivery
     * @return string|null
     */
    public function getInactivePaymentDelivery(): ?string;

    /**
     * Set city_id
     * @param string $paymentDelivery
     * @return $this
     */
    public function setInactivePaymentDelivery(string $paymentDelivery): CityInterface;

    /**
     * Get paymentDelivery
     * @return string|null
     */
    public function getAvailable(): ?string;

    /**
     * Set city_id
     * @param string $available
     * @return $this
     */
    public function setAvailable(string $available): CityInterface;

    /**
     * Get province
     * @return string|null
     */
    public function getProvince();

    /**
     * Set province
     * @param string $province
     * @return CityInterface
     */
    public function setProvince($province);

    /**
     * Get latitude
     * @return string|null
     */
    public function getLatitude();

    /**
     * Set latitude
     * @param string $latitude
     * @return CityInterface
     */
    public function setLatitude($latitude);

    /**
     * Get longitude
     * @return string|null
     */
    public function getLongitude();

    /**
     * Set longitude
     * @param string $longitude
     * @return CityInterface
     */
    public function setLongitude($longitude);

    /**
     * Get inventory_geoname
     * @return string|null
     */
    public function getInventoryGeoname();

    /**
     * Set inventory_geoname
     * @param string $inventoryGeoname
     * @return CityInterface
     */
    public function setInventoryGeoname($inventoryGeoname);
}
