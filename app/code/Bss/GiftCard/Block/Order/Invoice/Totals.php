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

namespace Bss\GiftCard\Block\Order\Invoice;

use Magento\Framework\View\Element\Template;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class totals
 *
 * Bss\GiftCard\Block\Order\Invoice
 */
class Totals extends Template
{
    /**
     * @var DataObjectFactory
     */
    protected $dataObjectFactory;

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
        parent::__construct(
            $context,
            $data
        );
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * Add gift card invoice totals array
     *
     * @return void
     */
    public function initTotals()
    {
        $invoiceTotalsBlock = $this->getParentBlock();
        $invoice = $invoiceTotalsBlock->getInvoice();
        if ($invoice->getBssGiftcardAmount() > 0) {
            $total = $this->dataObjectFactory->create();
            $total->setCode('bss_giftcard')
                ->setValue(-$invoice->getBssGiftcardAmount())
                ->setBaseValue(-$invoice->getBaseBssGiftcardAmount())
                ->setLabel(__('Gift Card'));
            $invoiceTotalsBlock->addTotal($total);
        }
    }
}
