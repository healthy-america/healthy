<?php

namespace Aventi\Checkout\Plugin\Magento\Checkout\CustomerData;

class Cart
{
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $checkoutSession;

    /**
     * @var \Magento\Checkout\Helper\Data
     */
    protected $checkoutHelper;

    /**
     * @var \Magento\Quote\Model\Quote|\Magento\Quote\Api\Data\CartInterface
     */
    protected $quote;

    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Checkout\Helper\Data $checkoutHelper
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->checkoutHelper = $checkoutHelper;
    }

    /**
     * @return \Magento\Quote\Api\Data\CartInterface|\Magento\Quote\Model\Quote
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getQuote()
    {
        if (null === $this->quote) {
            $this->quote = $this->checkoutSession->getQuote();
        }
        return $this->quote;
    }

    /**
     * @return float|int
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function getDiscountAmount()
    {
        $discountAmount = 0;
        $quote = $this->getQuote();
        foreach ($quote->getAllVisibleItems() as $item) {
            $discountAmount = $item->getProduct()->getPrice() - $item->getPrice();
            $discountAmount *= $item->getQty();
        }
        return ($discountAmount < 0 ? 0 : $discountAmount);
    }

    /**
     * @param \Magento\Checkout\CustomerData\Cart $subject
     * @param $result
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function afterGetSectionData(\Magento\Checkout\CustomerData\Cart $subject, $result)
    {
        $result['discount_amount'] = $this->checkoutHelper->formatPrice($this->getDiscountAmount());
        return $result;
    }
}
