<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\CityDropDown\Controller\Adminhtml\City;

use Magento\Framework\Exception\LocalizedException;


class Save extends \Magento\Backend\App\Action
{

    /**
     * @var \Magento\Framework\App\Request\DataPersistorInterface
     */
    protected \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor;

    /**
     * @var \Aventi\CityDropDown\Model\CityRepository
     */
    private \Aventi\CityDropDown\Model\CityRepository $_cityRepository;

    /**
     * @var \Aventi\CityDropDown\Api\Data\CityInterfaceFactory
     */
    private \Aventi\CityDropDown\Api\Data\CityInterfaceFactory $_cityInterfaceFactory;


    protected \Psr\Log\LoggerInterface $_logger;


    /**
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     * @param \Aventi\CityDropDown\Model\CityRepository $cityRepository
     * @param \Aventi\CityDropDown\Api\Data\CityInterfaceFactory $cityInterfaceFactory
     */

    public function __construct(
        \Psr\Log\LoggerInterface                              $logger,
        \Magento\Backend\App\Action\Context                   $context,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        \Aventi\CityDropDown\Model\CityRepository             $cityRepository,
        \Aventi\CityDropDown\Api\Data\CityInterfaceFactory    $cityInterfaceFactory

    )
    {
        $this->_logger = $logger;
        $this->dataPersistor = $dataPersistor;
        $this->_cityRepository = $cityRepository;
        $this->_cityInterfaceFactory = $cityInterfaceFactory;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute(): \Magento\Framework\Controller\ResultInterface
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $data = $this->getRequest()->getPostValue();
        if ($data) {
            $id = $this->getRequest()->getParam(\Aventi\CityDropDown\Api\Data\CityInterface::CITY_ID);
            $model = $this->_objectManager->create(\Aventi\CityDropDown\Model\City::class)->load($id);

            if (!$model->getId() && $id) {
                $this->messageManager->addErrorMessage(__('This City no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }

            $getId = $this->getIdCity($data);

            if (!$id && $getId !== -1) {
                $this->messageManager->addErrorMessage(__('There is already city.'));
                return $resultRedirect->setPath('*/*/');
            }

            $model->setData($data);

            try {
                $this->_cityRepository->save($model);
                $this->messageManager->addSuccessMessage(__('You saved the City.'));
                $this->dataPersistor->clear(\Aventi\CityDropDown\Model\City::TABLE);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', [\Aventi\CityDropDown\Api\Data\CityInterface::CITY_ID => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the City.'));
            }

            $this->dataPersistor->set(\Aventi\CityDropDown\Model\City::TABLE, $data);
            return $resultRedirect->setPath('*/*/edit',
                [
                    \Aventi\CityDropDown\Api\Data\CityInterface::CITY_ID => $this->getRequest()->getParam(\Aventi\CityDropDown\Api\Data\CityInterface::CITY_ID)
                ]
            );
        }
        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @param array $data
     * @return int
     */
    private function getIdCity(array $data): int
    {
        $fields = [
            'name' => $data['name'],
            'main_table.region_id' => $data['region_id'],
            'postcode' => $data['postcode']
        ];

        return $this->_cityRepository->getIdByFields($fields);
    }
}

