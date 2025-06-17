<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\PriceLists\Controller\Adminhtml\Customer;

use Magento\Backend\App\Action\Context;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;

class Search extends \Magento\Backend\App\Action
{

    /**
     * @var CollectionFactory
     */
    private CollectionFactory $customerCollection;

    /**
     * @var JsonFactory
     */
    private JsonFactory $jsonResultFactory;

    /**
     * SearchCustomers constructor.
     * @param Context $context
     * @param JsonFactory $jsonResultFactory
     * @param CollectionFactory $customerCollection
     */
    public function __construct(
        Context $context,
        JsonFactory $jsonResultFactory,
        CollectionFactory $customerCollection
    ) {
        $this->jsonResultFactory = $jsonResultFactory;
        $this->customerCollection = $customerCollection;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $searchKey = $this->getRequest()->getParam('searchKey');
        $pageNum = (int)$this->getRequest()->getParam('page');
        $limit = (int)$this->getRequest()->getParam('limit');

        $collection = $this->customerCollection->create()->addNameToSelect();
        if ($searchKey != "") {
            try {
                $collection->addAttributeToFilter('firstname', ['like' => $searchKey . '%']);
            } catch (LocalizedException $e) {
            }
        }
        $collection->setPageSize($limit)
            ->setCurPage($pageNum);

        $collection->getSelect()->group('entity_id');
        $totalValues = $collection->getSize();
        $customerById = [];
        /** @var  CustomerInterface $product */
        foreach ($collection as $customer) {
            $customerId = $customer->getId();
            $customerById[$customerId] = [
                'value' => $customerId,
                'label' => $customer->getName(),
                'is_active' => true,
                'path' => $customerId,
                'optgroup' => false
            ];
        }
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonResultFactory->create();
        return $resultJson->setData([
            'options' => $customerById,
            'total' => empty($customerById) ? 0 : $totalValues
        ]);
    }
}
