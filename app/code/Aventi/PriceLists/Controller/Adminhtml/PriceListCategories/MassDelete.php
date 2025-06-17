<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\PriceLists\Controller\Adminhtml\PriceListCategories;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Aventi\PriceLists\Model\ResourceModel\PriceListCategory\CollectionFactory;

class MassDelete extends Action
{
    /**
     * @var Filter
     */
    private Filter $filter;

    /**
     * @var CollectionFactory
     */
    private CollectionFactory $collectionFactory;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;

        parent::__construct($context);
    }

    /**
     * Execute action.
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     *
     * @throws \Magento\Framework\Exception\LocalizedException|\Exception
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();

        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());

            $done = 0;
            foreach ($collection as $item) {
                $item->delete();
                ++$done;
            }

            if ($done) {
                $this->messageManager->addSuccess(__('A total of %1 record(s) were modified.', $done));
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }

        return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
    }

    /**
     * @return bool
     */
    protected function _isAllowed()
    {
        return true;//$this->_authorization->isAllowed('{Namespace}_{Module}::mass_{action}');
    }
}
