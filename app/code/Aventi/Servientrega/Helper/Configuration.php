<?php

namespace Aventi\Servientrega\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Configuration extends AbstractHelper
{
    const PATH_URL_TRACKING = 'carriers/servientrega/url_tracking';
    const PATH_USER_NAME = 'carriers/servientrega/username';
    const PATH_USER_PASSWORD = 'carriers/servientrega/password';
    const PATH_BILLING_CODE = 'carriers/servientrega/billing_code';
    const PATH_URL_WEBSERVICE = 'carriers/servientrega/url_webservice';
    const PATH_CLIENT_ID = 'carriers/servientrega/id_client';
    const PATH_MODE = 'carriers/servientrega/mode';
    const PATH_ALLOW_PDF = 'carriers/servientrega/allow_pdf';
    const PATH_ALLOW_FREE_SHIPPING = 'carriers/servientrega/free_shipping';
    const PATH_FREE_SHIPPING_AMOUNT = 'carriers/servientrega/free_shipping_rule';
    const PATH_ALLOWED_REGIONS = 'carriers/servientrega/allow_regions';

    const NAMESPACE_GUIDES = 'http://tempuri.org/';
    const DIR_PDF_FILES = 'pub/servientrega/';
    const URL_MOBILE_TRACKING = 'https://mobile.servientrega.com/WebSitePortal/RastreoEnvioDetalle.html?Guia=';

    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    /**
     * @param null $store
     * @return string
     */
    public function getMode($store = null): string
    {
        return '_' . $this->scopeConfig->getValue(self::PATH_MODE, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getUrlTracking($store = null)
    {
        return $this->scopeConfig->getValue(self::PATH_URL_TRACKING, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getUrlWebservice($store = null)
    {
        return $this->scopeConfig->getValue(self::PATH_URL_WEBSERVICE . $this->getMode(), ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getUserName($store = null)
    {
        return $this->scopeConfig->getValue(self::PATH_USER_NAME . $this->getMode(), ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getUserPassword($store = null)
    {
        return $this->scopeConfig->getValue(self::PATH_USER_PASSWORD . $this->getMode(), ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getClientID($store = null)
    {
        return $this->scopeConfig->getValue(self::PATH_CLIENT_ID . $this->getMode(), ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getBillingCode($store = null)
    {
        return $this->scopeConfig->getValue(self::PATH_BILLING_CODE . $this->getMode(), ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * @return string
     */
    public function getNameSpacesGuide(): string
    {
        return self::NAMESPACE_GUIDES;
    }

    /**
     * Retrieves the path where guides are saved.
     *
     * @return string
     */
    public function getPDFPath(): string
    {
        return $this->_urlBuilder->getBaseUrl() . self::DIR_PDF_FILES;
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function allowSavePDF($store = null)
    {
        return $this->scopeConfig->getValue(self::PATH_ALLOW_PDF, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function isFreeShipping($store = null)
    {
        return $this->scopeConfig->getValue(self::PATH_ALLOW_FREE_SHIPPING, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getFreeAmount($store = null)
    {
        return $this->scopeConfig->getValue(self::PATH_FREE_SHIPPING_AMOUNT, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * @return string
     */
    public function getURLMTrack(): string
    {
        return self::URL_MOBILE_TRACKING;
    }

    /**
     * @param null $store
     * @return mixed
     */
    public function getAllowRegions($store = null)
    {
        return $this->scopeConfig->getValue(self::PATH_ALLOWED_REGIONS, ScopeInterface::SCOPE_STORE, $store);
    }
}
