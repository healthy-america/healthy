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

namespace Bss\GiftCard\Controller\Adminhtml\Pattern\Code;

use Bss\GiftCard\Controller\Adminhtml\AbstractGiftCard;

/**
 * Class delete
 *
 * Bss\GiftCard\Controller\Adminhtml\Pattern\Code
 */
class Delete extends AbstractGiftCard
{
    /**
     * Execute
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('id');
        $patternId = $this->getRequest()->getParam('pattern_id');
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $code = $this->codeFactory->create()->load($id);
            if ($code->getCodeId()) {
                $code->delete();
                $this->messageManager->addSuccessMessage(__('Success'));
            } else {
                $this->messageManager->addErrorMessage(__('We can\'t find an gift card code to delete.'));
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $resultRedirect->setPath('giftcard/pattern/edit', ['id' => $patternId]);
    }
}
