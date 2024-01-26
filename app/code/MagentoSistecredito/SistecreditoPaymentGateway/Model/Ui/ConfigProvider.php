<?php

/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace MagentoSistecredito\SistecreditoPaymentGateway\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Url;
use MagentoSistecredito\SistecreditoPaymentGateway\Gateway\Config\Config;
use MagentoSistecredito\SistecreditoPaymentGateway\Helper\DbHelper;
use MagentoSistecredito\SistecreditoPaymentGateway\Helper\GatewayActions;


/**
 * Class ConfigProvider
 */
final class ConfigProvider implements ConfigProviderInterface
{
    const CODE = "sistecredito_gateway";

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

    public function __construct(
        Config   $gatewayConfig,
        Url      $urlInterface,
        DbHelper $_dbHelper
    )
    {
        $this->_gatewayConfig = $gatewayConfig;
        $this->_urlInterface = $urlInterface;
        $this->_dbHelper = $_dbHelper;

    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {

        $data = [
            "idTypes" => [
                "CC" => __("Cédula Ciudadanía"),
                "CE" => __("Cédula Extranjería")
            ],
            "visorJs" => $this->_gatewayConfig->getVisorUrl(),
            "dataKey" => $this->_gatewayConfig->getDataKey(),
            "onSameSite" => $this->_gatewayConfig->getOnSameSite(),
            "vendorId" => $this->_gatewayConfig->getVendorId(),
            "storeId" => $this->_gatewayConfig->getStoreId(),
            "authentication" => "Authentication",
            "orderId" => "orderId",
            "responseUrl" => $this->_urlInterface->getBaseUrl() . GatewayActions::CONFIRMATION_PAGE_ROUTE,
            "environment" => $this->_gatewayConfig->getEnvironment(),
            "storeApps" => $this->_gatewayConfig->getStoreApps(),
            "subscriptionKeyVisor" => $this->_gatewayConfig->getSubscriptionKeyVisor(),
            "urlReturn" => $this->_gatewayConfig->getUrlReturn(),
        ];

        return [
            "payment" => [
                self::CODE => $data
            ]
        ];
    }


}
