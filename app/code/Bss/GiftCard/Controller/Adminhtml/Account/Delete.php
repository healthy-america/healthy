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

namespace Bss\GiftCard\Controller\Adminhtml\Account;

use Bss\GiftCard\Controller\Adminhtml\AbstractGiftCard;

/**
 * Class delete
 *
 * Bss\GiftCard\Controller\Adminhtml\Account
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
        if ($id) {
            $model = $this->codeFactory->create();
            $model->load($id);
            try {
                if ($model->getCodeId()) {
                    $model->delete();
                    $this->messageManager->addSuccessMessage(__('You deleted the gift code.'));
                    return $resultRedirect->setPath('giftcard/giftcard/account');
                }
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath(
                    'giftcard/account/edit',
                    ['id' => $id]
                );
            }
        }
        $this->messageManager->addErrorMessage(__('We can\'t find an gift code to delete.'));
        return $resultRedirect->setPath('giftcard/giftcard/account');
    }
}
