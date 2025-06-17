<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\PriceLists\Rewrite\MGS\InstantSearch\Controller\Ajax;

use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Exception\NoSuchEntityException;

class Result extends \MGS\InstantSearch\Controller\Ajax\Result
{
    /**
     * @return \Magento\Framework\Controller\Result\Json
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        $responseData = [];
        if ($this->catalogIsVisible()) {
            /* @var $query \Magento\Search\Model\Query */
            $query = $this->_queryFactory->get();
            $query->setStoreId($this->_storeManager->getStore()->getId());
            if ($query->getQueryText() != '') {
                $query->setId(0)->setIsActive(1)->setIsProcessed(1);
                $responseData['result'] = $this->_search->getData();
            }
        }
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
        $resultJson->setData($responseData);
        return $resultJson;
    }

    /**
     * @return bool
     */
    private function catalogIsVisible(): bool
    {
        $factory = \Magento\Framework\App\ObjectManager::getInstance();
        $validator = $factory->create(\Aventi\PriceLists\Helper\Validator::class);
        return $validator->catalogIsVisible();
    }
}
