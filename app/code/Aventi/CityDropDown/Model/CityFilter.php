<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\CityDropDown\Model;

class CityFilter
{
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private \Magento\Framework\Api\SearchCriteriaBuilder $_searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    private \Magento\Framework\Api\FilterBuilder $_filterBuilder;

    /**
     * @var \Magento\Framework\Api\Search\FilterGroupBuilder
     */
    private \Magento\Framework\Api\Search\FilterGroupBuilder $_filterGroupBuilder;

    /**
     * @var \Magento\Framework\Api\SortOrder
     */
    private \Magento\Framework\Api\SortOrder $_sortOrder;

    /**
     * @var \Aventi\CityDropDown\Model\CityRepository
     */
    private CityRepository $_cityRepository;

    private \Aventi\CityDropDown\Model\ResourceModel\City\CollectionFactory $_cityCollectionFactory;


    /**
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Framework\Api\SortOrder $sortOrder
     * @param \Magento\Framework\Api\Search\FilterGroupBuilder $filterGroupBuilder
     * @param CityRepository $cityRepository
     * @param \Aventi\CityDropDown\Model\ResourceModel\City\CollectionFactory $cityCollectionFactory
     */
    public function __construct(
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Framework\Api\SortOrder $sortOrder,
        \Magento\Framework\Api\Search\FilterGroupBuilder $filterGroupBuilder,
        \Aventi\CityDropDown\Model\CityRepository $cityRepository,
        \Aventi\CityDropDown\Model\ResourceModel\City\CollectionFactory $cityCollectionFactory
    ) {
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_filterBuilder = $filterBuilder;
        $this->_filterGroupBuilder = $filterGroupBuilder;
        $this->_sortOrder = $sortOrder;
        $this->_cityRepository = $cityRepository;
        $this->_cityCollectionFactory = $cityCollectionFactory;
    }

    /**
     *
     *
     * @param array $conditions
     * @return array
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function filterByFields(array $conditions): array
    {
        $items = [];
        $collection = $this->_cityCollectionFactory->create();
        foreach ($conditions['fields'] as $key => $value) {
            $collection->addFieldToFilter($key, [$conditions['condition_type'] => $value]);
        }
        $collection->setOrder($conditions['field_order'], $conditions['direction']);
        $completeItems = $collection->getItems();
        if (count($completeItems) > 0) {
            foreach ($completeItems as $city){
                $items[] =  [
                    'name' => $city->getName(),
                    'id' => $city->getCityId(),
                    'postalCode' => $city->getPostcode()
                ];
            }
        }
        return $items;
    }
}
