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

namespace Bss\GiftCard\Model\Pattern\Code;

use Bss\GiftCard\Model\ResourceModel\Pattern\Code\CollectionFactory;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\ReportingInterface;
use Magento\Framework\Api\Search\SearchCriteriaBuilder;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\UiComponent\DataProvider\DataProvider as UiDataProvider;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order\Address\Renderer;

/**
 * Class data provider
 *
 * Bss\GiftCard\Model\Pattern\Code
 */
class DataProvider extends UiDataProvider
{
    /**
     * @var CollectionFactory
     */
    private $codeCollectionFactory;

    /**
     * @var array
     */
    private $loadedData;

    /**
     * @var OrderInterface
     */
    private $orderInterface;

    /**
     * @var Renderer
     */
    private $addressRenderer;

    /**
     * DataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param ReportingInterface $reporting
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RequestInterface $request
     * @param FilterBuilder $filterBuilder
     * @param CollectionFactory $codeCollectionFactory
     * @param Renderer $addressRenderer
     * @param OrderInterface $orderInterface
     * @param array $meta
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        ReportingInterface $reporting,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RequestInterface $request,
        FilterBuilder $filterBuilder,
        CollectionFactory $codeCollectionFactory,
        Renderer $addressRenderer,
        OrderInterface $orderInterface,
        array $meta = [],
        array $data = []
    ) {
        $this->codeCollectionFactory = $codeCollectionFactory;
        $this->addressRenderer = $addressRenderer;
        $this->orderInterface = $orderInterface;

        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $reporting,
            $searchCriteriaBuilder,
            $request,
            $filterBuilder,
            $meta,
            $data
        );
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }

        $items = $this->codeCollectionFactory->create()->getItems();
        $this->loadedData = [];
        foreach ($items as $item) {
            $this->loadedData[$item->getId()] = $item->getData();
            if ($item->getOrderId()) {
                $order = $this->loadOrder($item->getOrderId());
                if ($order->getShippingAddress()) {
                    $address = $this->addressRenderer->format($order->getShippingAddress(), 'oneline');
                    $this->loadedData[$item->getId()]['shipping_address'] = $address;
                }
            }
        }

        return $this->loadedData;
    }

    /**
     * Load order
     *
     * @param int $orderId
     * @return mixed
     */
    private function loadOrder($orderId)
    {
        return $this->orderInterface->load($orderId);
    }
}
