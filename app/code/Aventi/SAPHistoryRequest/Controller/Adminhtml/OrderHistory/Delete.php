<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAPHistoryRequest\Controller\Adminhtml\OrderHistory;

class Delete extends \Aventi\SAPHistoryRequest\Controller\Adminhtml\OrderHistory
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
        $id = $this->getRequest()->getParam('orderhistory_id');
        if ($id) {
            try {
                // init model and delete
                $model = $this->_objectManager->create(\Aventi\SAPHistoryRequest\Model\OrderHistory::class);
                $model->load($id);
                $model->delete();
                // display success message
                $this->messageManager->addSuccessMessage(__('You deleted the Orderhistory.'));
                // go to grid
                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath('*/*/edit', ['orderhistory_id' => $id]);
            }
        }
        // display error message
        $this->messageManager->addErrorMessage(__('We can\'t find a Orderhistory to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/*/');
    }
}

