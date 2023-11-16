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

namespace Bss\GiftCard\Controller\Adminhtml\Template;

use Bss\GiftCard\Controller\Adminhtml\AbstractGiftCard;
use Magento\Framework\Controller\ResultFactory;

/**
 * Class mass status
 *
 * Bss\GiftCard\Controller\Adminhtml\Template
 */
class MassStatus extends AbstractGiftCard
{
    /**
     * Execute
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->templatesFactory->create());
        $templateIds = $collection->getAllIds();
        $status = (int) $this->getRequest()->getParam('status');
        try {
            foreach ($collection->getItems() as $template) {
                $this->modifyRecord($template, $status);
            }
            $this->messageManager->addSuccessMessage(
                __('A total of %1 record(s) have been updated.', count($templateIds))
            );
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                $e,
                __('Something went wrong while updating the template(s) status.')
            );
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('giftcard/giftcard/template');
    }

    /**
     * Modify record
     *
     * @param Object $record
     * @param int $status
     */
    private function modifyRecord($record, $status)
    {
        $record->setStatus($status)->save();
    }
}
