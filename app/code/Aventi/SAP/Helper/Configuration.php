<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Configuration
 */
class Configuration extends AbstractHelper
{
    /** Constants connection settings definition. */

    const XML_PATH_WS_SAP_PATH = 'integration/settings/url';
    const XML_PATH_WS_SAP_USERNAME = 'integration/settings/user';
    const XML_PATH_WS_SAP_PASSWORD = 'integration/settings/password';
    const XML_PATH_WS_URL_PRODUCT = 'integration/settings/ws_products_url';
    const XML_PATH_WS_URL_PRODUCT_FAST = 'integration/settings/ws_products_fast_url';
    const XML_PATH_WS_URL_CUSTOMER = 'integration/settings/ws_customer_url';
    const XML_PATH_WS_URL_CUSTOMER_FAST = 'integration/settings/ws_customer_fast_url';
    const XML_PATH_WS_URL_BRAND = 'integration/settings/ws_brand_url';
    const XML_PATH_WS_URL_STOCK = 'integration/settings/ws_stock_url';
    const XML_PATH_WS_URL_PRICE = 'integration/settings/ws_price_url';
    const XML_PATH_WS_URL_PRICE_FAST = 'integration/settings/ws_price_fast_url';
    const XML_PATH_WS_URL_ORDERS = 'integration/settings/ws_orders_url';
    const XML_PATH_WS_URL_STOCK_FAST = 'integration/settings/ws_stock_fast_url';

    /** End constants connection settings definition. */

    const XML_PATH_SAP_WHSCODE = 'integration/document/whscode';
    const XML_PATH_SAP_SHIPPING = 'integration/document/shipping';
    const XML_PATH_SAP_CN = 'integration/document/cardcode';
    const XML_PATH_SAP_SLPCODE = 'integration/document/slpcode';
    const XML_PATH_SAP_OCRCODE2 = 'integration/document/ocrcode2';
    const XML_PATH_SAP_OCRCODE3 = 'integration/document/ocrcode3';
    const XML_PATH_SAP_SERIE = 'integration/document/serie';
    const XML_PATH_SAP_LISTNUM = 'integration/document/listnum';
    const XML_PATH_SAP_GROUPCODE = 'integration/document/groupcode';
    const XML_PATH_SAP_GROUPNUM = 'integration/document/groupnum';
    const XML_PATH_SAP_TERRITORY = 'integration/document/territory';

    /**
     * Get URL WS
     *
     * @param $store
     * @return mixed
     */
    public function getUrlWS($store = null): mixed
    {
        return $this->scopeConfig->getValue(self::XML_PATH_WS_SAP_PATH, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * Get URL Products
     *
     * @param $store
     * @return mixed
     */
    public function getUrlProducts($store = null): mixed
    {
        return $this->scopeConfig->getValue(self::XML_PATH_WS_URL_PRODUCT, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * Get URL Products Fast
     *
     * @param $store
     * @return mixed
     */
    public function getUrlProductsFast($store = null): mixed
    {
        return $this->scopeConfig->getValue(self::XML_PATH_WS_URL_PRODUCT_FAST, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * Get URL Products
     *
     * @param $store
     * @return mixed
     */
    public function getUrlCustomers($store = null): mixed
    {
        return $this->scopeConfig->getValue(self::XML_PATH_WS_URL_CUSTOMER, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * Get URL Products Fast
     *
     * @param $store
     * @return mixed
     */
    public function getUrlCustomersFast($store = null): mixed
    {
        return $this->scopeConfig->getValue(self::XML_PATH_WS_URL_CUSTOMER_FAST, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * Get URL Stock
     *
     * @param $store
     * @return mixed
     */
    public function getUrlStock($store = null): mixed
    {
        return $this->scopeConfig->getValue(self::XML_PATH_WS_URL_STOCK, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * Get URL Price
     *
     * @param $store
     * @return mixed
     */
    public function getUrlPrice($store = null): mixed
    {
        return $this->scopeConfig->getValue(self::XML_PATH_WS_URL_PRICE, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * Get URL Price Fast
     *
     * @param $store
     * @return mixed
     */
    public function getUrlPriceFast($store = null): mixed
    {
        return $this->scopeConfig->getValue(self::XML_PATH_WS_URL_PRICE_FAST, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * Get URL Order
     *
     * @param $store
     * @return mixed
     */
    public function getUrlOrder($store = null): mixed
    {
        return $this->scopeConfig->getValue(self::XML_PATH_WS_URL_ORDERS, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * Get Warehouse Code
     *
     * @param $store
     * @return mixed
     */
    public function getWhsCode($store = null): mixed
    {
        return $this->scopeConfig->getValue(self::XML_PATH_SAP_WHSCODE, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * Get Shipping Code
     *
     * @param $store
     * @return mixed
     */
    public function getShippingCode($store = null): mixed
    {
        return $this->scopeConfig->getValue(self::XML_PATH_SAP_SHIPPING, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * Get Card Code
     *
     * @param $store
     * @return mixed
     */
    public function getCardCode($store = null): mixed
    {
        return $this->scopeConfig->getValue(self::XML_PATH_SAP_CN, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * Get SLP Code
     *
     * @param $store
     * @return mixed
     */
    public function getSlpCode($store = null): mixed
    {
        return $this->scopeConfig->getValue(self::XML_PATH_SAP_SLPCODE, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * Get OCR Code 2
     *
     * @param $store
     * @return mixed
     */
    public function getOcrCode2($store = null): mixed
    {
        return $this->scopeConfig->getValue(self::XML_PATH_SAP_OCRCODE2, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * Get OCR Code 3
     *
     * @param $store
     * @return mixed
     */
    public function getOcrCode3($store = null): mixed
    {
        return $this->scopeConfig->getValue(self::XML_PATH_SAP_OCRCODE3, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * Get URL Stock Fast
     *
     * @param $store
     * @return mixed
     */
    public function getUrlStockFast($store = null): mixed
    {
        return $this->scopeConfig->getValue(self::XML_PATH_WS_URL_STOCK_FAST, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * Get User
     *
     * @param $store
     * @return mixed
     */
    public function getUser($store = null): mixed
    {
        return $this->scopeConfig->getValue(self::XML_PATH_WS_SAP_USERNAME, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * Get Password
     *
     * @param $store
     * @return mixed
     */
    public function getPassword($store = null): mixed
    {
        return $this->scopeConfig->getValue(self::XML_PATH_WS_SAP_PASSWORD, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * Get Serie
     *
     * @param $store
     * @return mixed
     */
    public function getSerie($store = null): mixed
    {
        return $this->scopeConfig->getValue(self::XML_PATH_SAP_SERIE, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * Get List Num
     *
     * @param $store
     * @return mixed
     */
    public function getListNum($store = null): mixed
    {
        return $this->scopeConfig->getValue(self::XML_PATH_SAP_LISTNUM, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * Get Group code
     *
     * @param $store
     * @return mixed
     */
    public function getGroupCode($store = null): mixed
    {
        return $this->scopeConfig->getValue(self::XML_PATH_SAP_GROUPCODE, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * Get Territory code
     *
     * @param $store
     * @return mixed
     */
    public function getTerritory($store = null): mixed
    {
        return $this->scopeConfig->getValue(self::XML_PATH_SAP_TERRITORY, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * Get Url Brand
     *
     * @param $store
     * @return mixed
     */
    public function getUrlBrand($store = null): mixed
    {
        return $this->scopeConfig->getValue(self::XML_PATH_WS_URL_BRAND, ScopeInterface::SCOPE_STORE, $store);
    }
}
