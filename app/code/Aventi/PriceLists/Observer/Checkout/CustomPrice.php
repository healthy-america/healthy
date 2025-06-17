<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\PriceLists\Observer\Checkout;

use Aventi\PriceLists\Model\PriceListData;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;

class CustomPrice implements ObserverInterface
{
    /**
     * @var PriceListData
     */
    private PriceListData $priceListData;

    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * @param PriceListData $priceListData
     * @param ProductRepositoryInterface $productRepository
     */
    public function __construct(
        PriceListData $priceListData,
        ProductRepositoryInterface $productRepository
    ) {
        $this->priceListData = $priceListData;
        $this->productRepository = $productRepository;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->priceListData->getGeneralConfig('enable')) {
            return;
        }
        $item = $observer->getEvent()->getData('quote_item');
        if ($item->getParentItem()) {
            $parent = $item->getParentItem();
            $this->setPrice($parent);
        }
        foreach ($item->getChildren() as $child) {
            $this->setPrice($child);
        }
        $this->setPrice($item);
    }

    /**
     * @param $item
     * @return void
     * @throws NoSuchEntityException
     */
    private function setPrice($item): void
    {
        $product = $this->productRepository->get($item->getSku());
        $price = $product->getPrice();
        $item->setCustomPrice($price);
        $item->setOriginalCustomPrice($price);
        $item->setFinalPrice($price);
        $item->getProduct()->setIsSuperMode(true);
    }
}
