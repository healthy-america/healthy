<?php

namespace MagentoSistecredito\SistecreditoPaymentGateway\Helper;

use Firebase\JWT\JWT;
use Zend_Http_Client;
use Zend_Http_Response;
use Magento\Framework\Url;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use Magento\Sales\Model\Order;
use MagentoSistecredito\SistecreditoPaymentGateway\Gateway\Config\Config;

class GatewayEndpoint
{
    const ERROR_COMMUNICATION = "ERROR_COMMUNICATION";

    /**
     * @var Config
     */
    private $_gatewayConfig;

    /**
     * @var Url
     */
    private $_urlInterface;

    /**
     * @var DbHelper $dbHelper
     */
    protected DbHelper $_dbHelper;

    /**
     * @var SistecreditoOrderLog $sistecreditoOrderLog
     */
    private SistecreditoOrderLog $sistecreditoOrderLog;

    public function __construct(
        Config            $gatewayConfig,
        Url               $urlInterface,
        DbHelper          $_dbHelper
    )
    {
        $this->_gatewayConfig = $gatewayConfig;
        $this->_urlInterface = $urlInterface;
        $this->_dbHelper = $_dbHelper;
    }

    /**
     * @param Order $order
     * @param string $typeDocument
     * @param string $idDocument
     * @param SistecreditoOrderLog $sistecreditoOrderLog
     * @return string
     * @throws \Exception
     */
    public function getPaymentProcessUrl(Order $order, string $typeDocument, string $idDocument, SistecreditoOrderLog &$sistecreditoOrderLog): string
    {
        $this->sistecreditoOrderLog = $sistecreditoOrderLog;

        $requestHeaders = $this->_getHeaders($order, true);

        $this->sistecreditoOrderLog->request = $this->_getBody($order, $typeDocument, $idDocument);
        $this->sistecreditoOrderLog->requestUrl = $this->_gatewayConfig->getGatewayUrl();

        $client = new Client();
        $options = [
            'headers' => $requestHeaders,
            'body' => $this->sistecreditoOrderLog->request
        ];

        try {
            $this->sistecreditoOrderLog->action = GatewayActions::REST_API_REQUEST_SENT;

            $response = $client->post($this->sistecreditoOrderLog->requestUrl, $options);
            $parsedResponse = $this->_parseResponse($response->getBody()->getContents());

            if ($parsedResponse->getErrorCode() == GatewayResponse::SUCCESS_CODE) {
                $this->sistecreditoOrderLog->transactionId = $parsedResponse->getTransactionId();
                $this->sistecreditoOrderLog->gateway_url = $parsedResponse->getUrlToRedirect() . '?transactionId=' . $parsedResponse->getTransactionId();
                $this->_dbHelper->createSistecreditoOrderLog("Sistecredito [SistecreditoModule::validation]: Transaction creation successful, redirect url is {$this->sistecreditoOrderLog->gateway_url}", $this->sistecreditoOrderLog);
                return $this->sistecreditoOrderLog->gateway_url;
            }

            $error = (isset(GatewayActions::ERRORS[$parsedResponse->getErrorCode()])) ? GatewayActions::ERRORS[$parsedResponse->getErrorCode()] : null;
            if (is_null($error)) $error = ["message" => $parsedResponse->getMessage(), "description" =>  __('An error occurred while processing the transaction, please try again later.')];

            $this->_dbHelper->createSistecreditoOrderLog("Sistecredito [SistecreditoModule::validation]: Error create transaction payment: {$parsedResponse->getErrorCode()} - {$error["message"]} - {$error["description"]}", $this->sistecreditoOrderLog);

            throw new \Exception($parsedResponse->getErrorCode() . "-" . $error["description"]);
        } catch (RequestException $e) {
            throw new \Exception(self::ERROR_COMMUNICATION);
        }
    }

    /**
     * @param $sisteCreditoOrderLog
     * @param Order $order
     * @return mixed
     * @throws \Exception
     */
    public function getInfoCredit(&$sisteCreditoOrderLog, Order $order)
    {
        $this->sistecreditoOrderLog = $sisteCreditoOrderLog;
        $this->sistecreditoOrderLog->requestUrl = $this->_gatewayConfig->getInfoCreditUrl() . "{$sisteCreditoOrderLog->transactionId}";
        $requestHeader = $this->_getHeaders($order);
        $this->sistecreditoOrderLog->request = json_encode($requestHeader);

        $client = new Client();
        $options = [
            'headers' => $requestHeader
        ];

        try {
            $response = $client->get($this->sistecreditoOrderLog->requestUrl, $options);
            $this->sistecreditoOrderLog->response = $response->getBody()->getContents();

            return json_decode($this->sistecreditoOrderLog->response);
        } catch (RequestException $e) {
            throw new \Exception(self::ERROR_COMMUNICATION);
        }
    }

    private function _getBody(Order $order, string $typeDocument, string $idDocument): string
    {
        return json_encode([
            'typeDocument' => $typeDocument,
            'idDocument' => $idDocument,
            'transactionDate' => $order->getCreatedAt(),
            'valueToPaid' => $order->getGrandTotal(),
            'vendorId' => $this->_gatewayConfig->getVendorId(),
            'storeId' => $this->_gatewayConfig->getStoreId(),
            'orderId' => $order->getRealOrderId(),
            'responseUrl' => $this->_urlInterface->getBaseUrl() . GatewayActions::CONFIRMATION_PAGE_ROUTE,
        ]);
    }

    private function _getHeaders(Order $order, $isAuth = false): array
    {
        $header = [
            "Content-Type" => "application/json",
            "Ocp-Apim-Subscription-Key" => $this->_gatewayConfig->getSubscriptionKey(),
            "SCLocation" => "0,0",
            "country" => "co",
            "SCOrigen" => $this->_gatewayConfig->getEnvironment(),
        ];
        $isAuth ? $header["Authentication"] = $this->_generate_jwt_token($order->getRealOrderId()) : null;
        return $header;
    }

    public function _generate_jwt_token($orderId): ?string
    {
        $this->sistecreditoOrderLog->requestToken = md5($orderId);
        $baseUrl = $this->_urlInterface->getBaseUrl();

        $jwtPayload = array(
            "iss" => $baseUrl,
            "aud" => $baseUrl,
            "iat" => time(),
            "nbf" => strtotime("+5 seconds"),
        );

        $this->sistecreditoOrderLog->jwt = JWT::encode($jwtPayload, $this->sistecreditoOrderLog->requestToken, "HS256");
        $this->sistecreditoOrderLog->action = GatewayActions::TOKEN_GENERATED;
        $this->_dbHelper->createSistecreditoOrderLog("Sistecredito [SistecreditoModule::validation]: Token Generated jwtKey -{$this->sistecreditoOrderLog->requestToken},JwtValue - {$this->sistecreditoOrderLog->jwt}", $this->sistecreditoOrderLog);


        return $this->sistecreditoOrderLog->jwt;
    }

    private function _parseResponse($response): GatewayResponse
    {
        $this->sistecreditoOrderLog->response = $response;
        $parsedObject = json_decode($response);
        $errorCode = $parsedObject->errorCode ?? $parsedObject->statusCode;

        if ($errorCode == GatewayResponse::SUCCESS_CODE) {
            return new GatewayResponse($parsedObject->errorCode, $parsedObject->message, $parsedObject->data->urlToRedirect, $parsedObject->data->transactionId);
        }

        return new GatewayResponse($errorCode, $parsedObject->message);
    }
}
