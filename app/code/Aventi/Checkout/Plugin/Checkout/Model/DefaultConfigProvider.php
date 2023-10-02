<?php

declare(strict_types=1);

namespace Aventi\Checkout\Plugin\Checkout\Model;

use Aventi\Checkout\Helper\Discount;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

class DefaultConfigProvider
{
    /**
     * @var Discount
     */
    private Discount $discountHelper;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param Discount $discountHelper
     * @param LoggerInterface $logger
     */
    public function __construct(
        Discount $discountHelper,
        LoggerInterface $logger
    ) {
        $this->discountHelper = $discountHelper;
        $this->logger = $logger;
    }

    /**
     * @param $subject
     * @param $result
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function afterGetConfig($subject, $result)
    {
        $result['totalsData']['discount_amount'] = $this->discountHelper->getDiscountAmount();
        return $result;
    }
}
