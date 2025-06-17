<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\PriceLists\Observer\Sales;

use Aventi\PriceLists\Model\PriceListData;
use Magento\Framework\Event\Observer;

class QuoteCollectTotalsBefore implements \Magento\Framework\Event\ObserverInterface
{
    /**
     * @var PriceListData
     */
    private PriceListData $priceListData;

    /**
     * @param PriceListData $priceListData
     */
    public function __construct(
        PriceListData $priceListData
    ) {
        $this->priceListData = $priceListData;
    }

    /**
     * Execute observer
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(
        Observer $observer
    ) {
        if (!$this->priceListData->getGeneralConfig('enable')) {
            return;
        }
        $quote = $observer->getEvent()->getQuote();
        foreach ($quote->getAllItems() as $item) {
            if ($item->getParentItem()) {
                $parent = $item->getParentItem();
                $this->setPrice($parent);
            }
            $this->setPrice($item);
        }
    }

    /**
     * @param $item
     * @return void
     */
    private function setPrice($item): void
    {
        $item->setCustomPrice($item->getCustomPrice());
        $item->setOriginalCustomPrice($item->getOriginalCustomPrice());
        $item->setFinalPrice($item->getFinalPrice());
        $item->getProduct()->setIsSuperMode(true);
    }
}
