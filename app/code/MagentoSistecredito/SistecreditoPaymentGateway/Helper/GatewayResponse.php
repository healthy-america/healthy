<?php

namespace MagentoSistecredito\SistecreditoPaymentGateway\Helper;

class GatewayResponse
{
    const SUCCESS_CODE = "0";

    /** @var string */
    private string $_urlToRedirect;

    /** @var string */
    private string $_transactionId;

    /** @var string */
    private string $_errorCode;

    /** @var string */
    private string $_message;

    public function __construct(string $errorCode, string $message, string $urlToRedirect = '', string $transactionId = '')
    {
        $this->_urlToRedirect = $urlToRedirect;
        $this->_transactionId = $transactionId;
        $this->_errorCode = $errorCode;
        $this->_message = $message;
    }


    public function getUrlToRedirect(): string
    {
        return $this->_urlToRedirect;
    }

    public function getTransactionId(): string
    {
        return $this->_transactionId;
    }

    public function getErrorCode(): string
    {
        return $this->_errorCode;
    }

    public function getMessage(): string
    {
        return $this->_message;
    }
}
