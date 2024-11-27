<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Model\Integration;

use Aventi\SAP\Helper\Data;
use Aventi\SAP\Helper\Order as OrderHelper;
use Aventi\SAP\Model\Integration;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order as OrderModel;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use PHPUnit\Exception;

/**
 * @class Order
 */
class Order extends Integration
{
    const TYPE_URI = 'order';
    const ORDER_STATUS_ERROR = 'error';

    /**
     * @var array
     */
    private array $arrStatusOrders = [
        'headers' => ['Total orders', 'Total error', 'Total completed'],
        'rows' => ['total' => 0, 'error' => 0, 'success' => 0]
    ];

    /**
     * @constructor
     *
     * @param OrderRepositoryInterface $_orderRepository
     * @param CollectionFactory $_orderCollectionFactory
     * @param OrderHelper $_helperOrder
     * @param Data $_data
     * @param ManagerInterface $eventManager
     */
    public function __construct(
        private readonly OrderRepositoryInterface $_orderRepository,
        private readonly CollectionFactory        $_orderCollectionFactory,
        private readonly OrderHelper              $_helperOrder,
        private readonly Data                     $_data,
        private readonly ManagerInterface         $eventManager
    ) {
    }

    /**
     * Process
     *
     * @param $status
     * @return void
     * @throws CouldNotDeleteException
     * @throws LocalizedException
     */
    public function process($status = null) : void
    {
        $orders = $this->_orderCollectionFactory->create()
            ->addAttributeToFilter('status', $status)
            ->addAttributeToFilter('sap_id', ['null' => true])
            ->setOrder('created_at', 'ASC')
            ->getItems();

        $this->arrStatusOrders['rows']['total'] = count($orders);

        /**
         * @var $orderInfo OrderModel
         */
        foreach ($orders as $orderInfo) {
            $order = $this->_orderRepository->get($orderInfo->getId());
            $this->_helperOrder->createInvoice($order);

            if ($order->getState() == 'processing') {
                $resProcess = $this->_helperOrder->processIteration(['syncing', 'error'], $order->getId());
                if (!$resProcess) {
                    continue;
                }

                try {
                    $orderData = $this->_helperOrder->processDataSAP($order);
                    $response = $this->request($order, $orderData);
                } catch (NoSuchEntityException | LocalizedException | Exception $e) {
                    $response = ['status' => 0, 'body' => $e->getMessage()];
                }
                $this->processResponseOrder($order, $response);
            }
        }

        $this->printOrderTable($this->arrStatusOrders);
    }

    /**
     * Request
     *
     * @param OrderInterface $order
     * @param array $payload
     * @return bool|array
     */
    public function request(OrderInterface $order, array $payload) : bool|array
    {
        $this->eventManager->dispatch(
            'sap_order_before_request',
            [
                'order' => $order,
                'body' => $payload
            ]
        );
        $response = $this->_data->postResource(self::TYPE_URI, $payload);

        $this->eventManager->dispatch(
            'sap_order_after_request',
            [
                'order' => $order,
                'response' => $response
            ]
        );

        return $response;
    }

    /**
     * ProcessResponseOrder
     *
     * @param OrderInterface $order
     * @param $response
     * @return void
     */
    private function processResponseOrder(OrderInterface $order, $response): void
    {
        $idSAP = $this->validateIdSAP($response['body']);

        switch ($response['status']) {
            case 200:
                $order->addStatusToHistory(
                    \Magento\Sales\Model\Order::STATE_PROCESSING,
                    sprintf(
                        'El pedido <strong>%s</strong> fue ingresado en SAP con ID #<strong>%s</strong>.',
                        $order->getIncrementId(),
                        $idSAP
                    )
                );
                $order->setData('sap_id', $idSAP);
                $this->arrStatusOrders['rows']['success']++;
                break;
            case 100:
                if (is_numeric($idSAP)) {
                    $order->setData('sap_id', $idSAP);
                    $order->addStatusToHistory(
                        \Magento\Sales\Model\Order::STATE_PROCESSING,
                        sprintf(
                            'El pedido <strong>%s</strong> fue ingresado en SAP con ID #<strong>%s</strong>.',
                            $order->getIncrementId(),
                            $idSAP
                        )
                    );
                    $this->arrStatusOrders['rows']['success']++;
                } else {
                    $error = $this->getErrorDesc($response['body']);
                    $order->addStatusToHistory(
                        self::ORDER_STATUS_ERROR,
                        sprintf('<strong>Error de creación</strong><br>%s', $error)
                    );
                    $this->arrStatusOrders['rows']['error']++;
                }
                break;
            default:
                $error = $this->getErrorDesc($response['body']);
                $order->addStatusToHistory(
                    self::ORDER_STATUS_ERROR,
                    sprintf('<strong>Error de creación</strong><br>%s', $error)
                );
                $this->arrStatusOrders['rows']['error']++;
                break;
        }

        $this->_orderRepository->save($order);
    }

    /**
     * ValidateIdSAP
     *
     * @param $body
     * @return mixed|null
     */
    private function validateIdSAP($body): mixed
    {
        $response = json_decode($body, true);

        return $response['DocNum'] ?? null;
    }

    /**
     * GetErrorDesc
     *
     * @param $body
     * @return array|string|string[]
     */
    private function getErrorDesc($body): array|string
    {
        $re = '/(ErrorDesc)\s{0,3}->\s{0,3}?(.{1,}")?/m';
        $description = $body;
        preg_match_all($re, $body, $matches, PREG_SET_ORDER, 0);
        if (is_array($matches) && !empty($matches)) {
            $description = str_replace(['ErrorDesc', '->', '-', '"'], '', $matches[0][0]);
        }

        return $description;
    }
}
