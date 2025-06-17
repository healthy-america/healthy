<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\PriceLists\Controller\Adminhtml\PriceListCustomers;

use Aventi\PriceLists\Api\Data\PriceListCustomersInterface;
use Aventi\PriceLists\Api\Data\PriceListCustomersInterfaceFactory;
use Aventi\PriceLists\Api\PriceListCustomersRepositoryInterface;
use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class Save extends \Magento\Backend\App\Action
{
    protected $dataPersistor;

    /**
     * @var PriceListCustomersRepositoryInterface
     */
    private PriceListCustomersRepositoryInterface $priceListCustomersRepository;

    /**
     * @var PriceListCustomersInterfaceFactory
     */
    private PriceListCustomersInterfaceFactory $PriceListCustomersFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var JsonFactory
     */
    private JsonFactory $jsonFactory;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * Construct
     *
     * @param Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param PriceListCustomersRepositoryInterface $priceListCustomersRepository
     * @param PriceListCustomersInterfaceFactory $PriceListCustomersFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param JsonFactory $jsonFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        PriceListCustomersRepositoryInterface $priceListCustomersRepository,
        PriceListCustomersInterfaceFactory $PriceListCustomersFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        JsonFactory $jsonFactory,
        LoggerInterface $logger
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->priceListCustomersRepository = $priceListCustomersRepository;
        $this->PriceListCustomersFactory = $PriceListCustomersFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->jsonFactory = $jsonFactory;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();

        $messages = [
            'message' => __('Please correct the data sent.'),
            'error' => false
        ];
        $customersError = '';
        $error = false;
        $data = $this->getRequest()->getPostValue();
        if (!$data) {
            return  $resultJson->setData($messages);
        }
        foreach ($data['customer_ids'] as $customerId) {
            try {
                $this->removeCustomerInOtherList($data['price_list_id'], $customerId);
                $customerRegister = $this->getRowData($data['price_list_id'], $customerId);
                if (!$customerRegister) {
                    $customerRegister = $this->PriceListCustomersFactory->create();
                    $customerRegister->setPriceListId($data['price_list_id']);
                    $customerRegister->setCustomerId($customerId);
                    $this->priceListCustomersRepository->save($customerRegister);
                }
            } catch (\Exception $e) {
                $error = true;
                $customersError .= $customerId . ', ';
            }
        }
        if (!$error) {
            $this->dataPersistor->clear(\Aventi\PriceLists\Model\PriceListCustomers::TABLE);
            $messages['messages'] = __('You saved the customer in price list.');
        } else {
            $this->dataPersistor->set(\Aventi\PriceLists\Model\PriceListCustomers::TABLE, $data);
            $messages['messages'] = __('Customers with error : ' . rtrim($customersError, ", "));
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     * @param $priceListId
     * @param $customerId
     * @return false|mixed|null
     * @throws LocalizedException
     */
    private function getRowData($priceListId, $customerId): mixed
    {
        $row = null;
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(PriceListCustomersInterface::PRICE_LIST_ID, $priceListId)
            ->addFilter(PriceListCustomersInterface::PRICE_LIST_CUSTOMER_ID, $customerId)
            ->create();
        $searchResults = $this->priceListCustomersRepository->getList($searchCriteria);
        if ($searchResults->getTotalCount() > 0) {
            $items = $searchResults->getItems();
            $row = reset($items);
        }

        return $row;
    }

    /**
     * @param $listId
     * @param $customerId
     * @return void
     */
    public function removeCustomerInOtherList($listId, $customerId): void
    {
        try {
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter(PriceListCustomersInterface::PRICE_LIST_CUSTOMER_ID, $customerId)
                ->create();
            $list = $this->priceListCustomersRepository->getList($searchCriteria);
            if ($list->getTotalCount() > 0) {
                foreach ($list->getItems() as $item) {
                    if ($item->getPriceListId() === $listId) {
                        continue;
                    }
                    $this->priceListCustomersRepository->delete($item);
                }
            }
        } catch (Exception $e) {
            $this->logger->debug('PriceList removeCustomerInOtherList method: ' . $e->getMessage());
        }
    }
}
