<?php

namespace Aventi\Prehome\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Store\Model\StoreManagerInterface;

class SetCookie extends Action
{
    /**
     * @var JsonFactory
     */
    protected JsonFactory $jsonResultFactory;
    /**
     * @var CookieManagerInterface
     */
    protected CookieManagerInterface $cookieManager;
    /**
     * @var CookieMetadataFactory
     */
    protected CookieMetadataFactory $cookieMetadataFactory;
    /**
     * @var StoreManagerInterface
     */
    protected StoreManagerInterface $storeManager;

    public function __construct(
        Context $context,
        JsonFactory $jsonResultFactory,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->jsonResultFactory = $jsonResultFactory;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->storeManager = $storeManager;
        parent::__construct($context);
    }

    /**
     * @throws FailureToSendException
     * @throws InputException
     * @throws CookieSizeLimitReachedException
     */
    public function execute()
    {

        $result = $this->jsonResultFactory->create();

        $cookieName = $this->getRequest()->getParam('name');
        $cookieValue = $this->getRequest()->getParam('value');

        $stores = $this->storeManager->getStores();
        foreach ($stores as $store) {
            $domain = parse_url(rtrim($store->getBaseUrl(), "/"));
            $cookieMetadata = $this->cookieMetadataFactory->createPublicCookieMetadata()
                ->setPath("/")
                ->setHttpOnly(false);
            if (isset($domain['host'])) {
                $cookieMetadata->setDomain($domain['host']);
            }

            $this->cookieManager->setPublicCookie($cookieName, $cookieValue, $cookieMetadata);
        }

        $result->setData(['success' => true]);
        return $result;
    }
}
