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

namespace Bss\GiftCard\Block\Adminhtml\Items\Column\Name;

use Bss\GiftCard\Helper\Catalog\Product\Configuration;
use Magento\Backend\Block\Template\Context;
use Magento\Catalog\Model\Product\OptionFactory;
use Magento\CatalogInventory\Api\StockConfigurationInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Registry;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Block\Adminhtml\Items\Column\Name as ColumnName;
use Magento\Sales\Model\Order;

/**
 * Class gift card
 *
 * Bss\GiftCard\Block\Adminhtml\Items\Column\Name
 */
class GiftCard extends ColumnName
{
    /**
     * @var Configuration
     */
    private $configurationHelper;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @param Context $context
     * @param StockRegistryInterface $stockRegistry
     * @param StockConfigurationInterface $stockConfiguration
     * @param Registry $registry
     * @param OptionFactory $optionFactory
     * @param Configuration $configurationHelper
     * @param OrderRepositoryInterface $orderRepository
     * @param array $data
     */
    public function __construct(
        Context $context,
        StockRegistryInterface $stockRegistry,
        StockConfigurationInterface $stockConfiguration,
        Registry $registry,
        OptionFactory $optionFactory,
        Configuration $configurationHelper,
        OrderRepositoryInterface $orderRepository,
        array $data = []
    ) {
        $this->configurationHelper = $configurationHelper;
        $this->orderRepository = $orderRepository;
        parent::__construct(
            $context,
            $stockRegistry,
            $stockConfiguration,
            $registry,
            $optionFactory,
            $data
        );
    }

    /**
     * Get order options
     *
     * @return array
     * @throws LocalizedException
     */
    public function getOrderOptions()
    {
        return array_merge(
            $this->getOptionConvert(),
            parent::getOrderOptions()
        );
    }

    /**
     * Truncate String
     *
     * @param string $value
     * @param int $length
     * @param string $etc
     * @param string $remainder
     * @param bool $breakWords
     * @return string
     */
    public function truncateString(
        $value,
        $length = 80,
        $etc = '...',
        &$remainder = '',
        $breakWords = true
    ) {
        $length = 350;
        return parent::truncateString(
            $value,
            $length,
            $etc,
            $remainder,
            $breakWords
        );
    }

    /**
     * Get option convert
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function getOptionConvert()
    {
        $options = $this->getItem()->getProductOptions();
        $buyRequest = [];
        if (isset($options['info_buyRequest'])) {
            $buyRequest = $options['info_buyRequest'];
        }
        if ($this->_appState->getAreaCode() == "adminhtml") {
            try {
                $storeId = $this->getStoreId();
            } catch (\Exception $exception) {
                $storeId = 1;
            }

            return $this->configurationHelper->renderOptions(
                $buyRequest,
                $storeId
            );
        }
        // FE area
        return $this->configurationHelper->renderOptions($buyRequest);
    }

    /**
     * Get store id
     *
     * @return int|null
     */
    private function getStoreId()
    {
        $fullActionPath = $this->getRequest()->getFullActionName();
        $pageNew = [
            "sales_order_invoice_new",
            "sales_order_creditmemo_new",
            "adminhtml_order_shipment_new"
        ];
        if ($fullActionPath === "sales_order_view") {
            /** @var Order $order */
            $order = $this->_coreRegistry->registry('current_order');
            return $order->getStoreId();
        } elseif (in_array($fullActionPath, $pageNew)) {
            // We can get order_id from params
            $orderId = $this->getRequest()->getParam('order_id');
            $order = $this->orderRepository->get($orderId);
            return $order->getStoreId();
        } elseif ($fullActionPath === "sales_order_invoice_view") {
            /** @var \Magento\Sales\Model\Order\Invoice $invoice */
            $invoice = $this->_coreRegistry->registry('current_invoice');
            return $invoice->getStoreId();
        } elseif ($fullActionPath === "adminhtml_order_shipment_view") {
            /** @var \Magento\Sales\Model\Order\Shipment $invoice */
            $shipment = $this->_coreRegistry->registry('current_shipment');
            return $shipment->getStoreId();
        } elseif ($fullActionPath === "sales_order_creditmemo_view") {
            /** @var \Magento\Sales\Model\Order\Creditmemo $creditMemo */
            $creditMemo = $this->_coreRegistry->registry('current_creditmemo');
            return $creditMemo->getStoreId();
        }
        return 1;
    }

    /**
     * Escape html
     *
     * @param string|array $data
     * @param array|null $allowedTags
     * @return string
     */
    public function escapeHtml($data, $allowedTags = null)
    {
        $options = $this->getOptionConvert();
        if (!empty($options)) {
            $optionValue = array_column($options, 'value');
            if (in_array($data, $optionValue)) {
                return $data;
            }
        }
        return parent::escapeHtml($data, $allowedTags);
    }
}
