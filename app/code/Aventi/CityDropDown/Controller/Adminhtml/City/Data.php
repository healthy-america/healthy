<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\CityDropDown\Controller\Adminhtml\City;

class Data extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private \Magento\Framework\Controller\Result\JsonFactory $_jsonFactory;

    /**
     * @var \Aventi\CityDropDown\Model\Region
     */
    private \Aventi\CityDropDown\Model\Region $_region;

    /**
     * @var \Aventi\CityDropDown\Model\CityRepository
     */
    private \Aventi\CityDropDown\Model\CityRepository $_cityRepository;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Controller\Result\JsonFactory $jsonFactory
     * @param \Aventi\CityDropDown\Model\Region $region
     * @param \Aventi\CityDropDown\Model\CityRepository $cityRepository
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Aventi\CityDropDown\Model\Region $region,
        \Aventi\CityDropDown\Model\CityRepository $cityRepository
    ) {
        $this->_jsonFactory = $jsonFactory;
        $this->_region = $region;
        $this->_cityRepository = $cityRepository;
        parent::__construct($context);
    }

    public function execute(): \Magento\Framework\Controller\ResultInterface
    {
        $resultJson = $this->_jsonFactory->create();
        $response = [
            'city_id' => '',
            'code' => 400
        ];

        $cityId = $this->getRequest()->getParam(\Aventi\CityDropDown\Api\Data\CityInterface::CITY_ID);

        if(!$this->getRequest()->isAjax() || !$cityId){
            return $resultJson->setData($response);
        }

        $city = $this->_cityRepository->get($cityId);

        if ($city) {
            $response['city_id'] = $city->getCityId();
            $response['name'] = $city->getName();
            $response['region_id'] = $city->getRegionId();
            $response['postcode'] = $city->getPostcode();
            $region = $this->_region->getRegion($city->getRegionId());
            $response['country_id'] = $region ? $region->getCountryId() : '';
            $response['code'] = '202';
        }

        return $resultJson->setData($response);
    }
}
