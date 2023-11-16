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
 * Class edit
 *
 * Bss\GiftCard\Controller\Adminhtml\Template
 */
class Edit extends AbstractGiftCard
{
    /**
     * Edit gift card template page
     *
     * @return mixed
     */
    public function execute()
    {
        $templateId = $this->getRequest()->getParam('id');
        if ($templateId) {
            try {
                $template = $this->templateService->getTemplateById($templateId)['template_data'];
                $pageTitle = sprintf("%s", $template['name']);
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__('This template no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                return $resultRedirect->setPath('giftcard/giftcard/template/');
            }
        } else {
            $pageTitle = __('New Gift Card Template');
        }

        $breadcrumb = $templateId ? __('Edit Template') : __('New Template');
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Bss_GiftCard::giftcard');
        $resultPage->addBreadcrumb($breadcrumb, $breadcrumb);
        $resultPage->getConfig()->getTitle()->prepend(__('Gift Card Template'));
        $resultPage->getConfig()->getTitle()->prepend($pageTitle);
        return $resultPage;
    }
}
