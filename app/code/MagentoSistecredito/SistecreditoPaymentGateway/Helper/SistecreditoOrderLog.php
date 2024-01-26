<?php

namespace MagentoSistecredito\SistecreditoPaymentGateway\Helper;

class SistecreditoOrderLog
{

    /**
     * @var boolean
     */
    public $id;

    /**
     * @var string
     */
    public $date;

    /**
     * @var string
     */
    public $orderId;

    /**
     * @var float
     */
    public $totalOrder;

    /**
     * @var string
     */
    public $action;

    /**
     * @var string
     */
    public $requestToken = null;

    /**
     * @var string
     */
    public$jwt = null;

    /**
     * @var string
     */
    public $requestUrl = null;

    /**
     * @var string
     */
    public $request = null;

    /**
     * @var string
     */
    public $response = null;

    /**
     * @var string
     */
    public $transactionId = null;

    /**
     * @var int
     */
    public $creditNumber = null;


}
