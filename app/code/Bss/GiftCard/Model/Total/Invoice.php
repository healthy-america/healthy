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

use Magento\Sales\Model\Order\Invoice as SalesInvoice;
use Magento\Sales\Model\Order\Invoice\Total\AbstractTotal;

/**
 * Class invoice
 *
 * Bss\GiftCard\Model\Total
 */
class Invoice extends AbstractTotal
{
    /**
     * Collect
     *
     * @param SalesInvoice $invoice
     * @return $this|void
     */
    public function collect(
        SalesInvoice $invoice
    ) {
        parent::collect($invoice);
        $order = $invoice->getOrder();
        $baseAmount = $order->getBaseBssGiftcardAmount();
        $amount = $order->getBssGiftcardAmount();
        if (!$baseAmount) {
            return;
        }
        if (!$invoice->getId() && !empty($order->getInvoiceCollection()->getData())) {
            $invoiceBaseGcAmount = 0;
            $invoiceGcAmount = 0;
            foreach ($order->getInvoiceCollection() as $invoiceOrder) {
                $invoiceBaseGcAmount += $invoiceOrder->getBaseBssGiftcardAmount();
                $invoiceGcAmount += $invoiceOrder->getBssGiftcardAmount();
            }
            $amount -= $invoiceGcAmount;
            $baseAmount -= $invoiceBaseGcAmount;
        }
        $baseGrandTotal = $invoice->getBaseGrandTotal();
        $grandTotal = $invoice->getGrandTotal();
        if ($baseAmount >= $baseGrandTotal) {
            $baseAmountUsedLeft = $baseGrandTotal;
            $amountUsedLeft = $grandTotal;
            $invoice->setBaseGrandTotal(0);
            $invoice->setGrandTotal(0);
        } else {
            $baseAmountUsedLeft = $baseAmount;
            $amountUsedLeft = $amount;
            $invoice->setBaseGrandTotal($baseGrandTotal - $baseAmountUsedLeft);
            $invoice->setGrandTotal($grandTotal - $amountUsedLeft);
        }
        $invoice->setBssGiftcardAmount($amountUsedLeft);
        $invoice->setBaseBssGiftcardAmount($baseAmountUsedLeft);
    }
}
