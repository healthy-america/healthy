<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\CityDropDown\Controller\Adminhtml\City;

class Delete extends \Aventi\CityDropDown\Controller\Adminhtml\City
{

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute(): \Magento\Framework\Controller\ResultInterface
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam(\Aventi\CityDropDown\Api\Data\CityInterface::CITY_ID);
        if ($id) {
            try {
                // init model and delete
                $model = $this->_objectManager->create(\Aventi\CityDropDown\Model\City::class);
                $model->load($id);
                $model->delete();
                // display success message
                $this->messageManager->addSuccessMessage(__('You deleted the City.'));
                // go to grid
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', [\Aventi\CityDropDown\Api\Data\CityInterface::CITY_ID => $id]);
            }
        }
        // display error message
        $this->messageManager->addErrorMessage(__('We can\'t find a City to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}

