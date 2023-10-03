<?php

namespace Aventi\Prehome\Controller\Index;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Stdlib\Cookie\CookieSizeLimitReachedException;
use Magento\Framework\Stdlib\Cookie\FailureToSendException;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\Cookie\CookieMetadataFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Store\Model\StoreManagerInterface;
use Aventi\Prehome\Helper\SetDeleteCookie;
use Psr\Log\LoggerInterface;

class DeleteCookie implements HttpGetActionInterface
{
    /**
     * @var CookieManagerInterface
     */
    private CookieManagerInterface $cookieManager;

    /**
     * @var CookieMetadataFactory
     */
    private CookieMetadataFactory $cookieMetadataFactory;

    /**
     * @var RequestInterface
     */
    private RequestInterface $request;

    /**
     * @var ResultFactory
     */
    private ResultFactory $resultFactory;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var SetDeleteCookie
     */
    private SetDeleteCookie $setDeleteCookie;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param CookieManagerInterface $cookieManager
     * @param CookieMetadataFactory $cookieMetadataFactory
     * @param RequestInterface $request
     * @param ResultFactory $resultFactory
     * @param StoreManagerInterface $storeManager
     * @param SetDeleteCookie $setDeleteCookie
     * @param LoggerInterface $logger
     */
    public function __construct(
        CookieManagerInterface $cookieManager,
        CookieMetadataFactory $cookieMetadataFactory,
        RequestInterface $request,
        ResultFactory $resultFactory,
        StoreManagerInterface $storeManager,
        SetDeleteCookie $setDeleteCookie,
        LoggerInterface $logger
    ) {
        $this->cookieManager = $cookieManager;
        $this->cookieMetadataFactory = $cookieMetadataFactory;
        $this->request = $request;
        $this->resultFactory = $resultFactory;
        $this->storeManager = $storeManager;
        $this->setDeleteCookie = $setDeleteCookie;
        $this->logger = $logger;
    }

    /**
     * @return Json
     * @throws FailureToSendException
     * @throws InputException
     * @throws CookieSizeLimitReachedException
     */
    public function execute()
    {
        $resultRedirect = $this->createResultRedirect();
        $cookieName = $this->getCookieName();

        $this->setDeleteCookie->setCookieByStores($cookieName, null, false);

        return $resultRedirect->setPath('');
    }

    /**
     * @return string
     */
    public function getCookieName(): string
    {
        return trim($this->request->getParam('name'), '/');
    }

    /**
     * @return \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function createResultRedirect(): \Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
    {
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
    }
}
