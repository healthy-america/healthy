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
use Magento\Sales\Api\Data\OrderExtensionInterfaceFactory;
use Magento\Sales\Model\Order;

class OrderLoadAfter implements ObserverInterface
{
    /**
     * @var OrderExtensionInterfaceFactory
     */
    private $orderExtensionFactory;

    /**
     * @var GiftCardHelper
     */
    private $giftCardHelper;

    /**
     * Construct
     *
     * @param OrderExtensionInterfaceFactory $orderExtensionFactory
     * @param GiftCardHelper $giftCardHelper
     */
    public function __construct(
        OrderExtensionInterfaceFactory $orderExtensionFactory,
        GiftCardHelper $giftCardHelper
    ) {
        $this->orderExtensionFactory = $orderExtensionFactory;
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
        /** @var Order $order */
        $order = $observer->getOrder();
        $extensionAttributes = $order->getExtensionAttributes();

        if (!$extensionAttributes) {
            $extensionAttributes = $this->orderExtensionFactory->create();
        }

        $giftCardAmount = $order->getData('bss_giftcard_amount');
        $storeId = $order->getStoreId();

        if ($this->giftCardHelper->isEnabled($storeId)
            && $giftCardAmount
            && $giftCardAmount > 0
        ) {
            $extensionAttributes->setGiftCardAmount($giftCardAmount);
            $order->setExtensionAttributes($extensionAttributes);
        }
    }
}
