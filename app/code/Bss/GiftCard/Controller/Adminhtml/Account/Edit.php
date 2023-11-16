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
use Magento\Framework\Controller\ResultFactory;

/**
 * Class edit
 *
 * Bss\GiftCard\Controller\Adminhtml\Account
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
        $codeId = $this->getRequest()->getParam('id');
        if ($codeId) {
            try {
                $code = $this->codeFactory->create()->load($codeId);
                $pageTitle = sprintf("%s", $code->getCode());
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__('This gift card code no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                return $resultRedirect->setPath('giftcard/giftcard/account/');
            }
        } else {
            $pageTitle = __('New Gift Card Code');
        }

        $breadcrumb = $codeId ? __('Edit Code') : __('New Code');
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Bss_GiftCard::giftcard');
        $resultPage->addBreadcrumb($breadcrumb, $breadcrumb);
        $resultPage->getConfig()->getTitle()->prepend(__('Gift Card Code'));
        $resultPage->getConfig()->getTitle()->prepend($pageTitle);
        return $resultPage;
    }
}
