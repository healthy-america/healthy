<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Model\Integration;

use Aventi\SAP\Helper\Data;
use Magento\Sales\Api\Data\OrderInterface;

class Order extends \Aventi\SAP\Model\Integration
{
    const TYPE_URI = 'order';
    const ORDER_STATUS_ERROR = 'error';

    private array $arrStatusOrders = [
        'headers' => ['Total orders', 'Total error', 'Total completed'],
        'rows' => ['total' => 0, 'error' => 0, 'success' => 0]
    ];

    /**
     * @var \Magento\Sales\Api\OrderRepositoryInterface
     */
    private \Magento\Sales\Api\OrderRepositoryInterface $_orderRepository;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    private \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $_orderCollectionFactory;

    /**
     * @var \Aventi\SAP\Helper\Order
     */
    private \Aventi\SAP\Helper\Order $_helperOrder;

    /**
     * @var Data
     */
    private Data $_data;

    /**
     * @var \Aventi\SAP\Logger\Logger
     */
    private \Aventi\SAP\Logger\Logger $_logger;

    public function __construct(
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Aventi\SAP\Helper\Order $order,
        \Aventi\SAP\Helper\Data $data,
        \Aventi\SAP\Logger\Logger $logger
    ) {
        $this->_orderRepository = $orderRepository;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_helperOrder = $order;
        $this->_data = $data;
        $this->_logger = $logger;
    }

    /**
     * @param $status
     * @return void
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
         * @var $orderInfo \Magento\Sales\Model\Order
         */
        foreach ($orders as $orderInfo) {
            $order = $this->_orderRepository->get($orderInfo->getId());
            $this->_helperOrder->createInvoice($order);
            if ($order->getState() == 'processing') {
                $this->_logger->debug("Entra aca");
                $resProcess = $this->_helperOrder->processIteration(['syncing', 'error'], $order->getId());
                if (!$resProcess) {
                    continue;
                }

                $orderData = $this->_helperOrder->processDataSAP($order);
                $this->_logger->debug(json_encode($orderData));
                $response = $this->_data->postResource(self::TYPE_URI, $orderData);
                $this->_logger->debug(json_encode($response));
                $this->processResponseOrder($order, $response);
            }
        }

        $this->printOrderTable($this->arrStatusOrders);
    }

    /**
     * @param OrderInterface $order
     * @param $response
     * @return void
     */
    private function processResponseOrder(OrderInterface $order, $response)
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
     * @param $body
     * @return mixed|null
     */
    private function validateIdSAP($body)
    {
        $response = json_decode($body, true);

        return $response['DocNum'] ?? null;
    }

    /**
     * @param $body
     * @return array|string|string[]
     */
    private function getErrorDesc($body)
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
