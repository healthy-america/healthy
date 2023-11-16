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
 * @copyright  Copyright (c) BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\GiftCard\Observer;

use Bss\GiftCard\Helper\Data as GiftCardHelper;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\InvoiceExtensionInterfaceFactory;

class InvoiceLoadAfter implements ObserverInterface
{
    /**
     * @var InvoiceExtensionInterfaceFactory
     */
    private $invoiceExtensionFactory;

    /**
     * @var GiftCardHelper
     */
    private $giftCardHelper;

    /**
     * @param InvoiceExtensionInterfaceFactory $invoiceExtensionFactory
     * @param GiftCardHelper $giftCardHelper
     */
    public function __construct(
        InvoiceExtensionInterfaceFactory $invoiceExtensionFactory,
        GiftCardHelper $giftCardHelper
    ) {
        $this->invoiceExtensionFactory = $invoiceExtensionFactory;
        $this->giftCardHelper = $giftCardHelper;
    }

    /**
     * Execute
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     */
    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order\Invoice $invoice */
        $invoice = $observer->getInvoice();
        $extensionAttributes = $invoice->getExtensionAttributes();

        if (!$extensionAttributes) {
            $extensionAttributes = $this->invoiceExtensionFactory->create();
        }

        $giftCardAmount = $invoice->getOrder()->getData('bss_giftcard_amount');
        $storeId = $invoice->getStoreId();

        if ($this->giftCardHelper->isEnabled($storeId)
            && $giftCardAmount
            && $giftCardAmount > 0
        ) {
            $extensionAttributes->setGiftCardAmount($giftCardAmount);
            $invoice->setExtensionAttributes($extensionAttributes);
        }
    }
}
