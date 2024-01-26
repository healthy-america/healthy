<?php

namespace MagentoSistecredito\SistecreditoPaymentGateway\Helper;

class SistecreditoResponse
{

    /**
     * @var boolean
     */
    public $is_valid = false;

    /**
     * @var array
     */
    public $errors = [];

    /**
     * @var string
     */
    public $errorCode = 0;

    /**
     * @var string
     */
    public $message = "";

    /**
     * @var array
     */
    public $data = [];

    /**
     * @var string
     */
    public $transactionId = null;

    /**
     * @var int
     */
    public $orderId = 0;

    /**
     * @var string
     */
    public $gateway_url;


}
