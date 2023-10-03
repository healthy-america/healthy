<?php

declare(strict_types=1);

namespace Aventi\Prehome\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;

class SetDeleteCookie extends AbstractHelper
{
    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var CookieManagerInterface
     */
    private CookieManagerInterface $cookieManager;

    /**
     * @var CookieMetadataFactory
     */
    private CookieMetadataFactory $cookieMetadataFactory;

    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
    }

    /**
     * @param $cookieName
     * @param $cookieValue
     * @param bool $setCookie
     * @return void
     * @throws CookieSizeLimitReachedException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Stdlib\Cookie\FailureToSendException
     */
    public function setCookieByStores($cookieName, $cookieValue, bool $setCookie = true)
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

            if ($setCookie) {
                $this->cookieManager->setPublicCookie($cookieName, $cookieValue, $cookieMetadata);
            } else {
                $this->cookieManager->deleteCookie($cookieName, $cookieMetadata);
            }
        }
    }
}
