<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\PriceLists\Plugin\Magento\Catalog\Model;


use Psr\Log\LoggerInterface;

class Product
{
    /**
     * @var \Aventi\PriceLists\Model\PriceListData
     */
    protected $priceListData;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Product constructor.
     * @param \Aventi\PriceLists\Model\PriceListData $priceListData
     * @param LoggerInterface $logger
     */
    public function __construct(
        \Aventi\PriceLists\Model\PriceListData $priceListData,
        LoggerInterface  $logger
    ) {
        $this->priceListData = $priceListData;
        $this->logger = $logger;
    }

    /**
     * @param \Magento\Catalog\Model\Product $subject
     * @param $result
     * @return int
     */
    public function afterGetPrice(
        \Magento\Catalog\Model\Product $subject,
        $result
    ) {
        if (!$this->priceListData->getGeneralConfig('enable')) {
            return $result;
        }
        $price = $result;
        $newPrice = $this->priceListData->getProductPrice($subject, $price);
        if ($this->priceListData->getGeneralConfig('disable_tier_pricing')) {
            $subject->setTierPrices([]);
        }
        return $newPrice > 0 ? $newPrice : $price;
    }
}
