<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MagentoSistecredito\SistecreditoPaymentGateway\Test\Unit\Model\Ui;

use MagentoSistecredito\SistecreditoPaymentGateway\Gateway\Config\Config;
use MagentoSistecredito\SistecreditoPaymentGateway\Helper\DbHelper;
use MagentoSistecredito\SistecreditoPaymentGateway\Model\Ui\ConfigProvider;
use Magento\Framework\Url;

class ConfigProviderTest extends \PHPUnit\Framework\TestCase
{
    public $_gatewayConfig;

    public $_urlInterface;

    public $_dbHelper;

    protected function setUp(): void
    {
        $this->_dbHelper = $this->getMockBuilder(DbHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_gatewayConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->onlyMethods([
                'getVisorUrl',
                'getDataKey',
                'getOnSameSite',
                'getVendorId',
                'getStoreId',
                'getEnvironment',
                'getStoreApps',
                'getSubscriptionKeyVisor',
                'getUrlReturn'
            ])
            ->getMock();

        $this->_urlInterface = $this->getMockBuilder(Url::class)
            ->disableOriginalConstructor()
            ->getMock();

        parent::setUp();
    }


    public function testGetConfig()
    {

        $this->_gatewayConfig->expects(static::exactly(1))
            ->method('getVisorUrl')
            ->willReturn("https://sandbox.visor.com.co/visor.js");


        $this->_gatewayConfig->expects(static::exactly(1))
            ->method('getDataKey')
            ->willReturn("data-key");

        $this->_gatewayConfig->expects(static::exactly(1))
            ->method('getOnSameSite')
            ->willReturn("onSameSite");

        $this->_gatewayConfig->expects(static::exactly(1))
            ->method('getVendorId')
            ->willReturn("vendorId");

        $this->_gatewayConfig->expects(static::exactly(1))
            ->method('getStoreId')
            ->willReturn("storeId");

        $this->_gatewayConfig->expects(static::exactly(1))
            ->method('getStoreId')
            ->willReturn("storeId");

        $this->_urlInterface->expects(static::exactly(1))
            ->method('getBaseUrl')
            ->willReturn("https://baseurl.com/");

        $this->_gatewayConfig->expects(static::exactly(1))
            ->method('getEnvironment')
            ->willReturn("sandbox");

        $this->_gatewayConfig->expects(static::exactly(1))
            ->method('getStoreApps')
            ->willReturn("storeApps");

        $this->_gatewayConfig->expects(static::exactly(1))
            ->method('getSubscriptionKeyVisor')
            ->willReturn("subscriptionKeyVisor");

        $this->_gatewayConfig->expects(static::exactly(1))
            ->method('getUrlReturn')
            ->willReturn("https://baseurl.com/sistecredito/Gateway/Return");

        $configProvider = new ConfigProvider(
            $this->_gatewayConfig,
            $this->_urlInterface,
            $this->_dbHelper
        );

        static::assertEquals(
            [
                "payment" => [
                    "sistecredito_gateway"=>[
                        "idTypes" => [
                            "CC" => __("Cédula Ciudadanía"),
                            "CE" => __("Cédula Extranjería")
                        ],
                        "visorJs" => "https://sandbox.visor.com.co/visor.js",
                        "dataKey" => "data-key",
                        "onSameSite" => "onSameSite",
                        "vendorId" => "vendorId",
                        "storeId" => "storeId",
                        "authentication" => "Authentication",
                        "orderId" => "orderId",
                        "responseUrl" => "https://baseurl.com/" . "sistecredito/Gateway/Confirm",
                        "environment" => "sandbox",
                        "storeApps" => "storeApps",
                        "subscriptionKeyVisor" => "subscriptionKeyVisor",
                        "urlReturn" => "https://baseurl.com/sistecredito/Gateway/Return"
                    ]
                ]
            ],
            $configProvider->getConfig()
        );
    }
}
