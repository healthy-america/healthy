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

namespace Bss\GiftCard\Block\Adminhtml\Order\Totals;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\DataObjectFactory;

/**
 * Class gift card
 *
 * Bss\GiftCard\Block\Adminhtml\Order\Totals
 */
class GiftCard extends Template
{
    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @param Context $context
     * @param DataObjectFactory $dataObjectFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        DataObjectFactory $dataObjectFactory,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * Get order
     *
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder()
    {
        return $this->getParentBlock()->getOrder();
    }

    /**
     * Add gift card info to total
     *
     * @return $this
     */
    public function initTotals()
    {
        $source = $this->getParentBlock()->getSource();
        $total = $this->dataObjectFactory->create();
        $total->setCode('bss_giftcard')
            ->setValue(-$source->getBssGiftcardAmount())
            ->setBaseValue(-$source->getBaseBssGiftcardAmount())
            ->setLabel(__('Gift Card'));
        $this->getParentBlock()->addTotalBefore($total, 'grand_total');
        return $this;
    }
}
