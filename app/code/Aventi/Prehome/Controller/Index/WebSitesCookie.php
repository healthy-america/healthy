<?php
/**
  * Copyright  Aventi SAS All rights reserved.
  * See COPYING.txt for license details.
*/
declare(strict_types=1);

namespace Aventi\Prehome\Controller\Index;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Controller\ResultFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Controller\Result\Json;

/**
 * @class WebSitesCookie
 */
class WebSitesCookie implements HttpGetActionInterface, HttpPostActionInterface
{
    /**
     * @constructor
     *
     * @param ResultFactory $resultFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        private readonly ResultFactory $resultFactory,
        private readonly StoreManagerInterface $storeManager
    ) {
    }

    /**
     * @inheritDoc
     *
     * @return Json
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $defaultStoreUrl = $this->getUrlStoreDefault();
        $isWebSiteDefault = $this->isWebsiteDefault();

        return $this->responseJson([
            'default_store_url' => $defaultStoreUrl,
            'isWebsite' => $isWebSiteDefault
        ]);
    }

    /**
     * Get url base of store view default.
     *
     * @return string
     */
    private function getUrlStoreDefault(): string
    {
        $defaultStoreView = $this->storeManager->getDefaultStoreView();
        return  $defaultStoreView->getBaseUrl();
    }

    /**
     * Generate response json
     *
     * @param array $data
     * @return Json
     */
    private function responseJson(array $data)
    {
        $jsonResponse = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        return $jsonResponse->setData($data);
    }

    /**
     * Get url base of store view active.
     *
     * @return string
     * @throws NoSuchEntityException
     */
    private function getActiveStoreUrl(): string
    {
        $activeStore = $this->storeManager->getStore();
        return $activeStore->getBaseUrl();
    }

    /**
     * Validate if active website default
     *
     * @return bool
     * @throws NoSuchEntityException
     */
    private function isWebsiteDefault(): bool
    {
        return $this->getActiveStoreUrl() === $this->getUrlStoreDefault();
    }
}