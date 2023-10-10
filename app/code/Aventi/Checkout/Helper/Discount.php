<?php

declare(strict_types=1);

namespace Aventi\Checkout\Helper;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

class Discount
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private \Magento\Checkout\Model\Session $checkoutSession;

    /**
     * @var \Magento\Quote\Model\Quote|\Magento\Quote\Api\Data\CartInterface
     */
    private \Magento\Quote\Model\Quote|\Magento\Quote\Api\Data\CartInterface $quote;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @param \Magento\Quote\Model\Quote $quote
     * @param LoggerInterface $logger
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Model\Quote $quote,
        LoggerInterface $logger
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->quote = $quote;
        $this->logger = $logger;
    }

    /**
     * @return \Magento\Quote\Api\Data\CartInterface|\Magento\Quote\Model\Quote
     */
    public function getQuote()
    {
        $quote = $this->quote;

        if (count($quote->getAllVisibleItems()) === 0) {
            try {
                return $this->checkoutSession->getQuote();
            } catch (NoSuchEntityException|LocalizedException $e) {
                $this->logger->error('There was an error getting the quote: ' . $e->getMessage());
            }
        } else {
            return $quote;
        }
    }

    /**
     * @return float|int|mixed|null
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getDiscountAmount(): mixed
    {
        $discountAmount = 0;
        $quote = $this->getQuote();

        foreach ($quote->getItemsCollection() as $item) {
            $discount = $item->getProduct()->getPrice() - $item->getPrice();
            $discount *= $item->getQty();
            $discountAmount += $discount;
        }

        return (max($discountAmount, 0));
    }
}
