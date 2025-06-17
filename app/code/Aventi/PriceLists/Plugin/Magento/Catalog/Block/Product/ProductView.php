<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\PriceLists\Plugin\Magento\Catalog\Block\Product;

use Aventi\PriceLists\Model\PriceListData;
use Magento\Catalog\Block\Product\View;
use Psr\Log\LoggerInterface;

class ProductView
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
     * Product constructor.
     * @param PriceListData $priceListData
     * @param LoggerInterface $logger
     */
    public function __construct(
        PriceListData $priceListData,
        LoggerInterface  $logger
    )
    {
        $this->priceListData = $priceListData;
        $this->logger = $logger;
    }

    /**
     * @param View $subject
     * @param callable $proceed
     * @return string
     */
    public function aroundGetTemplate(
        $subject,
        callable $proceed
    ) {
        $result = $proceed();
        if ($this->priceListData->getGeneralConfig('enable') &&
            $this->priceListData->getGeneralConfig('change_template') &&
            !$this->priceListData->getGeneralConfig('restrict_product_lists')
        ) {
            if ($subject->getNameInLayout() == 'product.info') {
                $cId = $subject->getProduct()->getId();
                $customerIds =  [];
                if ($this->priceListData->isLoggedInId()) {
                    $customerIds = $this->priceListData->getCustomerProductIds();
                }

                if (!in_array($cId, $customerIds)) {
                    $result =  'Aventi_PriceLists::product/view/form.phtml';
                }
            }
        }

        return $result;
    }
}
