<?php

namespace MagentoSistecredito\SistecreditoPaymentGateway\Controller\Gateway;

use Exception;
use Firebase\JWT\JWT;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Url;
use Magento\Sales\Model\Order;
use MagentoSistecredito\SistecreditoPaymentGateway\Gateway\Config\Config;
use MagentoSistecredito\SistecreditoPaymentGateway\Helper\DbHelper;
use MagentoSistecredito\SistecreditoPaymentGateway\Helper\GatewayActions;
use MagentoSistecredito\SistecreditoPaymentGateway\Helper\SistecreditoOrderLog;
use Magento\Checkout\Model\Session;
use Magento\Sales\Model\OrderFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;
use MagentoSistecredito\SistecreditoPaymentGateway\Helper\GatewayEndpoint;

class GatewayUrl extends Action
{

    /**
     *
     * @var Session
     */
    private Session $_checkoutSession;

    /**
     *
     * @var OrderFactory
     */
    private OrderFactory $_orderFactory;

    /**
     *
     * @var GatewayEndpoint
     */
    private GatewayEndpoint $_gatewayEndpoint;

    /**
     *
     * @var ManagerInterface
     */
    private ManagerInterface $_messageManager;

    /**
     * @var SistecreditoOrderLog $sistecreditoOrderLog
     */
    private SistecreditoOrderLog $sistecreditoOrderLog;

    /**
     * @var DbHelper $_dbHelper
     */
    protected DbHelper $_dbHelper;

    /**
     * @var Json $resultJsonFactory
     */
    protected $resultJsonFactory;

    public function __construct(
        Session         $checkoutSession,
        OrderFactory    $orderFactory,
        GatewayEndpoint $gatewayEndpoint,
        DbHelper        $_dbHelper,
        Context         $context,
        Config          $gatewayConfig,
        Url             $urlInterface,
        JsonFactory     $resultJsonFactory
    )
    {
        parent::__construct($context);
        $this->_checkoutSession = $checkoutSession;
        $this->_orderFactory = $orderFactory;
        $this->_gatewayEndpoint = $gatewayEndpoint;
        $this->_messageManager = $context->getMessageManager();
        $this->_dbHelper = $_dbHelper;
        $this->_gatewayConfig = $gatewayConfig;
        $this->_urlInterface = $urlInterface;
        $this->resultJsonFactory = $resultJsonFactory;
    }

    public function execute()
    {
        $resultJson = $this->resultJsonFactory->create();
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

        $order = $this->_getOrder();
        $this->sistecreditoOrderLog = new SistecreditoOrderLog();

        try {
            $this->sistecreditoOrderLog->orderId = $order->getRealOrderId();
            $this->sistecreditoOrderLog->totalOrder = $order->getGrandTotal();

            $typeDocument = $this->getRequest()->getParam("typeDocument");
            $idDocument = $this->getRequest()->getParam("idDocument");
            if ($typeDocument == "" || $idDocument == "") {
                $this->sistecreditoOrderLog->action = GatewayActions::VERIFY_DOCUMENT_ID_DOCUMENT_TYPE;
                $this->_dbHelper->createSistecreditoOrderLog("Error on generate the gateway request: " . 'Document Type and Document ID are required fields.', $this->sistecreditoOrderLog);
                throw new Exception('Document Type and Document ID are required fields.');
            }

            //checkout with widget
            if ($this->getRequest()->getParam("onSameSite")) {

                $authentication = $this->getAuthentication();

                //Save the order log
                $this->sistecreditoOrderLog->action = GatewayActions::VALIDATE_ORDER;
                $this->_dbHelper->createSistecreditoOrderLog("Sistecredito [SistecreditoModule::validation]: Order created with status Sistecredito pending and orderId: {$this->sistecreditoOrderLog->orderId}", $this->sistecreditoOrderLog);

                return $resultJson->setData([
                    "orderId" => $order->getRealOrderId(),
                    "authentication" => $authentication
                ]);
            }

            //send request to gateway, create transaction and get the response
            $gatewayUrl = $this->_gatewayEndpoint->getPaymentProcessUrl($order, $typeDocument, $idDocument, $this->sistecreditoOrderLog);

            //Save the order log
            $this->sistecreditoOrderLog->action = GatewayActions::VALIDATE_ORDER;
            $this->_dbHelper->createSistecreditoOrderLog("Sistecredito [SistecreditoModule::validation]: Order created with status Sistecredito pending and orderId: {$this->sistecreditoOrderLog->orderId}", $this->sistecreditoOrderLog);

            //Redirect to the gateway url to process the payment
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setUrl($gatewayUrl);
        } catch (Exception $exception) {

            //Can't create the order, cancel the order and redirect to the cart
            $payment = $order->getPayment();
            $payment->setAmountCanceled($order->getGrandTotal());
            $order->setState(Order::STATE_CANCELED);
            $order->setStatus(Order::STATE_CANCELED);
            $order->registerCancellation(__($exception->getMessage()))->save();

            //Redirect to the cart and restore the cart
            $this->_checkoutSession->restoreQuote();
            $this->_messageManager->addErrorMessage(__("There was an error trying to process the gateway response, please try again later."));
            $this->_messageManager->addErrorMessage($exception->getMessage());

            $this->sistecreditoOrderLog->action = GatewayActions::CONFIRMATION_PAYMENT_EXCEPTION;
            $this->_dbHelper->createSistecreditoOrderLog("Error on generate the gateway request: " . $exception->getMessage(), $this->sistecreditoOrderLog);

            return ($this->getRequest()->getParam("onSameSite")) ?
                $resultJson->setHttpResponseCode(500) :
                $resultRedirect->setPath('checkout/cart', ['_secure' => false]);
        }
    }

    private function _getOrder(): ?Order
    {
        $orderId = $this->_checkoutSession->getLastRealOrderId();

        if (!isset($orderId)) {
            return null;
        }

        return $this->_getOrderById($orderId);
    }

    private function _getOrderById($orderId): ?Order
    {
        $order = $this->_orderFactory->create()->loadByIncrementId($orderId);

        if (!$order->getId()) {
            return null;
        }

        return $order;
    }

    public function getAuthentication(): ?string
    {
        $this->sistecreditoOrderLog->action = GatewayActions::TOKEN_GENERATED;
        $this->sistecreditoOrderLog->requestToken = md5($this->sistecreditoOrderLog->orderId);
        $this->sistecreditoOrderLog->requestUrl = $this->_gatewayConfig->getVisorUrl();

        $jwtPayload = array(
            "iss" => $this->getUrlResponse(),
            "aud" => $this->getUrlResponse(),
            "iat" => time(),
            "nbf" => strtotime("+5 seconds"),
        );

        $this->sistecreditoOrderLog->jwt = JWT::encode($jwtPayload, $this->sistecreditoOrderLog->requestToken, "HS256");
        $this->_dbHelper->createSistecreditoOrderLog("Sistecredito [SistecreditoModule::validation]: Token Generated jwtKey -{$this->sistecreditoOrderLog->requestToken},JwtValue - {$this->sistecreditoOrderLog->jwt}", $this->sistecreditoOrderLog);
        return $this->sistecreditoOrderLog->jwt;

    }

    public function getUrlResponse(): string
    {
        return $this->_urlInterface->getBaseUrl();
    }
}
