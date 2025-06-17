<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\PriceLists\Controller\Adminhtml\PriceList;

use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Aventi\PriceLists\Api\Data\PriceListInterface;

class Edit extends \Aventi\PriceLists\Controller\Adminhtml\PriceList
{
    protected $resultPageFactory;

    /**
     * @param Context $context
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context, $coreRegistry);
    }

    /**
     * Edit action
     *
     * @return ResultInterface
     */
    public function execute()
    {
        // 1. Get ID and create model
        $id = $this->getRequest()->getParam(PriceListInterface::ENTITY_ID);
        $model = $this->_objectManager->create(\Aventi\PriceLists\Model\PriceList::class);

        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This Pricelist no longer exists. 111'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }
        $this->_coreRegistry->register('pricelist', $model);

        // 3. Build edit form
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->addBreadcrumb(
            $id ? __('Edit Pricelist') : __('New Pricelist'),
            $id ? __('Edit Pricelist') : __('New Pricelist')
        );
        $resultPage
            ->getConfig()
            ->getTitle()
            ->prepend(__('Pricelists'));
        $resultPage
            ->getConfig()
            ->getTitle()
            ->prepend($model->getId() ? __('Edit Pricelist %1', $model->getId()) : __('New Pricelist'));
        return $resultPage;
    }
}
