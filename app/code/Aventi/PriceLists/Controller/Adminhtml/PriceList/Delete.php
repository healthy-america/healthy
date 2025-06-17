<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);


namespace Aventi\PriceLists\Controller\Adminhtml\PriceList;

use Aventi\PriceLists\Api\Data\PriceListInterface;

class Delete extends \Aventi\PriceLists\Controller\Adminhtml\PriceList
{

    /**
     * Delete action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        // check if we know what should be deleted
        $id = $this->getRequest()->getParam(PriceListInterface::ENTITY_ID);
        if ($id) {
            try {
                // init model and delete
                $model = $this->_objectManager->create(\Aventi\PriceLists\Model\PriceList::class);
                $model->load($id);
                $model->delete();
                // display success message
                $this->messageManager->addSuccessMessage(__('You deleted the Pricelist.'));
                // go to grid
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', [PriceListInterface::ENTITY_ID => $id]);
            }
        }
        // display error message
        $this->messageManager->addErrorMessage(__('We can\'t find a Pricelist to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}

