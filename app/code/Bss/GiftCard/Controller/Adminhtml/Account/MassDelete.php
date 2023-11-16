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
 * Class mass delete
 *
 * Bss\GiftCard\Controller\Adminhtml\Account
 */
class MassDelete extends AbstractGiftCard
{
    /**
     * Execute
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $collection = $this->filter->getCollection($this->codeCollection->create());
        try {
            $collectionSize = $collection->getSize();
            $collection->walk('delete');
            $this->messageManager->addSuccessMessage(
                __('A total of %1 record(s) have been deleted.', $collectionSize)
            );
        } catch (\Exception $e) {
            $this->logger->critical($e);
            $this->messageManager->addErrorMessage($e->getMessage());
        }

        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('giftcard/giftcard/account');
    }
}
