<?php

namespace MagentoSistecredito\SistecreditoPaymentGateway\Controller\Gateway;

use Exception;

use Firebase\JWT\Key;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Url;
use Magento\Sales\Model\Order\Payment\Transaction;
use Magento\Store\Model\ScopeInterface;
use MagentoSistecredito\SistecreditoPaymentGateway\Helper\DbHelper;
use MagentoSistecredito\SistecreditoPaymentGateway\Helper\GatewayActions;
use MagentoSistecredito\SistecreditoPaymentGateway\Helper\GatewayEndpoint;
use MagentoSistecredito\SistecreditoPaymentGateway\Helper\SistecreditoOrderLog;
use MagentoSistecredito\SistecreditoPaymentGateway\Helper\SistecreditoResponse;
use MagentoSistecredito\SistecreditoPaymentGateway\Gateway\Config;
use Magento\Checkout\Model\Session;
use Magento\Sales\Model\OrderFactory;
use Magento\Framework\View\Result\Page;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Message\ManagerInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Api\Data\OrderPaymentInterface;

class Confirm extends Action
{

    /**
     * @var Url
     */
    private $_urlInterface;

    /**
     * @var OrderFactory
     */
    private $_orderFactory;

    /**
     *
     * @var Session
     */
    private $_checkoutSession;

    /**
     *
     * @var ManagerInterface
     */
    private $_messageManager;

    /**
     *
     * @var GatewayEndpoint
     */
    private GatewayEndpoint $_gatewayEndpoint;

    /**
     * @var SistecreditoOrderLog $sistecreditoOrderLog
     */
    private SistecreditoOrderLog $sistecreditoOrderLog;

    /**
     * @var SistecreditoResponse $responseClient
     */
    private SistecreditoResponse $responseClient;

    /**
     * @var Json $resultJsonFactory
     */
    private Json $resultJsonFactory;

    /**
     * @var DbHelper $_dbHelper
     */
    protected DbHelper $_dbHelper;

    private $_confirmationRequestValidations = [
        "TypeDocument" => [
            "filter" => FILTER_VALIDATE_REGEXP,
            "options" => ["regexp" => "/^(CC|CE)$/"],
        ],
        "IdDocument" => [
            "filter" => FILTER_VALIDATE_REGEXP,
            "options" => ["regexp" => "/^[0-9a-zA-Z]+?$/"],
        ],
        "ValueToPay" => FILTER_VALIDATE_FLOAT,
        "OrderId" => [
            "filter" => FILTER_VALIDATE_REGEXP,
            "options" => ["regexp" => "/^[0-9]+?$/"],
        ],
        "CreditNumber" => FILTER_VALIDATE_INT,
        "Authentication" => [
            "filter" => FILTER_VALIDATE_REGEXP,
            "options" => ["regexp" => "/^.+?$/"],
        ],
        "TransactionStatus" => [
            "filter" => FILTER_VALIDATE_REGEXP,
            "options" => ["regexp" => "/^(Terminado|Error|Abandonada)$/"],
        ],
        "TransactionId" => [
            "filter" => FILTER_VALIDATE_REGEXP,
            "options" => ["regexp" => "/^.+?$/"],
        ]
    ];

    private $_orderStatusRequestValidations = [
        "transactionId" => [
            "filter" => FILTER_VALIDATE_REGEXP,
            "options" => ["regexp" => "/^.+?$/"],
        ],
        "orderId" => [
            "filter" => FILTER_VALIDATE_REGEXP,
            "options" => ["regexp" => "/^[0-9]+?$/"],
        ],
    ];

    public $scopeConfig;

    public function __construct(
        Context         $context,
        Url             $urlInterface,
        OrderFactory    $orderFactory,
        GatewayEndpoint $gatewayEndpoint,
        Session         $checkoutSession,
        DbHelper        $_dbHelper,
        ScopeConfigInterface $scopeConfig
    )
    {
        parent::__construct($context);

        $this->_urlInterface = $urlInterface;
        $this->_orderFactory = $orderFactory;
        $this->_checkoutSession = $checkoutSession;
        $this->_gatewayEndpoint = $gatewayEndpoint;
        $this->_messageManager = $context->getMessageManager();
        $this->_dbHelper = $_dbHelper;
        $this->scopeConfig = $scopeConfig;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }

    /**
     * @return ResponseInterface|ResultInterface|Page
     */
    public function execute()
    {
        $requestType = $_SERVER['REQUEST_METHOD'];
        switch ($requestType) {
            case 'POST':
                $requestBody = $this->_dbHelper->fileGetContents("php://input");
                $parsedResponse = json_decode($requestBody, true);
                $confirmationRequest = filter_var_array($parsedResponse, $this->_confirmationRequestValidations);
                return $this->confirmPayment($confirmationRequest);
            case 'GET':
                return $this->payerOrderResponse();
        }
    }

    private function confirmPayment($confirmationRequest)
    {
        $this->resultJsonFactory = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $this->resultJsonFactory->setHeader('Content-Type', 'application/json');

        $this->responseClient = new SistecreditoResponse();
        $this->sistecreditoOrderLog = new SistecreditoOrderLog();
        $this->sistecreditoOrderLog->request = json_encode($confirmationRequest);
        $this->sistecreditoOrderLog->orderId = $confirmationRequest["OrderId"];
        $this->sistecreditoOrderLog->requestUrl = $this->_urlInterface->getBaseUrl() . GatewayActions::CONFIRMATION_PAGE_ROUTE;
        $this->sistecreditoOrderLog->jwt = $confirmationRequest["Authentication"];
        $this->sistecreditoOrderLog->transactionId = $confirmationRequest["TransactionId"];
        $this->sistecreditoOrderLog->creditNumber = $confirmationRequest["CreditNumber"];
        $this->sistecreditoOrderLog->totalOrder = floatval($confirmationRequest["ValueToPay"]);

        //Validate request parameters
        if (!$this->validateRequestParams($confirmationRequest)) {
            return $this->resultJsonFactory;
        }

        $order = $this->_orderFactory->create()->loadByIncrementId($this->sistecreditoOrderLog->orderId);

        try {
            //Validate status transaction
            if (!$this->validateStatusTransaction($confirmationRequest, $order)) {
                return $this->resultJsonFactory;
            }

            $sistecreditoOrderLog = $this->_dbHelper->getSistecreditoOrderLog($this->sistecreditoOrderLog);
            $this->sistecreditoOrderLog->action = GatewayActions::GET_SISTECREDITO_ORDER_LOG;

            if (!$sistecreditoOrderLog) {
                $this->sistecreditoOrderLog->requestToken = md5($this->sistecreditoOrderLog->orderId);
            } else {
                $this->sistecreditoOrderLog->requestToken = $sistecreditoOrderLog["request_token"];
                $this->sistecreditoOrderLog->totalOrder = floatval($sistecreditoOrderLog["total_order"]);
            }

            $this->responseClient->message = "Records were found in sistecredito for the order received {$this->sistecreditoOrderLog->orderId}";
            $this->saveResponseConfirmation();

            try {
                if (!$this->validateRequestJwt($order)) {
                    return $this->resultJsonFactory;
                }
            } catch (Exception $e) {
                $this->responseClient->errorCode = 825;
                $this->sistecreditoOrderLog->action = GatewayActions::VALIDATE_JWT_REQUEST_EXCEPTION;
                $this->responseClient->message = __(sprintf('Error validating JWT Token: %s, The key error is %s', $e->getMessage(), $this->sistecreditoOrderLog->requestToken));
                $this->saveResponseConfirmation(true, $order);
                return $this->resultJsonFactory;
            }

            //Validate amount transaction
            if($this->validateAmountTransaction($order, floatval($confirmationRequest["ValueToPay"]))){
                //Create invoice transaction
                $this->invoiceOrder($order, $this->sistecreditoOrderLog->creditNumber);
                $this->sistecreditoOrderLog->action = GatewayActions::CONFIRMATION_PAYMENT;
                $this->_dbHelper->createSistecreditoOrderLog("payment confirm", $this->sistecreditoOrderLog);
            }

            return $this->resultJsonFactory;
        } catch (Exception $exception) {
            $this->sistecreditoOrderLog->action = GatewayActions::CONFIRMATION_PAYMENT_EXCEPTION;
            $this->responseClient->errorCode = 826;
            $this->responseClient->message = __(sprintf('Error confirming payment: %s', $exception->getMessage()));
            $this->saveResponseConfirmation(true, $order);
            return $this->resultJsonFactory;
        }

    }

    /**
     * @throws \Zend_Http_Client_Exception
     */
    private function payerOrderResponse()
    {
        $validatedGetParams = $this->_dbHelper->filterInputArray(INPUT_GET, $this->_orderStatusRequestValidations);

        if ($validatedGetParams !== NULL) {
            $isRequestValid = array_reduce($validatedGetParams, function ($previousValue, $currentField) {
                return $previousValue && $currentField !== FALSE;
            }, true);
        }

        if ($validatedGetParams === NULL || $isRequestValid === FALSE) {
            $this->_messageManager->addErrorMessage(__("We noticed a problem with your order. If you think this is an error, feel free to contact our."));
            return $this->redirect(false);
        }

        $order = $this->_orderFactory->create()->loadByIncrementId($validatedGetParams["orderId"]);

        $this->sistecreditoOrderLog = new SistecreditoOrderLog();
        $this->sistecreditoOrderLog->transactionId = $validatedGetParams["transactionId"];
        $this->sistecreditoOrderLog->orderId = $validatedGetParams["orderId"];

        $this->updateStatusTransactionGetInfoCredit($order);

        if (in_array($order->getState(), [Order::STATE_CANCELED])) {
            $this->_checkoutSession->restoreQuote();
            $this->_messageManager->addErrorMessage(__("We noticed a problem with your order. If you think this is an error, feel free to contact our."));
            $this->_messageManager->addErrorMessage(__("We have restored the order so that it can be processed again."));

            return $this->redirect(false);
        }

        $this->_messageManager->addSuccessMessage(__("Payment request accepted by Sistecredito"));

        return $this->redirect();
    }

    private function redirect(bool $success = true){
        /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $nameValue=Config\Config::URL_RETURN;

        $urlReturn = $this->getValueConfigData($nameValue);

        if($urlReturn){
            try {
                $response = $this->_dbHelper->validUrlRedirect($urlReturn);
                if($response){
                    return $resultRedirect->setPath($urlReturn, ['_secure' => false]);
                }
            }catch (Exception $exception){
                return $this->redirectMagento($success,$resultRedirect);
            }

        }

        return $this->redirectMagento($success,$resultRedirect);
    }



    private function redirectMagento($success,$resultRedirect){
        if($success){
            return $resultRedirect->setPath('checkout/onepage/success', ['_secure' => false]);
        }
        else {
            return $resultRedirect->setPath('checkout/onepage/failure', ['_secure' => false]);
        }
    }

    private function getValueConfigData($nameValue){
        $methodCode="sistecredito_gateway";
        return $this->scopeConfig->getValue(
            sprintf(\Magento\Payment\Gateway\Config\Config::DEFAULT_PATH_PATTERN, $methodCode, $nameValue),
            ScopeInterface::SCOPE_STORE,
        );
    }

    private function invoiceOrder(Order $order, $transactionId)
    {
        if (!$order->canInvoice()) {
            throw new Exception(
                __('Cannot create an invoice.')
            );
        }

        $invoice = $this->_dbHelper->getObjectManager()
            ->create('Magento\Sales\Model\Service\InvoiceService')
            ->prepareInvoice($order);

        if (!$invoice->getTotalQty()) {
            throw new Exception(
                __('You can\'t create an invoice without products.')
            );
        }

        $invoice->setTransactionId($transactionId);
        $invoice->setRequestedCaptureCase(Order\Invoice::CAPTURE_OFFLINE);
        $invoice->register();

        $transaction = $this->_dbHelper->getObjectManager()->create('Magento\Framework\DB\Transaction')
            ->addObject($invoice)
            ->addObject($invoice->getOrder());

        $transaction->save();
    }

    /**
     * Validate request params
     * @param $confirmationRequest
     * @return bool
     */
    private function validateRequestParams($confirmationRequest)
    {
        $this->sistecreditoOrderLog->action = GatewayActions::VALIDATE_REQUEST_PARAMS;
        $areThereErrors = array_reduce($confirmationRequest, function ($errors_exist, $next) {
            return empty($next);
        }, false);

        if ($areThereErrors || $confirmationRequest == null) {
            $this->responseClient->errorCode = 822;
            $this->responseClient->message = __('The gateway rejected the payment request. Invalid Params');
            $this->saveResponseConfirmation(true);
            return false;
        }

        $this->responseClient->message = __('The gateway accepted the payment request. the parameters are correct.');
        $this->saveResponseConfirmation();
        return true;
    }

    /**
     * Validate status transaction
     * @param $confirmationRequest
     * @param $order
     * @return bool
     */
    public function validateStatusTransaction($confirmationRequest, $order)
    {
        $this->sistecreditoOrderLog->action = GatewayActions::VALIDA_STATUS_TRANSACTION;

        if($order->getState() == Order::STATE_COMPLETE){
            $this->responseClient->errorCode = 823;
            $this->responseClient->message = __('The eccommerce rejected the payment request. Order already confirm.');
            $this->saveResponseConfirmation(true, $order);
            return false;
        }

        if ($confirmationRequest["TransactionStatus"] != "Terminado" && $confirmationRequest["TransactionStatus"] != "Abandonada") {
            $this->responseClient->errorCode = 823;
            $this->responseClient->message = __('The eccommerce rejected the payment request. Status transaction invalid');
            $this->saveResponseConfirmation(true, $order);
            return false;
        }

        if ($confirmationRequest["TransactionStatus"] == "Abandonada") {
            $this->responseClient->errorCode = 823;
            $this->responseClient->message = __('El usuario abandonó el proceso de pago con Sistecrédito');
            $this->saveResponseConfirmation(true, $order);
            return false;
        }

        $this->responseClient->message = __('The eccommerce accepted the payment request. Status transaction success');
        $this->saveResponseConfirmation();
        return true;
    }

    /**
     * validate request Jwt
     * @param $order
     * @return bool
     */
    public function validateRequestJwt($order)
    {
        $this->sistecreditoOrderLog->action = GatewayActions::VALIDATE_REQUEST_JWT;
        $jwt = new Key($this->sistecreditoOrderLog->requestToken, "HS256");
        $decodedJwtToken = $this->_dbHelper->decodeJwt($this->sistecreditoOrderLog->jwt, $jwt, array('HS256'));

        if ($decodedJwtToken->aud !== $this->_urlInterface->getBaseUrl()) {
            $this->responseClient->errorCode = 824;
            $this->responseClient->message = __(sprintf('The JWT token has a different audience (%s) than expected (%s)', $decodedJwtToken->aud, $this->_dbHelper->getShopDomainSsl()), 'confirmation');
            $this->saveResponseConfirmation(true, $order);
            return false;
        }

        $this->responseClient->message = sprintf('Received JWT Token: %s <br>', json_encode($decodedJwtToken));
        $this->saveResponseConfirmation();
        return true;

    }

    /**
     * validate amount transaction and confirm payment if accepted
     * @param $order
     * @param $totalSiste
     * @return bool
     */
    public function validateAmountTransaction($order, $totalSiste)
    {
        $this->sistecreditoOrderLog->action = GatewayActions::VALIDATE_AMOUNT_TRANSACTION;
        $total = floatval($order->getGrandTotal());
        $totalValid = $total === $totalSiste || $this->sistecreditoOrderLog->totalOrder === $totalSiste;

        if (!$totalValid) {
            $this->responseClient->errorCode = 826;
            $this->responseClient->message = __(sprintf('Value received from gateway (%f) does not match with the cart total (%f)', $totalSiste, $total), 'confirmation');
            $this->saveResponseConfirmation(true, $order);
            return false;
        }

        $this->responseClient->errorCode = 0;
        $this->responseClient->message = "";
        $this->saveResponseConfirmation(false, $order, true);
        return true;
    }

    /**
     * Validate status transacation and confirm payment if accepted
     * @param Order $order
     * @return void
     * @throws \Zend_Http_Client_Exception
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updateStatusTransactionGetInfoCredit(Order &$order)
    {
        $this->sistecreditoOrderLog->action = GatewayActions::GET_INFO_CREDIT;
        $sistecreditoResponse = $this->_gatewayEndpoint->getInfoCredit($this->sistecreditoOrderLog, $order);
        if (isset($sistecreditoResponse->data->transactionStatus)) {
            $this->sistecreditoOrderLog->jwt = $sistecreditoResponse->data->authentication;
            $this->sistecreditoOrderLog->creditNumber = (isset($sistecreditoResponse->data->credit->creditNumber)) ? $sistecreditoResponse->data->credit->creditNumber : null;
            $this->sistecreditoOrderLog->totalOrder = floatval($sistecreditoResponse->data->valueToPaid);

            //Validar si aún esta pendiente
            if (in_array($order->getState(), [Order::STATE_PENDING_PAYMENT])) {
                if (GatewayActions::SISTECREDITO_PAYMENT_ACCEPTED_STATE == $sistecreditoResponse->data->transactionStatus) {
                    $order->setState(Order::STATE_PROCESSING)
                        ->setStatus(Order::STATE_PROCESSING)
                        ->addStatusHistoryComment(__("Sistecredito payment received. Credit number: " . $this->sistecreditoOrderLog->creditNumber));
                    /** @var OrderPaymentInterface $payment */
                    $order->setCustomerNoteNotify(1);
                    if ($this->sistecreditoOrderLog->creditNumber) {
                        $payment = $order->getPayment();
                        $payment->setTransactionId($this->sistecreditoOrderLog->creditNumber);
                        $payment->addTransaction(Transaction::TYPE_CAPTURE, null, true);
                    }
                    $order->save();
                }
                else {
                    $payment = $order->getPayment();
                    $payment->setAmountCanceled($order->getGrandTotal());
                    $order->setState(Order::STATE_CANCELED);
                    $order->setStatus(Order::STATE_CANCELED);
                    $order->registerCancellation(__("Payment request rejected by Sistecredito"))->save();
                }
            }
        }

        $this->_dbHelper->createSistecreditoOrderLog("Sistecredito [SistecreditoModule::response] - getInfoCredit for transactionID: {$this->sistecreditoOrderLog->transactionId}.", $this->sistecreditoOrderLog);

    }

    /**
     * Save logs in database
     * @param $error
     * @param Order|null $order
     * @param $success
     * @return void
     */
    public function saveResponseConfirmation($error = false, Order $order = null, $success = false)
    {
        if ($error || $success) {
            $sistecreditoRequest = json_decode($this->sistecreditoOrderLog->request);
            $response = [
                "function" => "/module/sistecredito/confirmation",
                "errorCode" => $this->responseClient->errorCode,
                "message" => __($this->responseClient->message),
                "country" => "co",
                "data" => [
                    "orderId" => $this->sistecreditoOrderLog->orderId,
                    "transactionId" => $this->sistecreditoOrderLog->transactionId,
                    "status" => $sistecreditoRequest->TransactionStatus,
                    "errors" => $this->responseClient->errors,
                ],
            ];
            $this->resultJsonFactory->setData($response);
            $this->sistecreditoOrderLog->response = json_encode($response);
            $this->resultJsonFactory->setStatusHeader($success ? 200 : 400);
        } else {
            $this->sistecreditoOrderLog->response = null;
        }

        $this->_dbHelper->createSistecreditoOrderLog("Sistecredito [SistecreditoModule::confirmation] - {$this->responseClient->message}", $this->sistecreditoOrderLog);

        if ($order && $error) {
            if($order->getState() == "pending" || $order->getState() == "new" ) {
                $this->incrementInventory($order->getId());
            }
            $payment = $order->getPayment();
            $payment->setAmountCanceled($order->getGrandTotal());
            $order->setState(Order::STATE_CANCELED)
                ->setStatus(Order::STATE_CANCELED)
                ->registerCancellation($this->responseClient->message)
                ->addStatusHistoryComment($this->responseClient->message);
            $order->save();
        } else if ($success) {
            $order->setState(Order::STATE_PROCESSING)
                ->setStatus(Order::STATE_PROCESSING)
                ->addStatusHistoryComment(__("Sistecredito payment received. Credit number: " . $this->sistecreditoOrderLog->creditNumber));
            /** @var OrderPaymentInterface $payment */
            $order->setCustomerNoteNotify(1);
            $payment = $order->getPayment();
            $payment->setTransactionId($this->sistecreditoOrderLog->creditNumber);
            $payment->addTransaction(Transaction::TYPE_CAPTURE, null, true);
            $order->save();
        }

    }

    public function incrementInventory($orderId){
        $objectManager = $this->_dbHelper->getObjectManager();
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $sql = "SELECT sku FROM quote_item WHERE quote_id = '$orderId'";
        $result = $connection->fetchAll($sql);
        if($result != null){
            foreach($result as $sku){
                $sku  = $sku["sku"];
                $sql_ = "SELECT MAX(reservation_id),sku,quantity FROM inventory_reservation WHERE sku = '$sku' ORDER BY reservation_id ASC";
                $query = $connection->fetchAll($sql_);
                if($query != null){
                    foreach($query as $productInventory){
                        $connection->update(
                            'inventory_reservation',
                            ['quantity' => '0.0000'],
                            ['reservation_id = ?' => $productInventory["MAX(reservation_id)"]]
                        );
                    }
                }
            }
        }
    }
}
