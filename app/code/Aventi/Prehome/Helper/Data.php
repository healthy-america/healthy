<?php

declare(strict_types=1);

namespace Aventi\Prehome\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * @class Data
 */
class Data extends AbstractHelper
{
    /**
     * @constructor
     *
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     */
    public function __construct(
        Context                                 $context,
        private readonly StoreManagerInterface  $storeManager,
        private readonly CookieManagerInterface $cookieManager,
        private readonly CookieMetadataFactory  $cookieMetadataFactory
    ) {
        parent::__construct($context);
    }

    /**
     * Set cookie by stores
     *
     * @param $cookieName
     * @param $cookieValue
     * @param bool $setCookie
     * @return void
     * @throws CookieSizeLimitReachedException
     * @throws InputException
     * @throws FailureToSendException
     */
    public function setCookieByStores($cookieName, $cookieValue, bool $setCookie = true): void
    {
        $stores = $this->storeManager->getStores();
        foreach ($stores as $store) {
            $domain = parse_url(rtrim($store->getBaseUrl(), "/"));
            $cookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
                ->setPath("/")
                ->setHttpOnly(false);
            if (isset($domain['host'])) {
                $cookieMetadata->setDomain($domain['host']);
            }

            if ($setCookie && $cookieValue) {
                $this->cookieManager->setPublicCookie($cookieName, $cookieValue, $cookieMetadata);
            } else {
                $this->cookieManager->deleteCookie($cookieName, $cookieMetadata);
            }
        }
    }

    /**
     * Get base url by website
     *
     * @param string $website
     * @return string
     */
    public function getBaseUrlByWebsite(string $website = 'base'): string
    {
        try {
            return $this->storeManager->getWebsite($website)->getDefaultStore()->getBaseUrl();
        } catch (LocalizedException $e) {
            return '';
        }
    }
}
