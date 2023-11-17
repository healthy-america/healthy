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

namespace Bss\GiftCard\Block\Adminhtml\Pattern\Tab;

use Bss\GiftCard\Model\Config\Source\Status;
use Bss\GiftCard\Model\Pattern\CodeFactory;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Helper\Data;
use Magento\Framework\Exception\FileSystemException;

/**
 * Class code list
 *
 * Bss\GiftCard\Block\Adminhtml\Pattern\Tab
 */
class CodeList extends Extended
{
    /**
     * @var CodeFactory
     */
    private $codeFactory;

    /**
     * @var Status
     */
    private $giftCardStatus;

    /**
     * @param Context $context
     * @param Data $backendHelper
     * @param CodeFactory $codeFactory
     * @param Status $giftCardStatus
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        CodeFactory $codeFactory,
        Status $giftCardStatus,
        array $data = []
    ) {
        $this->codeFactory = $codeFactory;
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
        $this->setId('pattern_code_list');
        $this->setDefaultSort('code_id');
        $this->setUseAjax(true);
    }

    /**
     * Prepare collection
     *
     * @return Grid
     */
    protected function _prepareCollection()
    {
        $patternId = (int)$this->getRequest()->getParam('id');
        $collection = [];
        if ($patternId) {
            $collection = $this->codeFactory->create()->getCollection();
            $collection->filterByPattern($patternId);
        }
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * Prepare columns
     *
     * @return Extended
     * @throws \Exception
     * @SuppressWarnings(PHPMD.RequestAwareBlockMethod)
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'code_id',
            [
                'header' => __('ID'),
                'sortable' => true,
                'index' => 'code_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn(
            'code',
            [
                'header' => __('Code'),
                'index' => 'code'
            ]
        );
        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'index' => 'status',
                'type' => 'options',
                'options' => $this->giftCardStatus->getOptionArray(),
                'filter_index'=>'main_table.status'
            ]
        );
        $this->addColumn(
            'value',
            [
                'header' => __('Current Amount'),
                'index' => 'value',
                'type' => 'currency',
                'currency_code' => (string)$this->_scopeConfig->getValue(
                    \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
            ]
        );
        $this->addColumn(
            'origin_value',
            [
                'header' => __('Initial Amount'),
                'index' => 'origin_value',
                'type' => 'currency',
                'currency_code' => (string)$this->_scopeConfig->getValue(
                    \Magento\Directory\Model\Currency::XML_PATH_CURRENCY_BASE,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                )
            ]
        );
        $this->addColumn(
            'order_id',
            [
                'header' => __('Order #'),
                'index' => 'order_id',
                'renderer' => Renderer\Order::class
            ]
        );
        $this->addColumn(
            'expiry_day',
            [
                'header' => __('Expiry Day'),
                'index' => 'expiry_day',
                'type' => 'date'
            ]
        );
        $this->addColumn(
            'action',
            [
                'header' => __('Action'),
                'type' => 'action',
                'getter' => 'getCodeId',
                'actions' => [
                    [
                        'caption' => __('Delete'),
                        'url' => [
                            'base' => 'giftcard/pattern_code/delete',
                            'params' => [
                                'pattern_id' => $this->getRequest()->getParam('id')
                            ],
                        ],
                        'field' => 'id',
                    ],
                ],
                'is_system' => true,
                'filter' => false,
                'sortable' => false
            ]
        );
        $this->addExportType('*/*/exportPatternCodeCsv', __('CSV'));

        return parent::_prepareColumns();
    }

    /**
     * Get grid url
     *
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('giftcard/pattern/grid', ['_current' => true]);
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
