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

namespace Bss\GiftCard\Model\ResourceModel\Attribute\Backend\GiftCard;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Class amounts
 *
 * Bss\GiftCard\Model\ResourceModel\Attribute\Backend\GiftCard
 */
class Amounts extends AbstractDb
{
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
     * Load amount data
     *
     * @param int $productId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Db_Statement_Exception
     */
    public function loadAmountsData($productId)
    {
        $select = $this->getConnection()->select()->from(
            $this->getMainTable()
        )->where(
            'product_id = ?',
            $productId
        );

        $result = [];
        $query = $this->getConnection()->query($select);
        while ($row = $query->fetch()) {
            $result[] = $row;
        }

        return $result;
    }

    /**
     * Insert amount data
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $params
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveAmountsData($product, $params = [])
    {
        $productId = (int)$product->getId();
        $data = [];
        $dataUpdate = [];

        if (!empty($params)) {
            foreach ($params as $param) {
                if (isset($param['delete'])) {
                    $this->deleteAmountData($param['amount_id']);
                    continue;
                }
                $dataArr = [
                    'product_id' => $productId,
                    'value' => $param['value'],
                    'price' => $param['price'],
                    'website_id' => $param['website_id']
                ];
                if (isset($param['amount_id'])) {
                    $dataArr['amount_id'] = $param['amount_id'];
                    $dataUpdate[] = $dataArr;
                } else {
                    $data[] = $dataArr;
                }
            }
        }
        if (!empty($data)) {
            if (!empty($dataUpdate)) {
                $this->getConnection()->insertOnDuplicate(
                    $this->getMainTable(),
                    $dataUpdate
                );
            }
            $this->getConnection()->insertMultiple(
                $this->getMainTable(),
                $data
            );
        }
        return $this;
    }

    /**
     * Delete amount data
     *
     * @param int $productId
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteAmountsData($productId)
    {
        $where = [
            'product_id = ?' => $productId
        ];
        $connection = $this->getConnection();
        $connection->delete($this->getMainTable(), $where);
        return $this;
    }

    /**
     * Delete amount data
     *
     * @param int|string $amountId
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteAmountData($amountId)
    {
        $where = [
            'amount_id = ?' => $amountId
        ];
        $connection = $this->getConnection();
        $connection->delete($this->getMainTable(), $where);
        return $this;
    }
}
