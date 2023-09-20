<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\CityDropDown\Controller\Index;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

class Index implements \Magento\Framework\App\ActionInterface
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $_jsonFactory;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    private \Magento\Framework\App\Request\Http $_request;

    /**
     * @var \Aventi\CityDropDown\Model\CityFilter
     */
    private \Aventi\CityDropDown\Model\CityFilter $_cityFilter;


    /**
     * @param ResultFactory $jsonFactory
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Aventi\CityDropDown\Model\CityFilter $cityFilter
     */
    public function __construct(
        \Magento\Framework\Controller\ResultFactory $jsonFactory,
        \Magento\Framework\App\Request\Http $request,
        \Aventi\CityDropDown\Model\CityFilter $cityFilter
    ) {
        $this->_jsonFactory = $jsonFactory;
        $this->_request = $request;
        $this->_cityFilter = $cityFilter;
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        try {
            $params = $this->_request->getParams();

            $region = $params['region_id'];

            $items =  $this->_cityFilter->filterByFields([
                'fields' =>  [
                    'main_table.region_id' => $region,
                    'available' => '1'
                ],
                'condition_type' => 'eq',
                'field_order' => 'name',
                'direction' => 'ASC'
            ]);

            usort($items, function ($a, $b) {
                return $a['name'] <=> $b['name'];
            });

            return $this->jsonResponse($items);
        } catch (\Magento\Framework\Exception\LocalizedException|\Exception $e) {
            return $this->jsonResponse($e->getMessage());
        }
    }

    /**
     * Create json response
     *
     * @param $response
     * @return ResultInterface
     */
    public function jsonResponse($response): \Magento\Framework\Controller\ResultInterface
    {
        $resultJson = $this->_jsonFactory->create(ResultFactory::TYPE_JSON);
        return  $resultJson->setData($response);
    }
}
