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

namespace Bss\GiftCard\Block\Customer;

use Bss\GiftCard\Helper\Data as GiftCardData;
use Bss\GiftCard\Model\Pattern\CodeFactory;
use Magento\Framework\Pricing\Helper\Data;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Theme\Block\Html\Pager;

/**
 * Class account
 *
 * Bss\GiftCard\Block\Customer
 */
class Account extends Template
{
    /**
     * @var Data
     */
    private $priceHelper;

    /**
     * @var CodeFactory
     */
    private $codeFactory;

    /**
     * @var GiftCardData
     */
    private $giftCardData;

    /**
     * Construct
     *
     * @param Context $context
     * @param Data $priceHelper
     * @param CodeFactory $codeFactory
     * @param GiftCardData $giftCardData
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $priceHelper,
        CodeFactory $codeFactory,
        GiftCardData $giftCardData,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->codeFactory = $codeFactory;
        $this->giftCardData = $giftCardData;
        $this->priceHelper = $priceHelper;
    }

    /**
     * Prepare the layout of the history block.
     *
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function _prepareLayout()
    {
        parent::_prepareLayout();

        if ($this->getGiftCardCutomer()) {
            $pager = $this->getLayout()->createBlock(
                Pager::class,
                'bss.giftcard.history.pager'
            )->setAvailableLimit(
                [
                    10 => 10,
                    15 => 15,
                    20 => 20
                ]
            )->setShowPerPage(
                true
            )->setCollection(
                $this->getGiftCardCutomer()
            );
            $this->setChild('pager', $pager);
            $this->getGiftCardCutomer()->load();
        }
        return $this;
    }

    /**
     * Render pagination HTML
     *
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * Get gift card customer
     *
     * @return \Bss\GiftCard\Model\Pattern\Code
     * @SuppressWarnings(PHPMD.RequestAwareBlockMethod)
     */
    public function getGiftCardCutomer()
    {
        $page = ($this->getRequest()->getParam('p')) ? $this->getRequest()->getParam('p') : 1;
        $pageSize = ($this->getRequest()->getParam('limit')) ? $this->getRequest()->getParam('limit') : 10;
        $collection = $this->codeFactory->create()->loadByEmail();
        $collection->setPageSize($pageSize);
        $collection->setCurPage($page);
        return $collection;
    }

    /**
     * Convert price with currency
     *
     * @param   float $price
     * @return  string
     */
    public function convertPrice($price)
    {
        return $this->giftCardData->convertPrice($price);
    }

    /**
     * Convert update time
     *
     * @param   string $time
     * @return  string
     */
    public function formatDateTime($time)
    {
        return $this->giftCardData->formatDateTime($time);
    }

    /**
     * Get order details
     *
     * @param   \Bss\GiftCard\Model\Pattern\Code $giftCard
     * @return  string
     */
    public function getOrderDetails($giftCard)
    {
        $html = '';
        if ($giftCard->getOrderId()) {
            $url = $this->getUrl(
                'sales/order/view',
                ['order_id' => $giftCard->getOrderId()]
            );
            $html = '<span>';
            $html .= '<a href="' . $url . '"">';
            $html .= __('Order # %1', $giftCard->getIncrementId());
            $html .= '</a>';
            $html .= '</span>';
        }
        return $html;
    }

    /**
     * Get code
     *
     * @param   string $code
     * @return  string
     */
    public function getCode($code)
    {
        return $this->giftCardData->hideCode($code);
    }
}
