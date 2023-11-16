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

namespace Bss\GiftCard\Block\Adminhtml\Pattern;

use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Json\EncoderInterface;
use Bss\GiftCard\Block\Adminhtml\Pattern\Tab\CodeList as CodeListTab;

/**
 * Class code list
 *
 * Bss\GiftCard\Block\Adminhtml\Pattern
 */
class CodeList extends Template
{
    /**
     * Block template
     *
     * @var string
     */
    protected $_template = 'pattern/code/list.phtml';

    /**
     * @var \Bss\GiftCard\Block\Adminhtml\Pattern\Tab\CodeList
     */
    protected $blockGrid;

    /**
     * @var EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @param Context $context
     * @param EncoderInterface $jsonEncoder
     * @param array $data
     */
    public function __construct(
        Context $context,
        EncoderInterface $jsonEncoder,
        array $data = []
    ) {
        $this->jsonEncoder = $jsonEncoder;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve instance of grid block
     *
     * @return \Magento\Framework\View\Element\BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getBlockGrid()
    {
        if (null === $this->blockGrid) {
            $this->blockGrid = $this->getLayout()->createBlock(
                CodeListTab::class,
                'bssgiftcard.pattern.code.list'
            );
        }
        return $this->blockGrid;
    }

    /**
     * Return HTML of grid block
     *
     * @return string
     * @throws LocalizedException
     */
    public function getGridHtml()
    {
        return $this->getBlockGrid()->toHtml();
    }
}
