<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\PriceLists\Observer\AdditionalRestrictions;

use Aventi\PriceLists\Helper\Validator;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CollectionObserver implements ObserverInterface
{
    /**
     * @var Validator
     */
    private Validator $validator;

    /**
     * @param Validator $validator
     */
    public function __construct(
        Validator $validator
    ) {
        $this->validator = $validator;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        if (!$this->validator->catalogIsVisible()) {
            $collection = $observer->getEvent()->getCollection();
            foreach ($collection as $key => $product) {
                $collection->removeItemByKey($key);
            }
        }
    }
}
