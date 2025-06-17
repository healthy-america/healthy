<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\PriceLists\Controller\Adminhtml\PriceListCategories;

use Aventi\PriceLists\Api\Data\PriceListCategoryInterface;

class Edit extends \Aventi\PriceLists\Controller\Adminhtml\PriceListCategories
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
        $id = $this->getRequest()->getParam(PriceListCategoryInterface::PRICE_LIST_CATEGORY_ENTITY_ID);
        $model = $this->_objectManager->create(\Aventi\PriceLists\Model\PriceListCategory::class);
        // 2. Initial checking
        if ($id) {
            $model->load($id);
            if (!$model->getId()) {
                $this->messageManager->addErrorMessage(__('This Pricelistcategory no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultRedirectFactory->create();
                return $resultRedirect->setPath('*/*/');
            }
        }
        $this->_coreRegistry->register('pricelistcategory', $model);
        // 3. Build edit form
        /** @var \Magento\Backend\Model\View\Result\Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->initPage($resultPage)->addBreadcrumb(
            $id ? __('Edit Price list category') : __('New Price list category'),
            $id ? __('Edit Price list category') : __('New Price list category')
        );
        $resultPage->getConfig()
            ->getTitle()
            ->prepend(__('Price list category'));
        $resultPage->getConfig()
            ->getTitle()
            ->prepend(
                $model->getId() ? __('Edit Pricelistcategory %1', $model->getId())
                    : __('New Pricelistcategory')
            );
        return $resultPage;
    }
}
