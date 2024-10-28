<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\Checkout\Rewrite\Bancolombia\Wompi\Controller\Response;

use Bancolombia\Wompi\Controller\Response\Result as Source;
use Exception;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;

/**
 * Class result
 */
class Result extends Source
{
    /**
     * Constructor
     *
     * @param Context $context
     * @param Session $checkoutSession
     * @param ConfigInterface $config
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Context                                   $context,
        private readonly Session                  $checkoutSession,
        private readonly ConfigInterface          $config,
        private readonly OrderRepositoryInterface $orderRepository,
    ) {
        parent::__construct($context, $checkoutSession, $config);
    }

    /**
     * Execute
     *
     * @return ResponseInterface|Redirect|ResultInterface|void
     * @throws Exception
     */
    public function execute()
    {
        $order = $this->checkoutSession->getLastRealOrder();
        $id = filter_input(INPUT_GET, 'id');

        $resultRedirect = $this->resultRedirectFactory->create();
        $resultRedirect->setPath('checkout/onepage/failure');

        if (!empty($id)) {
            if ($this->config->getValue('test_mode')==='1') {
                $wompi="https://sandbox.wompi.co/v1/transactions";
            } else {
                $wompi="https://production.wompi.co/v1/transactions";
            }
            $getJson = file_get_contents($wompi . "/" . $id);
            $json = json_decode($getJson);
            $transaction_status = $json->data->status;
            if ($transaction_status == "APPROVED" && isset($json->data->id)) {
                $order->setData('state', Order::STATE_PROCESSING);
                $order->setData('status', Order::STATE_PROCESSING);
                $reference = explode("-", $json->data->id);
                $order->getPayment()->setAdditionalInformation('reference', end($reference));

                $resultRedirect->setPath('checkout/onepage/success');
            } else {
                $order->setData('state', Order::STATE_CANCELED);
                $order->setData('status', Order::STATE_CANCELED);
                $this->messageManager->addErrorMessage(__('No se pudo proceder con el pago'));
                $this->messageManager->addNoticeMessage(__('Consulte con su banco.'));
            }
            $this->orderRepository->save($order);
        } else {
            $this->messageManager->addErrorMessage(__('No se pudo proceder con el pago'));
        }

        return $resultRedirect;
    }
}
