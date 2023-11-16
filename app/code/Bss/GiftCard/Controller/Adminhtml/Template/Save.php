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
use Magento\Framework\Exception\LocalizedException;

/**
 * Class save
 *
 * Bss\GiftCard\Controller\Adminhtml\Template
 */
class Save extends AbstractGiftCard
{
    /**
     * Execute
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws LocalizedException
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $resultRedirect = $this->resultRedirectFactory->create();
        $redirectBack = $this->getRequest()->getParam('back', false);

        if ($params['template_id']) {
            $templateModel = $this->giftCardTemplate->create();
            if (!$templateModel->checkBeforeDisableTemplate($params)) {
                $this->messageManager->addErrorMessage(
                    __(
                        'There are some products using this Template. Please delete them first. (Template Id: %1)',
                        $params['template_id']
                    )
                );
                $resultRedirect->setPath(
                    'giftcard/template/edit',
                    ['id' => $params['template_id']]
                );
                return $resultRedirect;
            }
        }

        try {
            $templateModel = $this->giftCardTemplate->create();
            $templateId = $templateModel->insertTemplate($params);
            $this->messageManager->addSuccessMessage(__('Template was saved'));
        } catch (LocalizedException $e) {
            $this->logger->critical($e);
            $this->messageManager->addExceptionMessage($e);
            $redirectBack = !empty($templateId) ? true : 'new';
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage($e->getMessage());
            $redirectBack = !empty($templateId) ? true : 'new';
        }

        if ($redirectBack === 'new') {
            $resultRedirect->setPath(
                'giftcard/template/new'
            );
        } elseif ($redirectBack === 'edit' && !empty($templateId)) {
            $resultRedirect->setPath(
                'giftcard/template/edit',
                ['id' => $templateId]
            );
        } else {
            $resultRedirect->setPath('giftcard/giftcard/template');
        }

        return $resultRedirect;
    }
}
