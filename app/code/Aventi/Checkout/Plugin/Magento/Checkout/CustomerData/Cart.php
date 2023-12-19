<?php
/**
 * Copyright © 2023 Aventi SAS. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\Checkout\Plugin\Magento\Checkout\CustomerData;

use Aventi\Checkout\Helper\Discount;

class Cart
{
    /**
     * @param Discount $discountHelper
     */
    public function __construct(
        private Discount $discountHelper,
    ) {}

    /**
     * @param \Magento\Checkout\CustomerData\Cart $subject
     * @param $result
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGetSectionData(\Magento\Checkout\CustomerData\Cart $subject, $result)
    {
        $result['discount_amount'] = $this->discountHelper->getDiscountAmount();
        return $result;
    }
}
