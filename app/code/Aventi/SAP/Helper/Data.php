<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\DateTime\DateTime;

class Data extends AbstractHelper
{
    const ROW_START = 0;
    const ROW_END = 1000;

    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    private \Magento\Framework\HTTP\Client\Curl $curl;

    /**
     * @var \Aventi\SAP\Logger\Logger
     */
    private \Aventi\SAP\Logger\Logger $logger;

    /**
     * @var Configuration
     */
    private Configuration $configHelper;

    private ?string $_token = null;

    /**
     * @var DateTime
     */
    private DateTime $_dateTime;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private \Magento\Framework\App\ResourceConnection $resourceConnection;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Framework\HTTP\Client\Curl $curl
     * @param \Aventi\SAP\Logger\Logger $logger
     * @param Configuration $configHelper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $dateTime
     * @param \Magento\Framework\App\ResourceConnection $resourceConnection
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\HTTP\Client\Curl $curl,
        \Aventi\SAP\Logger\Logger $logger,
        Configuration $configHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Framework\App\ResourceConnection $resourceConnection
    ) {
        parent::__construct($context);
        $this->curl = $curl;
        $this->logger = $logger;
        $this->configHelper = $configHelper;
        $this->_dateTime = $dateTime;
        $this->resourceConnection = $resourceConnection;
    }

    /**
     * @param $typeUri
     * @param $start
     * @param $rows
     * @param $fast
     * @return false|string
     */
    public function getResource($typeUri, $start, $rows, $fast)
    {
        $mainUri = $this->configHelper->getUrlWS();
        try {
            if ($mainUri) {
                $url = $this->formatUrl($mainUri, $typeUri, $start, $rows, $fast);
                $this->logger->debug($url);
                $headers = [
                    "Content-Type" => "application/json"
                ];
                $this->curl->setHeaders($headers);
                $this->curl->get($url);
                if ($this->curl->getStatus() == 200) {
                    return $this->curl->getBody();
                } else {
                    throw new LocalizedException(__("Error in connection to server: " . $this->curl->getBody()));
                }
            } else {
                throw new LocalizedException(__("Main url webservice undefined."));
            }
        } catch (LocalizedException $exception) {
            $this->logger->error(__('An error has occurred: ' . $exception->getMessage()));
        }
        return false;
    }

    /**
     * @param $typeUri
     * @param $params
     * @return array|false
     */
    public function postResource($typeUri, $params)
    {
        $mainUri = $this->configHelper->getUrlWS();
        try {
            if ($mainUri) {
                $url = $this->formatUrl($mainUri, $typeUri);
//                if ($this->getToken() === null) {
//                    $this->generateToken();
//                }
                $headers = [
                    "Content-Type" => "application/json"
//                    "Authorization" => "Bearer {$this->getToken()}"
                ];
//                $this->curl->setHeaders($headers);
                $this->curl->post($url, $params);
                return [
                    "status" => $this->curl->getStatus(),
                    "body" => $this->curl->getBody()
                ];
            } else {
                throw new LocalizedException(__("Main url webservice undefined."));
            }
        } catch (LocalizedException $exception) {
            $this->logger->error(__('An error has occurred: ' . $exception->getMessage()));
        }

        return false;
    }

    /**
     * Returns formatted url.
     * @param $mainUri
     * @param $type
     * @param null $start
     * @param null $rows
     * @param bool $fast
     * @return string
     * @throws LocalizedException
     */
    private function formatUrl($mainUri, $type, $start = null, $rows = null, bool $fast = false): string
    {
        switch ($type) {
            case 'price':
                if ($fast) {
                    $uri = $this->configHelper->getUrlPriceFast();
                }else{
                    $uri = $this->configHelper->getUrlPrice();
                }
                $uri .= $start . "/" . $rows;
                break;
            case 'product':
                if ($fast) {
                    $uri = $this->configHelper->getUrlProductsFast();
                } else {
                    $uri = $this->configHelper->getUrlProducts();
                }
                $uri.= $start . "/" . $rows;
                break;
            case 'stock':
                if ($fast) {
                    $uri = $this->configHelper->getUrlStockFast();
                } else {
                    $uri = $this->configHelper->getUrlStock();
                }
                $uri.= $start . "/" . $rows;
                break;
            case 'order':
                $uri = $this->configHelper->getUrlOrder();
                break;
            case 'brand':
                $uri = $this->configHelper->getUrlBrand();
                break;
            case 'category':
                $uri = $this->configHelper->getUrlCategory();
                break;
            default:
                throw new LocalizedException(__("Option undefined"));
        }

        if ($uri === null || $uri === '') {
            throw new LocalizedException(__("The " . $type . " ws url is not set in admin configuration."));
        }

        return $mainUri . $uri;
    }

    /**
     * Returns generated token by SAP WS.
     * @return string|null
     */
    public function getToken(): ?string
    {
        return $this->_token;
    }

    public function setToken($token)
    {
        $this->_token = $token;
    }

    private function generateToken()
    {
        $mainUri = $this->configHelper->getUrlWS();
        try {
            if ($mainUri) {
                $url = $mainUri . 'token';
                $params = [
                    'Username' => $this->configHelper->getUser(),
                    'Password' => $this->configHelper->getPassword(),
                    'grant_type' => 'password'
                ];
                $this->curl->post($url, $params);
                $response = json_decode($this->curl->getBody());
                if ($this->curl->getStatus() != 400) {
                    $this->setToken($response->access_token);
                }
            } else {
                throw new LocalizedException(__("Main url webservice undefined."));
            }
        } catch (LocalizedException $exception) {
            $this->logger->error(__('An error has occurred: ' . $exception->getMessage()));
        }
    }

    /**
     * @param $sap
     * @param $companyId
     * @return float|int|string|null
     */
    public function getCompanyId($sap, $companyId = null): float|int|string|null
    {
        $id = null;
        $connection = $this->resourceConnection->getConnection();
        if (is_numeric($companyId)) {
            $sql = 'UPDATE aw_ca_company  SET `sap` = "' . addslashes($sap) . '" WHERE  id = ' . (int)$companyId;
            $connection->query($sql);
            $id  = $companyId;
        } else {
            $sql = 'SELECT id from aw_ca_company where `sap` = "' . addslashes($sap) . '"';
            $id = $connection->fetchOne($sql);
            $id = is_numeric($id) ? $id : null;
        }
        return $id;
    }

    /**
     * @param $address
     * @param $customerId
     * @param $addressId
     * @return float|int|string|null
     */
    public function getCustomerAddressSAP($address, $customerId = null, $addressId = null)
    {
        $id = null;

        $connection = $this->resourceConnection->getConnection();

        if (is_numeric($addressId)) {
            $sql = 'UPDATE customer_address_entity  SET `sap` = "' . addslashes($address) . '" WHERE  entity_id = ' . (int)$addressId;
            $connection->query($sql);
            $id  = $addressId;
        } else {
            $sql = 'SELECT entity_id from customer_address_entity where `sap` = "' . addslashes($address) . '" AND `parent_id` = "' . addslashes($customerId) . '"';
            $id = $connection->fetchOne($sql);
            $id = is_numeric($id) ? $id : null;
        }

        return $id;
    }
}
