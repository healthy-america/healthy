<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAPHistoryRequest\Observer\Sap;

use Aventi\SAPHistoryRequest\Api\Data\OrderHistoryInterface;
use Magento\Framework\Exception\LocalizedException;

class OrderAfterRequest implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var \Aventi\SAPHistoryRequest\Api\OrderHistoryRepositoryInterface
     */
    private \Aventi\SAPHistoryRequest\Api\OrderHistoryRepositoryInterface $_orderHistoryRepository;

    /**
     * @var \Aventi\SAPHistoryRequest\Model\OrderHistoryFactory
     */
    private \Aventi\SAPHistoryRequest\Model\OrderHistoryFactory $_orderHistoryFactory;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    private \Magento\Framework\Api\FilterBuilder $_filterBuilder;

    /**
     * @var \Magento\Framework\Api\Search\FilterGroupBuilder
     */
    private \Magento\Framework\Api\Search\FilterGroupBuilder $_filterGroupBuilder;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private \Magento\Framework\Api\SearchCriteriaBuilder $_searchCriteriaBuilder;

    /**
     * @var \Aventi\SAPHistoryRequest\Helper\Config
     */
    private \Aventi\SAPHistoryRequest\Helper\Config $config;

    /**
     * @param \Aventi\SAPHistoryRequest\Api\OrderHistoryRepositoryInterface $orderHistoryRepository
     * @param \Aventi\SAPHistoryRequest\Model\OrderHistoryFactory $orderHistoryFactory
     * @param \Magento\Framework\Api\FilterBuilder $filterBuilder
     * @param \Magento\Framework\Api\Search\FilterGroupBuilder $filterGroupBuilder
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Aventi\SAPHistoryRequest\Helper\Config $config
     */
    public function __construct(
        \Aventi\SAPHistoryRequest\Api\OrderHistoryRepositoryInterface $orderHistoryRepository,
        \Aventi\SAPHistoryRequest\Model\OrderHistoryFactory $orderHistoryFactory,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Framework\Api\Search\FilterGroupBuilder $filterGroupBuilder,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Aventi\SAPHistoryRequest\Helper\Config $config
    ) {
        $this->_orderHistoryRepository = $orderHistoryRepository;
        $this->_orderHistoryFactory = $orderHistoryFactory;
        $this->_filterBuilder = $filterBuilder;
        $this->_filterGroupBuilder = $filterGroupBuilder;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->config = $config;
    }

    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        try {
            if (!$this->config->isActiveOrderHistory()) {
                return;
            }
            date_default_timezone_set('America/Bogota');
            $order = $observer->getOrder();
            $response = $observer->getResponse() ?? [];
            $parentId = $order->getEntityId();
            $filter = $this->_filterBuilder
                ->setField(OrderHistoryInterface::PARENT_ID)
                ->setConditionType("eq")
                ->setValue($parentId)->create();
            $filterGroup[] =  $this->_filterGroupBuilder->addFilter($filter)->create();
            $searchCriteria = $this->_searchCriteriaBuilder->setFilterGroups($filterGroup)->create();
            $result = $this->_orderHistoryRepository->getList($searchCriteria);
            if ($result->getTotalCount() > 0) {
                $this->updateFirstElement($result->getItems(), $response);
            } else {
                $responses = [];
                $date  = date("Y-m-d H:i:s");
                $history = $this->_orderHistoryFactory->create();
                $history->setParentId($parentId);
                $responses[] = $response;
                $history->setJsonResponse(json_encode($responses));
                $history->setCreatedAt($date);
                $history->setUpdatedAt($date);
                $this->_orderHistoryRepository->save($history);
            }
        } catch (\Exception $e) {
            // error save
        }
    }

    /**
     * @param $items
     * @param $response
     * @return void
     * @throws LocalizedException
     */
    private function updateFirstElement($items, $response): void
    {
        /** @var \Aventi\SAPHistoryRequest\Api\Data\OrderHistoryInterface $item */
        foreach ($items as $item) {
            $date  = date("Y-m-d H:i:s");
            $responses = json_decode($item->getJsonResponse() ?? '', true);
            if (!$responses || !is_array($responses)) {
                $responses = [];
            }
            if (count($responses) >= 10) {
                array_shift($responses);
            }
            $responses[] = $response;
            $item->setJsonResponse(json_encode($responses));
            $item->setUpdatedAt($date);
            $this->_orderHistoryRepository->save($item);
            break;
        }
    }
}
