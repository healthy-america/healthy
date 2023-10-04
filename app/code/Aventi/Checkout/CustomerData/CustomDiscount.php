<?php
/**
 * Copyright © Aventi All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\Checkout\CustomerData;

use Aventi\Checkout\Helper\Discount;
use Magento\Customer\CustomerData\SectionSourceInterface;
use Psr\Log\LoggerInterface;

class CustomDiscount implements SectionSourceInterface
{

    /**
     * @var LoggerInterface
     */
    protected LoggerInterface $logger;

    /**
     * @var Discount
     */
    private Discount $discountHelper;

    /**
     * @param LoggerInterface $logger
     * @param Discount $discountHelper
     */
    public function __construct(
        LoggerInterface $logger,
        Discount $discountHelper
    ) {
        $this->logger = $logger;
        $this->discountHelper = $discountHelper;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSectionData()
    {
        return [
            'custom_discount' => $this->discountHelper->getDiscountAmount()
        ];
    }
}
