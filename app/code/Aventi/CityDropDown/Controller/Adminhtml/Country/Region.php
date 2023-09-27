<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\CityDropDown\Controller\Adminhtml\Country;

class Region extends \Magento\Backend\App\Action
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
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $jsonFactory,
        \Aventi\CityDropDown\Model\Region $region
    ) {
        $this->_jsonFactory = $jsonFactory;
        $this->_region = $region;
        parent::__construct($context);
    }

    /**
     * Index action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute(): \Magento\Framework\Controller\ResultInterface
    {
        $resultJson = $this->_jsonFactory->create();
        $response = [
            'country_id' => '',
            'code' => 400
        ];

        /*if(!$this->getRequest()->isAjax()){
            return $resultJson->setData($response);
        }*/


        $regionId = $this->getRequest()->getParam('region_id');
        $region = $this->_region->getRegion($regionId);
        if ($region) {
            $response['country_id'] = $region->getCountryId();
            $response['code'] = '202';
        }

        return $resultJson->setData($response);
    }
}
