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

namespace Bss\GiftCard\Model\ResourceModel\GiftCard;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class quote
 *
 * Bss\GiftCard\Model\ResourceModel\GiftCard
 */
class Quote extends AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('bss_giftcard_quote', 'id');
    }

    /**
     * Set gift card code
     *
     * @param mixed $quote
     * @param mixed $giftCardCode
     * @return int
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function setGiftCardCode($quote, $giftCardCode)
    {
        $giftCardData = [
            'quote_id' => $quote->getId(),
            'giftcard_code' => $giftCardCode->getCode()
        ];
        return $this->getConnection()->insert(
            $this->getMainTable(),
            $giftCardData
        );
    }

    /**
     * Get gift card code
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @return array|void
     * @throws LocalizedException
     * @throws \Zend_Db_Statement_Exception
     */
    public function getGiftCardCode($quote = null)
    {
        if ($quote && $quote->getId()) {
            return $this->getQuoteCodeById($quote->getId());
        }
    }

    /**
     * Get quote code
     *
     * @param int $quoteId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Db_Statement_Exception
     */
    public function getQuoteCodeById($quoteId)
    {
        $select = $this->getConnection()->select()->from(
            $this->getMainTable()
        )->where(
            'quote_id = ?',
            $quoteId
        );

        $result = [];
        $query = $this->getConnection()->query($select);
        while ($row = $query->fetch()) {
            $result[] = $row;
        }
        return $result;
    }

    /**
     * Validate quote
     *
     * @param mixed $quote
     * @param string $giftCardCode
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validateQuote($quote, $giftCardCode)
    {
        $select = $this->getConnection()->select()->from(
            $this->getMainTable()
        )->where(
            'quote_id = ?',
            $quote->getId()
        )->where(
            'giftcard_code = ?',
            $giftCardCode
        );
        return $this->getConnection()->fetchOne($select);
    }

    /**
     * Remove pattern code from quote cart
     *
     * @param int $giftCardQuoteId
     * @param int|null $quoteId
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function removeGiftCardQuote($giftCardQuoteId, $quoteId = null)
    {
        $select = $this->getConnection()->select()
            ->from($this->getMainTable())
            ->where('id = ?', $giftCardQuoteId);
        if ($quoteId) {
            $select->where('quote_id = ?', $quoteId);
        }
        if (!$this->getConnection()->fetchOne($select)) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __("Could not found gift card code")
            );
        }
        return (bool) $this->getConnection()->delete(
            $this->getMainTable(),
            [
                'id = ?' => $giftCardQuoteId
            ]
        );
    }

    /**
     * Add use amount
     *
     * @param int $id
     * @param string $amount
     * @param string $baseAmount
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function addUseAmount($id, $amount, $baseAmount)
    {
        $connection = $this->getConnection();
        $bind = [
            'giftcard_amount' => $amount,
            'base_giftcard_amount' => $baseAmount
        ];
        $connection->update($this->getMainTable(), $bind, ['id = ?' => $id]);
        return $this;
    }
}
