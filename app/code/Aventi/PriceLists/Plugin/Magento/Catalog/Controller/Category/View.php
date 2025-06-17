<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\PriceLists\Plugin\Magento\Catalog\Controller\Category;

use Magento\Customer\Model\SessionFactory as Session;
use Magento\Customer\Model\Url;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Aventi\PriceLists\Model\PriceListData;
use Psr\Log\LoggerInterface;

class View
{

    /**
     * @var Url
     */
    protected $customerUrl;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var PriceListData
     */
    protected $priceListData;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param Url $customerUrl
     * @param Session $customerSession
     * @param PriceListData $priceListData
     * @param LoggerInterface $logger
     */
    public function __construct(
        Url $customerUrl,
        Session $customerSession,
        PriceListData $priceListData,
        LoggerInterface  $logger
    ) {
        $this->customerUrl = $customerUrl;
        $this->customerSession = $customerSession;
        $this->priceListData = $priceListData;
        $this->logger = $logger;
    }

    /**
     * Controller that forces the user to authenticate in order to view categories
     *
     * @param ActionInterface $subject
     * @param RequestInterface $request
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function beforeDispatch(ActionInterface $subject, RequestInterface $request)
    {
        /** Only redirect if enabled in the config */
        if ($this->priceListData->getGeneralConfig('categories_logged_in') &&
            $this->priceListData->getGeneralConfig('enable')
        ) {
            $loginUrl = $this->customerUrl->getLoginUrl();

            if (!$this->customerSession->create()->authenticate($loginUrl)) {
                $subject->getActionFlag()->set('', $subject::FLAG_NO_DISPATCH, true);
            }
        }
    }
}
