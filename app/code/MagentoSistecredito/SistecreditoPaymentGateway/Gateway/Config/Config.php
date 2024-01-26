<?php

namespace MagentoSistecredito\SistecreditoPaymentGateway\Gateway\Config;

class Config extends \Magento\Payment\Gateway\Config\Config
{
    const VENDOR_ID = 'vendor_id';
    const STORE_ID = 'store_id';
    const SUBSCRIPTION_KEY = 'subscription_key';
    const ENVIRONMENT = 'environment';
    const DATA_KEY = 'data_key';
    const ON_SAME_SITE = 'on_same_site';
    const URL_RETURN = 'url_return';

    const VALIDATE_FIELD = 'validate_field';

    const PATH_START_CREDIT = "/startCredit";
    const PATH_GET_INFO_CREDIT = "/getInfoCredit?transactionId=";

    const ENDPOINTS = [
        "Development" => [
            "startCredit" => "https://devapi.credinet.co/paymentgateway" . self::PATH_START_CREDIT,
            "getInfoCredit" => "https://devapi.credinet.co/paymentgateway" . self::PATH_GET_INFO_CREDIT,
            "visorJs" => "https://stonprdeu2sistepayecomme.blob.core.windows.net/ecommerce-dev/visor/visor.js",
            "storeApps" => "https://devapi.credinet.co/viewfinder/GetStoreApps?productoCliente=",
            "subscriptionKeyVisor" => "b0dc8eb7924540e1913ab262b8500721",
        ],
        "Qa" => [
            "startCredit" => "https://devapi.credinet.co/paymentgateway" . self::PATH_START_CREDIT,
            "getInfoCredit" => "https://devapi.credinet.co/paymentgateway" . self::PATH_GET_INFO_CREDIT,
            "visorJs" => "https://stonprdeu2sistepayecomme.blob.core.windows.net/ecommerce-qa/visor/visor.js",
            "storeApps" => "https://devapi.credinet.co/viewfinder/GetStoreApps?productoCliente=",
            "subscriptionKeyVisor" => "b0dc8eb7924540e1913ab262b8500721",
        ],
        "Staging" => [
            "startCredit" => "https://api.credinet.co/paymentpublic" . self::PATH_START_CREDIT,
            "getInfoCredit" => "https://api.credinet.co/paymentpublic" . self::PATH_GET_INFO_CREDIT,
            "visorJs" => "https://stostgeu2sistepayecommer.blob.core.windows.net/ecommerce/visor/visor.js",
            "storeApps" => "https://api.credinet.co/viewfinder/api/Visor/GetStoreApps?productoCliente=",
            "subscriptionKeyVisor" => "20a09a82fe574408bcc22d148a684e54",
        ],
        "Production" => [
            "startCredit" => "https://api.credinet.co/paymentpublic" . self::PATH_START_CREDIT,
            "getInfoCredit" => "https://api.credinet.co/paymentpublic" . self::PATH_GET_INFO_CREDIT,
            "visorJs" => "https://stoprdeu2sistepayecomerc.blob.core.windows.net/ecommerce/visor/visor.js",
            "storeApps" => "https://api.credinet.co/viewfinder/api/Visor/GetStoreApps?productoCliente=",
            "subscriptionKeyVisor" => "20a09a82fe574408bcc22d148a684e54",
        ],
    ];

    public function getGatewayUrl(): string
    {
        return self::ENDPOINTS[$this->getEnvironment()]['startCredit'];
    }

    public function getInfoCreditUrl(): string
    {
        return self::ENDPOINTS[$this->getEnvironment()]['getInfoCredit'];
    }

    public function getVisorUrl(): string
    {
        return self::ENDPOINTS[$this->getEnvironment()]['visorJs'];
    }

    public function getVendorId(): string
    {
        return $this->getValue(self::VENDOR_ID);
    }

    public function getStoreId(): string
    {
        return $this->getValue(self::STORE_ID);
    }

    public function getSubscriptionKey(): string
    {
        return $this->getValue(self::SUBSCRIPTION_KEY);
    }

    public function getValidateField(): string
      {
            return $this->getValue(self::VALIDATE_FIELD);
      }
    public function getEnvironment()
    {
        return $this->getValue(self::ENVIRONMENT);
    }

    public function getDataKey()
    {
        return $this->getValue(self::DATA_KEY);
    }

    public function getOnSameSite(): string
    {
        return $this->getValue(self::ON_SAME_SITE);
    }

    public function getStoreApps()
    {
        return self::ENDPOINTS[$this->getEnvironment()]['storeApps'];
    }

    public function getSubscriptionKeyVisor()
    {
        return self::ENDPOINTS[$this->getEnvironment()]['subscriptionKeyVisor'];
    }

    public function getUrlReturn()
    {
        return $this->getValue(self::URL_RETURN);
    }
}
