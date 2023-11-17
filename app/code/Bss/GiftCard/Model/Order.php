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

namespace Bss\GiftCard\Model;

/**
 * Class order
 *
 * Bss\GiftCard\Model
 */
class Order
{
    /**
     * Get amount invoice
     *
     * @param \Magento\Sales\Model\Order $order
     * @return float
     */
    public function getAmountInvoice($order)
    {
        $invoiceBaseGcAmount = 0;
        if ($order->getInvoiceCollection() && $order->getInvoiceCollection()->getSize()) {
            foreach ($order->getInvoiceCollection() as $invoice) {
                $invoiceBaseGcAmount += $invoice->getBaseBssGiftcardAmount();
            }
        }
        return $invoiceBaseGcAmount;
    }

    /**
     * Get amount credirmemo
     *
     * @param \Magento\Sales\Model\Order $order
     * @return float
     */
    public function getAmountCreditmemo($order)
    {
        $creditmemoBaseGcAmount = 0;
        if ($order->getCreditmemosCollection() && $order->getCreditmemosCollection()->getSize()) {
            foreach ($order->getCreditmemosCollection() as $creditmemo) {
                $creditmemoBaseGcAmount += $creditmemo->getBaseBssGiftcardAmount();
            }
        }
        return $creditmemoBaseGcAmount;
    }
}
