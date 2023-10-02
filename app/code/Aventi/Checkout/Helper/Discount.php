<?php

declare(strict_types=1);

namespace Aventi\Checkout\Helper;

class Discount
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected \Magento\Checkout\Model\Session $checkoutSession;

    /**
     * @var \Magento\Checkout\Helper\Data
     */
    protected \Magento\Checkout\Helper\Data $checkoutHelper;

    /**
     * @var \Magento\Quote\Model\Quote|\Magento\Quote\Api\Data\CartInterface
     */
    protected \Magento\Quote\Model\Quote|\Magento\Quote\Api\Data\CartInterface $quote;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Checkout\Helper\Data $checkoutHelper
     * @param \Magento\Quote\Model\Quote $quote
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Checkout\Helper\Data $checkoutHelper,
        \Magento\Quote\Model\Quote $quote
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->checkoutHelper = $checkoutHelper;
        $this->quote = $quote;
    }

    /**
     * @return \Magento\Quote\Api\Data\CartInterface|\Magento\Quote\Model\Quote
     */
    public function getQuote(): \Magento\Quote\Api\Data\CartInterface|\Magento\Quote\Model\Quote
    {
        return $this->quote;
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
        foreach ($quote->getAllVisibleItems() as $item) {
            $discount = $item->getProduct()->getPrice() - $item->getPrice();
            $discount *= $item->getQty();
            $discountAmount += $discount;
        }
        return ($discountAmount < 0 ? 0 : $discountAmount);
    }
}
