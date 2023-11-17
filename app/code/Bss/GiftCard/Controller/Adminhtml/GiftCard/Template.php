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

namespace Bss\GiftCard\Controller\Adminhtml\GiftCard;

use Bss\GiftCard\Controller\Adminhtml\AbstractGiftCard;

/**
 * Class template
 *
 * Bss\GiftCard\Controller\Adminhtml\GiftCard
 */
class Template extends AbstractGiftCard
{
    /**
     * Execute
     *
     * @return Page
     */
    public function execute()
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Bss_GiftCard::giftcard');
        $resultPage
            ->getConfig()
            ->getTitle()
            ->prepend(__('Gift Card Template'));

        return $resultPage;
    }
}
