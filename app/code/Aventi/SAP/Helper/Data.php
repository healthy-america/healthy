<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Helper;

use Aventi\SAP\Logger\Logger;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Store\Api\WebsiteRepositoryInterface;

/**
 * Class Data
 */
class Data extends AbstractHelper
{
    protected ?string $_token = null;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Curl $curl
     * @param Logger $logger
     * @param Configuration $configHelper
     * @param WebsiteRepositoryInterface $websiteRepository
     */
    public function __construct(
        Context                             $context,
        private readonly Curl               $curl,
        private readonly Logger             $logger,
        private readonly Configuration      $configHelper,
        private readonly WebsiteRepositoryInterface $websiteRepository
    ) {
        parent::__construct($context);
    }

    /**
     * GetResource
     *
     * @param $typeUri
     * @param $start
     * @param $rows
     * @param $fast
     * @return false|string
     */
    public function getResource($typeUri, $start, $rows, $fast): bool|string
    {
        $mainUri = $this->configHelper->getUrlWS();
        try {
            if ($mainUri) {
                $url = $this->formatUrl($mainUri, $typeUri, $start, $rows, $fast);
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
     * PostResource
     *
     * @param $typeUri
     * @param $params
     * @return array|false
     */
    public function postResource($typeUri, $params): bool|array
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
                $this->curl->setHeaders($headers);
                $this->curl->post($url, json_encode($params));
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
     *
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
                } else {
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
            case 'customer':
                if ($fast) {
                    $uri = $this->configHelper->getUrlCustomersFast();
                } else {
                    $uri = $this->configHelper->getUrlCustomers();
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
     *
     * @return string|null
     */
    public function getToken(): ?string
    {
        return $this->_token;
    }

    /**
     * SetToken
     *
     * @param $token
     * @return void
     */
    public function setToken($token): void
    {
        $this->_token = $token;
    }

    /**
     * GenerateToken
     *
     * @return void
     */
    private function generateToken(): void
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
     * GetWebsiteIds
     *
     * @param $websiteCode
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getWebsiteIds($websiteCode): mixed
    {
        return match ($websiteCode) {
            'HEALTHY SPORTS' => $this->websiteRepository->get('healthy_sports')->getId(),
            'NUTRIVITA' => $this->websiteRepository->get('nutrivita')->getId(),
            default => $this->websiteRepository->get('base')->getId(),
        };
    }

    /**
     * GetWebsiteName
     *
     * @param $websiteId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getWebsiteName($websiteId): string
    {
        $websiteCode = $this->websiteRepository->getById($websiteId)->getCode();

        return match ($websiteCode) {
            'healthy_sports' => 'HEALTHY SPORTS',
            'nutrivita' => 'NUTRIVITA',
            default => 'HEALTHY',
        };
    }
}
