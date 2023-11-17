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

namespace Bss\GiftCard\Block\Adminhtml\Pattern\Code\Tab;

use Bss\GiftCard\Block\Adminhtml\Pattern\Tab\Renderer\Order;
use Bss\GiftCard\Model\Config\Source\Status;
use Bss\GiftCard\Model\Pattern\HistoryFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Framework\Exception\FileSystemException;

/**
 * Class history
 *
 * Bss\GiftCard\Block\Adminhtml\Pattern\Code\Tab
 */
class History extends Extended
{
    /**
     * @var HistoryFactory
     */
    private $historyFactory;

    /**
     * @var Status
     */
    private $giftCardStatus;

    /**
     * @param Context $context
     * @param Data $backendHelper
     * @param HistoryFactory $historyFactory
     * @param Status $giftCardStatus
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        HistoryFactory $historyFactory,
        Status $giftCardStatus,
        array $data = []
    ) {
        $this->historyFactory = $historyFactory;
        $this->giftCardStatus = $giftCardStatus;
        parent::__construct(
            $context,
            $backendHelper,
            $data
        );
    }

    /**
     * Construct
     *
     * @return void
     * @throws FileSystemException
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('history_list');
        $this->setDefaultSort('history_id');
        $this->setUseAjax(true);
    }

    /**
     * Prepare collection
     *
     * @return Grid
     */
    protected function _prepareCollection()
    {
        $codeId = (int)$this->getRequest()->getParam('id');
        $collection = [];
        if ($codeId) {
            $collection = $this->historyFactory->create()->getCollection();
            $collection->addFieldToFilter('code_id', $codeId);
        }
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare columns
     *
     * @return Extended
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'history_id',
            [
                'header' => __('ID'),
                'sortable' => true,
                'index' => 'history_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn(
            'order_id',
            [
                'header' => __('Order #'),
                'index' => 'order_id',
                'renderer' => Order::class,
                'filter_index'=>'increment_id'
            ]
        );
        $this->addColumn(
            'amount',
            [
                'header' => __('Amount'),
                'index' => 'amount',
                'type' => 'currency',
                'currency_code' => (string)$this->_scopeConfig->getValue(
                    \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
            ]
        );
        $this->addColumn(
            'updated_time',
            [
                'header' => __('Updated At'),
                'index' => 'updated_time',
                'type' => 'date'
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * Get grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('giftcard/account/grid', ['_current' => true]);
    }

    /**
     * Get row edit URL.
     *
     * @param \Magento\Framework\DataObject $row
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getRowUrl($row)
    {
        return '#';
    }
}
