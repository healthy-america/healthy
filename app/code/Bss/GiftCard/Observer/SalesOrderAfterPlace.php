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

namespace Bss\GiftCard\Observer;

use Bss\GiftCard\Model\Pattern\CodeFactory;
use Bss\GiftCard\Model\ResourceModel\GiftCard\QuoteFactory;
use Magento\Framework\Event\ObserverInterface;

class SalesOrderAfterPlace implements ObserverInterface
{
    /**
     * @var QuoteFactory
     */
    private $giftCardQuoteFactory;

    /**
     * @var CodeFactory
     */
    private $codeFactory;

    /**
     * SalesOrderAfterPlace constructor.
     *
     * @param QuoteFactory $giftCardQuoteFactory
     * @param CodeFactory $codeFactory
     */
    public function __construct(
        QuoteFactory $giftCardQuoteFactory,
        CodeFactory $codeFactory
    ) {
        $this->giftCardQuoteFactory = $giftCardQuoteFactory;
        $this->codeFactory = $codeFactory;
    }
    /**
     * Execute
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();
        $quoteId = $order->getQuoteId();
        $giftCardQuote = $this->giftCardQuoteFactory->create();
        $giftCardCodes = $giftCardQuote->getQuoteCodeById($quoteId);
        foreach ($giftCardCodes as $giftCardCode) {
            $baseGiftcardAmount = $giftCardCode['base_giftcard_amount'];
            $code = $giftCardCode['giftcard_code'];
            $this->codeFactory->create()->updateAmount($code, $baseGiftcardAmount, $quoteId);
        }

        return $this;
    }
}
