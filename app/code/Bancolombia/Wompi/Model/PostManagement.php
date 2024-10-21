<?php
namespace Bancolombia\Wompi\Model;
use Bancolombia\Wompi\Api\PostManagementInterface;

//Obtener y cambiar status

use Magento\Checkout\Model\Session;
use Magento\Sales\Model\Order;
use Magento\Framework\App\Action\Action;
use Magento\Payment\Gateway\ConfigInterface;

//********************************

class PostManagement implements PostManagementInterface
{
    protected $request;

    protected $order;

    private $checkoutSession;
    private $config;

    public function __construct(
        Session $checkoutSession,
        ConfigInterface $config,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Magento\Framework\Webapi\Rest\Request $request)
    {
        $this->checkoutSession = $checkoutSession;
        $this->order = $order;
        $this->request = $request;
        $this->config = $config;
    }

    public function customPostMethod()
    {
        $bodyParams['event'] = $this->request->getBodyParams(); // It will return all params which will pass from body of postman.
        $referencia = intval($bodyParams['event']['data']['transaction']['reference']) - 20211021;
        $status = $bodyParams['event']['data']['transaction']['status'];

        $t_id = $bodyParams['event']['data']['transaction']['id'];
        $t_amount = $bodyParams['event']['data']['transaction']['amount_in_cents'];
        $t_time = $bodyParams['event']['timestamp'];

        if($this->config->getValue('test_mode')==='1') {
            $event_key = $this->config->getValue('wompi_event_key_test');
        } else {
            $event_key = $this->config->getValue('wompi_event_key');
        }

        $check_s = $bodyParams['event']['signature']['checksum'];

        $conca = $t_id . $status . $t_amount . $t_time . $event_key;

        $secreto = hash ("sha256", $conca);

        if($status == "APPROVED")
        {
            $changeBy = Order::STATE_PROCESSING;
        }
        elseif ($status == "DECLINED" || $status == "ERROR") {
            $changeBy = Order::STATE_CANCELED;
        }
        elseif ($status == "VOIDED") {
            $changeBy = Order::STATE_PENDING_PAYMENT;
        }

        $order = $this->order->loadByIncrementId((string)$referencia);

        if(hash_equals($secreto, $check_s))
        {
            $order->setData('state', $changeBy)->save();
            $order->setData('status', $changeBy)->save();
        }else
        {
            $order->setData('state', Order::STATE_PAYMENT_REVIEW)->save();
            $order->setData('status', Order::STATE_PAYMENT_REVIEW)->save();
        }

        return $referencia;
    }
}
