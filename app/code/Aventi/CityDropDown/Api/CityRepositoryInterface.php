<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\CityDropDown\Api;

interface CityRepositoryInterface
{

    /**
     * Save City
     * @param \Aventi\CityDropDown\Api\Data\CityInterface $city
     * @return \Aventi\CityDropDown\Api\Data\CityInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(
        \Aventi\CityDropDown\Api\Data\CityInterface $city
    ): \Aventi\CityDropDown\Api\Data\CityInterface;

    /**
     * Retrieve City
     * @param string $cityId
     * @return \Aventi\CityDropDown\Api\Data\CityInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function get(string $cityId): \Aventi\CityDropDown\Api\Data\CityInterface;

    /**
     * Retrieve City matching the specified criteria.
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Aventi\CityDropDown\Api\Data\CitySearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
    );

    /**
     * Delete City
     * @param \Aventi\CityDropDown\Api\Data\CityInterface $city
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(
        \Aventi\CityDropDown\Api\Data\CityInterface $city
    ): bool;

    /**
     * Delete City by ID
     * @param string $cityId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById(string $cityId): bool;

    /**
     * Get city id by fields
     * @param array $fields
     * @return int
     */
    public function getIdByFields(array $fields): int;


    /**
     * Get city by fields
     * @param array $fields
     * @return int
     */
    public function getByFields(array $fields);
}
