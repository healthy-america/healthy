<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAPHistoryRequest\Observer\Sap;

use Aventi\SAPHistoryRequest\Api\Data\OrderHistoryInterface;
use Magento\Framework\Exception\LocalizedException;

class OrderBeforeRequest implements \Magento\Framework\Event\ObserverInterface
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
            $body = $observer->getBody() ?? [];
            $parentId = $order->getEntityId();
            $filter = $this->_filterBuilder
                ->setField(OrderHistoryInterface::PARENT_ID)
                ->setConditionType("eq")
                ->setValue($parentId)
                ->create();
            $filterGroup[] =  $this->_filterGroupBuilder->addFilter($filter)->create();
            $searchCriteria = $this->_searchCriteriaBuilder->setFilterGroups($filterGroup)->create();
            $result = $this->_orderHistoryRepository->getList($searchCriteria);
            if ($result->getTotalCount() > 0) {
                $this->updateFirstElement($result->getItems(), $body);
            } else {
                $date  = date("Y-m-d H:i:s");
                $incrementId = $order->getIncrementId();
                $history = $this->_orderHistoryFactory->create();
                $history->setIncrementId($incrementId);
                $history->setParentId($parentId);
                $history->setJsonBody(json_encode($body));
                $history->setJsonResponse(json_encode([]));
                $history->setCreatedAt($date);
                $history->setUpdatedAt($date);
                $this->_orderHistoryRepository->save($history);
            }
        } catch (\Exception $e) {
            // errors
        }
    }

    /**
     * @param $items
     * @param $body
     * @return void
     * @throws LocalizedException
     */
    private function updateFirstElement($items, $body): void
    {
        /** @var \Aventi\SAPHistoryRequest\Api\Data\OrderHistoryInterface $item */
        foreach ($items as $item) {
            $date  = date("Y-m-d H:i:s");
            $item->setJsonBody(json_encode($body));
            $item->setUpdatedAt($date);
            $this->_orderHistoryRepository->save($item);
            break;
        }
    }
}
