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
namespace Bss\GiftCard\Cron;

use Bss\GiftCard\Helper\Data as GiftCardData;
use Bss\GiftCard\Model\Config\Source\Status;
use Bss\GiftCard\Model\Email;
use Bss\GiftCard\Model\Pattern\CodeFactory;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;

/**
 * Class update expiredCode
 *
 * Bss\GiftCard\Cron
 */
class UpdateExpiredCode
{
    /**
     * @var CodeFactory
     */
    private $codeFactory;

    /**
     * @var DateTimeFactory
     */
    private $dateFactory;

    /**
     * @var Email
     */
    private $emailModel;

    /**
     * @var GiftCardData
     */
    private $giftCardHelper;

    /**
     * @param DateTimeFactory $dateFactory
     * @param CodeFactory $codeFactory
     * @param Email $emailModel
     * @param GiftCardData $giftCardHelper
     */
    public function __construct(
        DateTimeFactory $dateFactory,
        CodeFactory $codeFactory,
        Email $emailModel,
        GiftCardData $giftCardHelper
    ) {
        $this->dateFactory = $dateFactory;
        $this->codeFactory = $codeFactory;
        $this->emailModel = $emailModel;
        $this->giftCardHelper = $giftCardHelper;
    }

    /**
     * Execute
     *
     * @return $this
     */
    public function execute()
    {
        // send email notify before x days
        $gmtDate = $this->dateFactory->create()->gmtDate();
        $daysBeforeNotify = (int)$this->giftCardHelper->getConfigEmail('day_before_notify_expire');
        $gmtDate = date('Y-m-d H:i:s', (strtotime($gmtDate) + (86400 * $daysBeforeNotify)));

        $collection = $this->codeFactory->create()->getCollection()->addFieldToFilter(
            'main_table.status',
            Status::BSS_GC_STATUS_ACTIVE
        )->addFieldToFilter(
            'sent_expire_notify',
            0
        )->addFieldToFilter(
            'expiry_day',
            ['notnull' => true]
        )->addFieldToFilter(
            'expiry_day',
            ['lt' => $gmtDate]
        );
        $this->emailModel->sendEmailNotify($collection);

        $this->setExpiredCode();

        return $this;
    }

    /**
     * Update status of expired codes
     */
    protected function setExpiredCode()
    {
        $gmtDate = $this->dateFactory->create()->gmtDate();
        $collection = $this->codeFactory->create()->getCollection()->addFieldToFilter(
            'main_table.status',
            Status::BSS_GC_STATUS_ACTIVE
        )->addFieldToFilter(
            'expiry_day',
            ['notnull' => true]
        )->addFieldToFilter(
            'expiry_day',
            ['lt' => $gmtDate]
        );
        $ids = $collection->getAllIds();
        if (!empty($ids)) {
            $this->codeFactory->create()->updateStatus($ids, Status::BSS_GC_STATUS_EXPIRED);
        }
    }
}
