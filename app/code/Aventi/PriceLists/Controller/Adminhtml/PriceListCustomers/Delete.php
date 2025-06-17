<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\PriceLists\Controller\Adminhtml\PriceListCustomers;

use Aventi\PriceLists\Api\Data\PriceListCustomersInterface;

class Delete extends \Aventi\PriceLists\Controller\Adminhtml\PriceListCustomers
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
        $parentId = $this->getRequest()->getParam('parent_id');
        $id = $this->getRequest()->getParam(PriceListCustomersInterface::PRICE_LIST_ENTITY_ID);
        if ($id) {
            try {
                // init model and delete
                $model = $this->_objectManager->create(\Aventi\PriceLists\Model\PriceListCustomers::class);
                $model->load($id);
                $model->delete();
                // display success message
                $this->messageManager->addSuccessMessage(__('You deleted the Pricelist customers.'));
                // go to grid
                return $resultRedirect->setPath(
                    '*/pricelist/edit',
                    [
                        'entity_id' => $parentId,
                        '_current' => true,
                        'active_tab' => 'customers'
                    ]
                );
            } catch (\Exception $e) {
                // display error message
                $this->messageManager->addErrorMessage($e->getMessage());
                // go back to edit form
                return $resultRedirect->setPath(
                    '*/pricelist/edit',
                    [
                        'id' => $parentId,
                        '_current' => true,
                        'active_tab' => 'customers'
                    ]
                );
            }
        }
        // display error message
        $this->messageManager->addErrorMessage(__('We can\'t find a Pricelist customers to delete.'));
        // go to grid
        return $resultRedirect->setPath('*/pricelist/index');
    }
}

