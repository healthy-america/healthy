<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\PriceLists\Observer\AdditionalRestrictions;

use Aventi\PriceLists\Helper\Validator;
use Aventi\PriceLists\Model\PriceListData;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ProductObserver implements ObserverInterface
{
    /**
     * @var Validator
     */
    private Validator $validator;

    /**
     * @var PriceListData
     */
    private PriceListData $priceListData;

    /**
     * @param Validator $validator
     * @param PriceListData $priceListData
     */
    public function __construct(
        Validator $validator,
        PriceListData $priceListData
    ) {
        $this->validator = $validator;
        $this->priceListData = $priceListData;
    }

    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        if (!$this->validator->catalogIsVisible()) {
            $product = $observer->getEvent()->getProduct();
            $product->setVisibility(false);
        }
    }
}
