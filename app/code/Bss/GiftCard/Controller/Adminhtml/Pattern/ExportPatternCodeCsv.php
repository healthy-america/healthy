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

namespace Bss\GiftCard\Controller\Adminhtml\Pattern;

use Magento\Framework\App\Filesystem\DirectoryList;
use Bss\GiftCard\Controller\Adminhtml\AbstractGiftCard;
use Bss\GiftCard\Block\Adminhtml\Pattern\Tab\CodeList;

/**
 * Class export pattern code csv
 *
 * Bss\GiftCard\Controller\Adminhtml\Pattern
 */
class ExportPatternCodeCsv extends AbstractGiftCard
{
    /**
     * Export pattern codes as CSV file
     *
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('id');
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($id) {
            $fileName = 'pattern_codes.csv';
            $content = $this->_view->getLayout()->createBlock(
                CodeList::class
            )->getCsvFile();

            try {
                return $this->fileFactory->create($fileName, $content, DirectoryList::VAR_DIR);
            } catch (\Exception $e) {
                $this->logger->critical($e);
                $this->messageManager->addErrorMessage($e->getMessage());
            }
        } else {
            return $resultRedirect->setPath('giftcard/giftcard/pattern', ['_current' => true]);
        }
    }
}
