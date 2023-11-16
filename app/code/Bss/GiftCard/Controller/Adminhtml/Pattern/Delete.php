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

use Bss\GiftCard\Controller\Adminhtml\AbstractGiftCard;

/**
 * Class delete
 *
 * Bss\GiftCard\Controller\Adminhtml\Pattern;
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
        $resultRedirect = $this->resultRedirectFactory->create();
        try {
            $pattern = $this->giftCardPattern->create()->load($id);
            if ($pattern->getPatternId()) {
                $pattern->delete();
                $this->messageManager->addSuccessMessage(__('Success'));
                return $resultRedirect->setPath('giftcard/giftcard/pattern');
            }
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage($e->getMessage());
            return $resultRedirect->setPath(
                'giftcard/pattern/edit',
                ['id' => $id]
            );
        }
        $this->messageManager->addErrorMessage(__('We can\'t find an pattern to delete.'));
        return $resultRedirect->setPath('giftcard/giftcard/pattern');
    }
}
