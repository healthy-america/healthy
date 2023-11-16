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

namespace Bss\GiftCard\Plugin\Sales;

use Bss\GiftCard\Model\Order as GiftCardOrder;
use Magento\Sales\Model\Order as SalesOrder;

/**
 * Class order
 *
 * Bss\GiftCard\Plugin\Sales
 */
class Order
{
    /**
     * @var \Bss\GiftCard\Model\Order
     */
    private $gcOrderModel;

    /**
     *
     * @param GiftCardOrder $gcOrderModel
     */
    public function __construct(
        GiftCardOrder $gcOrderModel
    ) {
        $this->gcOrderModel = $gcOrderModel;
    }

    /**
     * Cancel creditmemo
     *
     * @param SalesOrder $order
     * @return void
     */
    public function beforeCanCreditmemo(
        SalesOrder $order
    ) {
        $creditmemoBaseGcAmount = $this->gcOrderModel->getAmountCreditmemo($order);
        $invoiceBaseGcAmount = $this->gcOrderModel->getAmountInvoice($order);
        $amount = $invoiceBaseGcAmount - $creditmemoBaseGcAmount;
        if (!$order->isCanceled() && $order->getBaseBssGiftcardAmount()
            && $order->getState() !== \Magento\Sales\Model\Order::STATE_CLOSED
            && $amount > 0) {
            $order->setForcedCanCreditmemo(true);
        }
    }
}
