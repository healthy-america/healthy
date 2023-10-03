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
use Aventi\Prehome\Helper\SetDeleteCookie;

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

    /**
     * @var SetDeleteCookie
     */
    private SetDeleteCookie $setDeleteCookie;

    public function __construct(
        Context $context,
        JsonFactory $jsonResultFactory,
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        StoreManagerInterface $storeManager,
        SetDeleteCookie $setDeleteCookie
    ) {
        $this->jsonResultFactory = $jsonResultFactory;
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->storeManager = $storeManager;
        $this->setDeleteCookie = $setDeleteCookie;
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

        $this->setDeleteCookie->setCookieByStores($cookieName, $cookieValue);

        $result->setData(['success' => true]);
        return $result;
    }
}
