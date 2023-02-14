<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Configuration extends AbstractHelper
{
    /** Constants connection settings definition. */

    const XML_PATH_WS_SAP_PASSWORD_CUSTOMER = 'sap/setting/customer_password';
    const XML_PATH_WS_SAP_PATH = 'integration/settings/url';
    const XML_PATH_WS_SAP_USERNAME = 'integration/settings/user';
    const XML_PATH_WS_SAP_PASSWORD = 'integration/settings/password';
    const XML_PATH_WS_URL_PRODUCT = 'integration/settings/ws_products_url';
    const XML_PATH_WS_URL_STOCK = 'integration/settings/ws_stock_url';
    const XML_PATH_WS_URL_PRICE = 'integration/settings/ws_price_url';
    const XML_PATH_WS_URL_CUSTOMERS = 'integration/settings/ws_customers_url';
    const XML_PATH_WS_URL_ORDERS = 'integration/settings/ws_orders_url';
    const XML_PATH_WS_URL_STOCK_FAST = 'integration/settings/ws_stock_fast_url';

    /** End constants connection settings definition. */

    const XML_PATH_SAP_WHSCODE = 'integration/document/whscode';
    const XML_PATH_SAP_SHIPPING = 'integration/document/shipping';
    const XML_PATH_SAP_CN = 'integration/document/cardcode';

    public function getUrlWS($store = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_WS_SAP_PATH, ScopeInterface::SCOPE_STORE, $store);
    }

    public function getUrlProducts($store = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_WS_URL_PRODUCT, ScopeInterface::SCOPE_STORE, $store);
    }

    public function getUrlStock($store = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_WS_URL_STOCK, ScopeInterface::SCOPE_STORE, $store);
    }

    public function getUrlPrice($store = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_WS_URL_PRICE, ScopeInterface::SCOPE_STORE, $store);
    }

    public function getUrlOrder($store = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_WS_URL_ORDERS, ScopeInterface::SCOPE_STORE, $store);
    }

    public function getWhsCode($store = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_SAP_WHSCODE, ScopeInterface::SCOPE_STORE, $store);
    }

    public function getShippingCode($store = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_SAP_SHIPPING, ScopeInterface::SCOPE_STORE, $store);
    }

    public function getCardCode($store = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_SAP_CN, ScopeInterface::SCOPE_STORE, $store);
    }

    public function getUrlStockFast($store = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_WS_URL_STOCK_FAST, ScopeInterface::SCOPE_STORE, $store);
    }

    public function getUser($store = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_WS_SAP_USERNAME, ScopeInterface::SCOPE_STORE, $store);
    }

    public function getPassword($store = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_WS_SAP_PASSWORD, ScopeInterface::SCOPE_STORE, $store);
    }
}
