<?php

namespace Bancolombia\Wompi\Controller\Response;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Payment\Gateway\ConfigInterface;
class Result extends Action
{
    private $checkoutSession;
    private $config;

    public function __construct(
        Context $context,
        Session $checkoutSession,
        ConfigInterface $config
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->config = $config;

        parent::__construct($context);
    }

    public function execute()
    {
        $order = $this->checkoutSession->getLastRealOrder();
        $idget = filter_input(INPUT_GET, 'id');
        $id = isset($idget) ? $idget : '';
        $shippingAddress = $order->getShippingAddress();

        if($id)
        {
            if($this->config->getValue('test_mode')==='1' )
            {
                $wompi="https://sandbox.wompi.co/v1/transactions";
            }else{
                $wompi="https://production.wompi.co/v1/transactions";
            }
            $getJson = file_get_contents( $wompi . "/".$id);
            $json = json_decode($getJson);
            $transaction_status = $json->data->status;
            if($transaction_status == "APPROVED")
            {
                $order->setData('state', Order::STATE_PROCESSING)->save();
                $order->setData('status', Order::STATE_PROCESSING)->save();
                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('checkout/onepage/success');
                return $resultRedirect;
            }
            else{
                $order->setData('state', Order::STATE_CANCELED)->save();
                $order->setData('status', Order::STATE_CANCELED)->save();
                $this->messageManager->addError( __('No se pudo proceder con el pago') );
                $this->messageManager->addNotice( __('Consulte con su banco.') );

                $resultRedirect = $this->resultRedirectFactory->create();
                $resultRedirect->setPath('checkout/onepage/failure');
                return $resultRedirect;
            }
        }
    }
}
    ?>
