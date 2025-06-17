<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\PriceLists\Plugin\Magento\Catalog\Api;

use Aventi\PriceLists\Model\PriceListData;
use Magento\Catalog\Api\Data\ProductExtensionFactory;
use Magento\Catalog\Api\Data\ProductExtensionInterface;
use Magento\Catalog\Api\Data\ProductInterface;
use Psr\Log\LoggerInterface;

class ProductRepositoryInterface
{
    /**
     * @var PriceListData
     */
    protected $priceListData;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * Order Extension Attributes Factory
     *
     * @var ProductExtensionFactory
     */
    protected $extensionFactory;

    /**
     * @param PriceListData $priceListData
     * @param ProductExtensionFactory $extensionFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        PriceListData           $priceListData,
        ProductExtensionFactory $extensionFactory,
        LoggerInterface         $logger
    ) {
        $this->priceListData = $priceListData;
        $this->extensionFactory = $extensionFactory;
        $this->logger = $logger;
    }

    public function afterGetById(
        \Magento\Catalog\Api\ProductRepositoryInterface $subject,
        $result
    ) {
        return $this->afterGet($subject, $result);
    }

    public function afterGet(
        \Magento\Catalog\Api\ProductRepositoryInterface $subject,
        ProductInterface $result
    ) {
        if ($this->priceListData->getGeneralConfig('enable')) {
            //$priceInList = $this->priceListData->getProductPrice($result, $result->getPrice());
            /** @var ProductExtensionInterface $extension */
            $extensionAttributes = $result->getExtensionAttributes();
            $extensionAttributes = $extensionAttributes ? $extensionAttributes : $this->extensionFactory->create();
            $extensionAttributes->setCustomPrice($result->getPrice());
            $extensionAttributes->setOriginalPrice($result->getPrice());
            $result->setExtensionAttributes($extensionAttributes);
        }

        return $result;
    }
}
