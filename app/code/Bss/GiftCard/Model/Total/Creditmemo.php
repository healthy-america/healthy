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

use Magento\Sales\Model\Order\Creditmemo\Total\AbstractTotal;
use Bss\GiftCard\Model\Order;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Sales\Model\Order\Creditmemo as SalesCreditmemo;

/**
 * Class creditmemo
 *
 * Bss\GiftCard\Model\Total
 */
class Creditmemo extends AbstractTotal
{
    /**
     * @var \Bss\GiftCard\Model\Order
     */
    private $gcOrderModel;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * Creditmemo constructor.
     * @param Order $gcOrderModel
     * @param PriceCurrencyInterface $priceCurrency
     * @param array $data
     */
    public function __construct(
        Order $gcOrderModel,
        PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        parent::__construct($data);
        $this->gcOrderModel = $gcOrderModel;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Collect storecredit of refunded items
     *
     * @param SalesCreditmemo $creditmemo
     * @return $this
     */
    public function collect(SalesCreditmemo $creditmemo)
    {
        parent::collect($creditmemo);

        $order = $creditmemo->getOrder();
        if (!$order->getBssGiftcardAmount()) {
            return $this;
        }

        $invoiceBaseGcAmount = $this->gcOrderModel->getAmountInvoice($order);
        $creditmemoBaseGcAmountRefund = $this->gcOrderModel->getAmountCreditmemo($order);
        $baseAmount = $invoiceBaseGcAmount - $creditmemoBaseGcAmountRefund;
        if ($baseAmount >= $creditmemo->getBaseGrandTotal()) {
            $baseAmountUsedLeft = $creditmemo->getBaseGrandTotal();
            $amountUsedLeft = $creditmemo->getGrandTotal();
            $creditmemo->setBaseGrandTotal(0)
                ->setGrandTotal(0)
                ->setAllowZeroGrandTotal(true);
        } else {
            $baseAmountUsedLeft = $baseAmount;
            $amountUsedLeft = $this->priceCurrency->convert($baseAmountUsedLeft, $creditmemo->getStore());
            $creditmemo->setBaseGrandTotal($creditmemo->getBaseGrandTotal() - $baseAmountUsedLeft)
                ->setGrandTotal($creditmemo->getGrandTotal() - $amountUsedLeft);
        }
        $creditmemo->setBaseBssGiftcardAmount($baseAmountUsedLeft)
            ->setBssGiftcardAmount($amountUsedLeft);
        return $this;
    }
}
