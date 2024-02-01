<?php
/**
  * Copyright  Aventi SAS All rights reserved.
  * See COPYING.txt for license details.
*/

declare(strict_types=1);

namespace Aventi\Prehome\Controller\Index;

use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Controller\Result\Json;

/**
 *  Class WebSitesCookie
 */
class WebSitesCookie implements HttpGetActionInterface , HttpPostActionInterface
{
    /**
     *  Construct
     *
     * @param PageFactory $pageFactory
     */
    public function __construct(
        private PageFactory $pageFactory,
        private RequestInterface $request,
        private ResultFactory $resultFactory,
        private StoreManagerInterface $storeManager
    ) {
    }

    /**
     * Default category list view
     *
     * @return ResponseInterface
     */
    public function execute()
    {
      $defaultStoreUrl = $this->getUrlStoreDefault();
      return $this->responseJson(['default_store_url' => $defaultStoreUrl]);
    }

    /**
     * Get url base of store view default.
     * 
     * @return string
     */
    private function getUrlStoreDefault() : string
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
}