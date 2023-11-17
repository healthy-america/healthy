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
use Magento\Framework\Exception\LocalizedException;

/**
 * Class save
 *
 * Bss\GiftCard\Controller\Adminhtml\Pattern
 */
class Save extends AbstractGiftCard
{
    /**
     * Execute
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $resultRedirect = $this->resultRedirectFactory->create();
        $patternId = $params['pattern_id'];
        $patternModel = $this->giftCardPattern->create();

        $redirectBack = $this->getRequest()->getParam('back', false);

        if ($patternId) {
            $patternModel->insertPattern($params);
            $this->messageManager->addSuccessMessage(
                __('Pattern was saved')
            );

            return $this->returnResult($redirectBack, $resultRedirect, $patternId);
        }

        try {
            if ($patternModel->validatePattern($params['pattern'])) {
                $patternId = $patternModel->insertPattern($params);
                $this->messageManager->addSuccessMessage(__('Pattern was saved'));
            } else {
                $this->messageManager->addErrorMessage(
                    __('Please add {L} or {D}. Maximum 6 {D} and {L}.
                    Make sure the pattern code does not already exist')
                );
            }
        } catch (LocalizedException $e) {
            $this->logger->critical($e);
            $this->messageManager->addExceptionMessage($e);
            $redirectBack = $patternId ? true : 'new';
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage($e->getMessage());
            $redirectBack = $patternId ? true : 'new';
        }

        return $this->returnResult($redirectBack, $resultRedirect, $patternId);
    }

    /**
     * Return result
     *
     * @param string|boolean $redirectBack
     * @param \Magento\Framework\Controller\Result\Redirect $resultRedirect
     * @param integer|null $patternId
     * @return mixed
     */
    protected function returnResult($redirectBack, $resultRedirect, $patternId)
    {
        if ($redirectBack === 'new') {
            $resultRedirect->setPath(
                'giftcard/pattern/new'
            );
        } elseif ($redirectBack === 'edit') {
            $resultRedirect->setPath(
                'giftcard/pattern/edit',
                ['id' => $patternId]
            );
        } else {
            $resultRedirect->setPath('giftcard/giftcard/pattern');
        }

        return $resultRedirect;
    }
}
