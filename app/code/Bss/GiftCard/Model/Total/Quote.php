<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_GiftCard
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\GiftCard\Model\Total;

use Bss\GiftCard\Model\Pattern\CodeFactory;
use Bss\GiftCard\Model\ResourceModel\GiftCard\QuoteFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote\Address;
use Magento\Quote\Model\Quote\Address\Total\AbstractTotal;
use Magento\Quote\Model\Quote\Address\Total as QuoteTotal;
use Magento\Quote\Model\Quote as CartQuote;
use Magento\Store\Model\StoreManagerInterface;

class Quote extends AbstractTotal
{

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var QuoteFactory
     */
    private $giftCardQuoteFactory;

    /**
     * @var CodeFactory
     */
    private $codeFactory;

    /**
     * Quote constructor.
     * @param StoreManagerInterface $storeManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param QuoteFactory $giftCardQuoteFactory
     * @param CodeFactory $codeFactory
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        PriceCurrencyInterface $priceCurrency,
        QuoteFactory $giftCardQuoteFactory,
        CodeFactory $codeFactory
    ) {
        $this->storeManager = $storeManager;
        $this->priceCurrency = $priceCurrency;
        $this->giftCardQuoteFactory = $giftCardQuoteFactory;
        $this->codeFactory = $codeFactory;
    }

    /**
     * Collect store credit used
     *
     * @param CartQuote $quote
     * @param ShippingAssignmentInterface $shippingAssignment
     * @param QuoteTotal $total
     * @return $this|AbstractTotal
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function collect(
        CartQuote $quote,
        ShippingAssignmentInterface $shippingAssignment,
        QuoteTotal $total
    ) {
        parent::collect($quote, $shippingAssignment, $total);

        $quoteId = $quote->getId();
        if (!$quoteId) {
            return $this;
        }

        $address = $shippingAssignment->getShipping()->getAddress();
        if ($address->getAddressType() == Address::ADDRESS_TYPE_BILLING && !$quote->isVirtual()) {
            return $this;
        }

        if ($address->getAddressType() == Address::TYPE_SHIPPING && $quote->isVirtual()) {
            return $this;
        }

        $baseGrandTotal = (float) $total->getBaseGrandTotal();
        $grandTotal = $total->getGrandTotal();
        $baseTotalGiftCardAmount = 0;
        $totalGiftCardAmount = 0;
        if ($baseGrandTotal) {
            $giftCardQuote = $this->giftCardQuoteFactory->create();
            $giftCardCodes = $giftCardQuote->getGiftCardCode($quote);
            $quote->setBaseBssGiftCardAmount(0);
            $quote->setBssGiftCardAmount(0);
            foreach ($giftCardCodes as $giftCard) {
                $giftCardQuoteUsed = $this->codeFactory->create()->loadByCode($giftCard['giftcard_code']);
                if (!$giftCardQuoteUsed->validate()) {
                    $this->giftCardQuoteFactory->create()->removeGiftCardQuote($giftCard['id']);
                    continue;
                }
                $baseGiftCardAmount = $giftCardQuoteUsed->getValue();
                $baseGiftCardUsedAmount = $baseGiftCardAmount;
                if ($baseGiftCardUsedAmount > $baseGrandTotal) {
                    $baseGiftCardUsedAmount = $baseGrandTotal;
                }
                $baseGrandTotal -= $baseGiftCardUsedAmount;

                $giftCardAmount = $this->priceCurrency->convert($baseGiftCardAmount);
                $giftCardUsedAmount = $giftCardAmount;
                if ($giftCardUsedAmount > $grandTotal) {
                    $giftCardUsedAmount = $grandTotal;
                }
                $grandTotal -= $giftCardUsedAmount;

                $baseGiftCardAmount = $this->priceCurrency->convert($baseGiftCardUsedAmount);
                $giftCardAmount = $this->priceCurrency->convert($giftCardUsedAmount);

                $baseTotalGiftCardAmount += $baseGiftCardAmount;
                $totalGiftCardAmount += $giftCardAmount;
                $this->giftCardQuoteFactory->create()
                    ->addUseAmount($giftCard['id'], $giftCardAmount, $baseGiftCardAmount);
            }
            $quote->setBaseBssGiftcardAmount($baseTotalGiftCardAmount);
            $quote->setBssGiftcardAmount($totalGiftCardAmount);
            $total->setBaseBssGiftcardAmount($baseTotalGiftCardAmount);
            $total->setBssGiftcardAmount($totalGiftCardAmount);
            $total->setBaseGrandTotal($total->getBaseGrandTotal() - $baseTotalGiftCardAmount);
            $total->setGrandTotal($total->getGrandTotal() - $totalGiftCardAmount);
        }
        return $this;
    }

    /**
     * Fetch
     *
     * @param CartQuote $quote
     * @param QuoteTotal $total
     * @return array|null
     * @throws \Magento\Framework\Exception\LocalizedException
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetch(CartQuote $quote, QuoteTotal $total)
    {
        $result = null;

        $giftCard = $this->giftCardQuoteFactory->create()->getGiftCardCode($quote);
        if (!empty($giftCard)) {
            $result = [
                'code' => $this->getCode(),
                'gift_card' => $giftCard
            ];
        }
        return $result;
    }
}
