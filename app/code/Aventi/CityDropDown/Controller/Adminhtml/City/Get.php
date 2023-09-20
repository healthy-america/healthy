<?php

namespace Aventi\CityDropDown\Controller\Adminhtml\City;

use Magento\Framework\Controller\ResultFactory;

class Get extends \Magento\Backend\App\Action
{
    /**
     * @var \Magento\Framework\Controller\ResultFactory
     */
    private \Magento\Framework\Controller\ResultFactory $_jsonFactory;

    /**
     * @var \Aventi\CityDropDown\Model\CityFilter
     */
    private \Aventi\CityDropDown\Model\CityFilter $_cityFilter;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    private \Magento\Customer\Api\AddressRepositoryInterface $_addressRepositoryInterface;


    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param ResultFactory $jsonFactory
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Aventi\CityDropDown\Model\CityFilter $cityFilter
     * @param \Magento\Customer\Api\AddressRepositoryInterface $addressRepositoryInterface
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\ResultFactory $jsonFactory,
        \Magento\Framework\App\Request\Http $request,
        \Aventi\CityDropDown\Model\CityFilter $cityFilter,
        \Magento\Customer\Api\AddressRepositoryInterface $addressRepositoryInterface
    ) {
        $this->_jsonFactory = $jsonFactory;
        $this->_request = $request;
        $this->_cityFilter = $cityFilter;
        $this->_addressRepositoryInterface = $addressRepositoryInterface;
        parent::__construct($context);
    }


    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute(): \Magento\Framework\Controller\ResultInterface
    {
        $response = [];
        $address = null;
        try {
            if(!$this->getRequest()->isAjax()){
                return $this->jsonResponse(['error' => 'Not authorized']);
            }

            $params = $this->_request->getParams();

            $region = $params['region_id'];

            if (array_key_exists('address_id', $params)) {
                $address = $this->getAddressData($params['address_id']);
                $region = $address['region_id'];
            }

            $items =  $this->_cityFilter->filterByFields([
                'fields' =>  [
                    'main_table.region_id' => $region,
                    'available' => '1'
                ],
                'condition_type' => 'eq',
                'field_order' => 'name',
                'direction' => 'ASC'
            ]);

            usort($items, function($a, $b) {
                return $a['name'] <=> $b['name'];
            });

            if ($address) {
                $response = [
                    'cities' => $items,
                    'address' => $address
                ];
            } else {
                $response = $items;
            }

            return $this->jsonResponse($response);
        } catch (\Magento\Framework\Exception\LocalizedException|\Exception $e) {
            return $this->jsonResponse(['error' => $e->getMessage()]);
        }
    }


    /**
     * @param $response
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function jsonResponse($response): \Magento\Framework\Controller\ResultInterface
    {
        $resultJson = $this->_jsonFactory->create(ResultFactory::TYPE_JSON);
        return  $resultJson->setData($response);
    }

    /**
     * @param string $addressId
     * @return array
     */
    public function getAddressData(string $addressId): array
    {
        try {
            $addressData = $this->_addressRepositoryInterface->getById($addressId);
            return [
                'id' => $addressData->getId(),
                'firstname' => $addressData->getFirstname(),
                'lastname' => $addressData->getLastname(),
                'country_id' => $addressData->getCountryId(),
                'region_id' => $addressData->getRegionId(),
                'city' => $addressData->getCity(),
                'postcode' => $addressData->getPostcode()
            ];
        } catch (\Exception $exception) {
            return ['id' => -1];
        }
    }
}
