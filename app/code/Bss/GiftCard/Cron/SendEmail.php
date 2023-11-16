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

use Bss\GiftCard\Model\Pattern\CodeFactory;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use Bss\GiftCard\Model\Email;

/**
 * Class send email
 *
 * Bss\GiftCard\Cron
 */
class SendEmail
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
    private $email;

    /**
     * @param DateTimeFactory $dateFactory
     * @param CodeFactory $codeFactory
     * @param Email $email
     */
    public function __construct(
        DateTimeFactory $dateFactory,
        CodeFactory $codeFactory,
        Email $email
    ) {
        $this->dateFactory = $dateFactory;
        $this->codeFactory = $codeFactory;
        $this->email = $email;
    }

    /**
     * Execute
     *
     * @return $this
     */
    public function execute()
    {
        $gmtDate = $this->dateFactory->create()->gmtDate();
        $collection = $this->codeFactory->create()->getCollection()->addFieldToFilter(
            'sent',
            0
        )->addFieldToFilter(
            'send_at',
            ['notnull' => true]
        )->addFieldToFilter(
            'send_at',
            ['lt' => $gmtDate]
        );
        foreach ($collection as $code) {
            $this->email->autoSendMail($code);
            $this->modifyRecord($code);
        }
        return $this;
    }

    /**
     * Modify record
     *
     * @param mixed $record
     */
    private function modifyRecord($record)
    {
        $record->setSent(true)->save();
    }
}
