<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\PriceLists\Plugin\Magento\Catalog\Block\Product;

use \Aventi\PriceLists\Model\PriceListData;
use Psr\Log\LoggerInterface;

class ListProduct
{

    /**
     * @var PriceListData
     */
    protected $priceListData;
    /**
     * @var \Magento\Catalog\Model\Layer\Resolver
     */
    private $layerResolver;

    /**
     * @var \Magento\Customer\Model\SessionFactory
     */
    private $customerSession;

    /**
     * @var LoggerInterface
     */
    protected $logger;

    /**
     * @param PriceListData $priceListData
     * @param LoggerInterface $logger
     */
    public function __construct(
        PriceListData   $priceListData,
        LoggerInterface $logger
    ) {
        $this->priceListData = $priceListData;
        $this->logger = $logger;
    }

    /**
     * @param $subject
     * @param $result
     * @return mixed
     */
    public function afterGetProductCollection(
        $subject,
        $result
    ) {
        if (
            $this->priceListData->getGeneralConfig('restrict_product_lists')
            &&
            $this->priceListData->getGeneralConfig('enable')
        ) {
            $productIds = $this->priceListData->getEntityId();
            $ids = $result->getAllIds();
            $result->addFieldToFilter('entity_id', ['in' => array_intersect($productIds ?? [], $ids ?? [])]);
        }
        return $result;
    }

    public function getCurrentCategory()
    {
        return $this->layerResolver->get()->getCurrentCategory();
    }
}
