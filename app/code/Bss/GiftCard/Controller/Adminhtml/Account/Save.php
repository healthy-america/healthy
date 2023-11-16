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
use Magento\Framework\Exception\LocalizedException;

/**
 * Class save
 *
 * Bss\GiftCard\Controller\Adminhtml\Account
 */
class Save extends AbstractGiftCard
{
    /**
     * Execute
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $resultRedirect = $this->resultRedirectFactory->create();
        $redirectBack = $this->getRequest()->getParam('back', false);
        $codeId = $params['code_id'];
        try {
            $codeModel = $this->codeFactory->create();
            $code = $codeModel->load($codeId);
            if ($code->getId()) {
                $expiry = $this->dateTime->formatDate($params['expiry_day']);
                $code->setValue($params['value'])
                    ->setStatus($params['status'])
                    ->setExpiryDay($expiry)
                    ->setWebsiteId($params['website_id'])
                    ->setSenderName($params['sender_name'])
                    ->setRecipientName($params['recipient_name'])
                    ->setRecipientEmail($params['recipient_email'])
                    ->setSenderEmail($params['sender_email'])
                    ->setMessage($params['message'])
                    ->save();
            } else {
                $codeModel->getResource()->insertCode($params);
            }
            $this->messageManager->addSuccessMessage(__('Success'));
        } catch (LocalizedException $e) {
            $this->logger->critical($e);
            $this->messageManager->addExceptionMessage($e);
            $redirectBack = $codeId ? true : 'new';
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage($e->getMessage());
            $redirectBack = $codeId ? true : 'new';
        }

        if ($redirectBack === 'new') {
            $resultRedirect->setPath(
                'giftcard/account/new'
            );
        } elseif ($redirectBack === 'edit') {
            $resultRedirect->setPath(
                'giftcard/account/edit',
                ['id' => $codeId]
            );
        } else {
            $resultRedirect->setPath('giftcard/giftcard/account');
        }
        return $resultRedirect;
    }
}
