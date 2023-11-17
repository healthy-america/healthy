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
use Magento\Framework\Controller\ResultFactory;

/**
 * Class edit
 *
 * Bss\GiftCard\Controller\Adminhtml\Pattern
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
        $patternId = $this->getRequest()->getParam('id');
        if ($patternId) {
            try {
                $pattern = $this->giftCardPattern->create()->load($patternId);
                $this->registry->register('pattern', $pattern);
                $pageTitle = sprintf("%s", $pattern->getName());
            } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__('This pattern no longer exists.'));
                /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                return $resultRedirect->setPath('giftcard/giftcard/pattern/');
            }
        } else {
            $pageTitle = __('New Gift Card Pattern');
        }

        $breadcrumb = $patternId ? __('Edit Pattern') : __('New Pattern');
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Bss_GiftCard::giftcard');
        $resultPage->addBreadcrumb($breadcrumb, $breadcrumb);
        $resultPage->getConfig()->getTitle()->prepend(__('Gift Card Pattern'));
        $resultPage->getConfig()->getTitle()->prepend($pageTitle);
        return $resultPage;
    }
}
