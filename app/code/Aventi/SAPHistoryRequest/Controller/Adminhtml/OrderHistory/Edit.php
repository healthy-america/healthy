<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAPHistoryRequest\Controller\Adminhtml\OrderHistory;

class Edit extends \Aventi\SAPHistoryRequest\Controller\Adminhtml\OrderHistory
{

    protected $resultPageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Edit action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam('orderhistory_id');
        $model = $this->_objectManager->create(\Aventi\SAPHistoryRequest\Model\OrderHistory::class);
        
        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This Orderhistory no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }
        $this->_coreRegistry->register('aventi_saphistoryrequest_orderhistory', $model);
        
        // 3. Build edit form
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->addBreadcrumb(
            $id ? __('Edit Orderhistory') : __('New Orderhistory'),
            $id ? __('Edit Orderhistory') : __('New Orderhistory')
        );
        $resultPage->getConfig()->getTitle()->prepend(__('Orderhistorys'));
        $resultPage->getConfig()->getTitle()->prepend($model->getId() ? __('Edit Orderhistory %1', $model->getId()) : __('New Orderhistory'));
        return $resultPage;
    }
}

