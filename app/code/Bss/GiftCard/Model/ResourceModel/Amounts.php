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

namespace Bss\GiftCard\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class amounts
 *
 * Bss\GiftCard\Model\ResourceModel
 */
class Amounts extends AbstractDb
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param Context $context
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager
    ) {
        parent::__construct($context);
        $this->storeManager = $storeManager;
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('bss_giftcard_amounts', 'amount_id');
    }

    /**
     * Validate
     *
     * @param int $amountId
     * @param int $productId
     * @param int|null $websiteId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validateAmounts($amountId, $productId, $websiteId = null)
    {
        if (!$websiteId) {
            $websiteId = $this->storeManager->getWebsite()->getWebsiteId();
        }
        $select = $this->getConnection()->select()->from(
            $this->getMainTable()
        )->where(
            'amount_id = ?',
            $amountId
        )->where(
            '(website_id = ?) OR (website_id = 0)',
            $websiteId
        )->where(
            'product_id = ?',
            $productId
        );
        return $this->getConnection()->fetchRow($select);
    }
}
