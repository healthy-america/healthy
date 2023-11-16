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

namespace Bss\GiftCard\Model\ResourceModel\Pattern;

use Bss\GiftCard\Model\Pattern\CodeFactory;
use Bss\GiftCard\Model\Pattern\HistoryFactory;
use Bss\GiftCard\Model\PatternFactory;
use Bss\GiftCard\Model\ResourceModel\Pattern;
use Magento\Framework\DataObjectFactory;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;

/**
 * Class code
 *
 * Bss\GiftCard\Model\ResourceModel\Pattern
 */
class Code extends AbstractDb
{
    /**
     * @var CodeFactory
     */
    private $codeFactory;

    /**
     * @var PatternFactory
     */
    private $patternFactory;

    /**
     * @var Pattern
     */
    private $patternResourceModel;

    /**
     * @var HistoryFactory
     */
    private $historyFactory;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * Code constructor.
     *
     * @param Context $context
     * @param CodeFactory $codeFactory
     * @param Pattern $patternResourceModel
     * @param PatternFactory $patternFactory
     * @param HistoryFactory $historyFactory
     * @param DataObjectFactory $dataObjectFactory
     * @param null|string $connectionName
     */
    public function __construct(
        Context $context,
        CodeFactory $codeFactory,
        Pattern $patternResourceModel,
        PatternFactory $patternFactory,
        HistoryFactory $historyFactory,
        DataObjectFactory $dataObjectFactory,
        $connectionName = null
    ) {
        $this->codeFactory = $codeFactory;
        $this->patternFactory = $patternFactory;
        $this->patternResourceModel = $patternResourceModel;
        $this->historyFactory = $historyFactory;
        $this->dataObjectFactory = $dataObjectFactory;
        parent::__construct(
            $context,
            $connectionName
        );
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('bss_giftcard_pattern_code', 'code_id');
    }

    /**
     * Insert code
     *
     * @param mixed $data
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function insertCode($data)
    {
        $dataObject = $this->dataObjectFactory->create();
        $data = $this->_prepareDataForTable(
            $dataObject->setData($data),
            $this->getMainTable()
        );
        $this->getConnection()->insert($this->getMainTable(), $data);
        $this->updatePattern($data['pattern_id']);
    }

    /**
     * Validate code
     *
     * @param string $code
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validateCode($code)
    {
        $select = $this->getConnection()->select()->from(
            $this->getMainTable()
        )->where(
            'code =?',
            $code
        );
        return $this->getConnection()->fetchRow($select);
    }

    /**
     * Pattern
     *
     * @param int $patternId
     */
    private function updatePattern($patternId)
    {
        $codeModel = $this->codeFactory->create();
        $totalQty = $codeModel->getCollection()->filterByPattern($patternId)->count();
        $totalQtyUnused = $codeModel->getCollection()->filterByPatternUnused($patternId)->count();
        $pattern = $this->patternFactory->create()->load($patternId);
        $maxQty = (int)$pattern->getPatternCodeQtyMax();
        $data = [
            'pattern_code_qty' => $totalQty,
            'pattern_code_unused' => $totalQtyUnused,
            'pattern_code_qty_max' => $maxQty - 1
        ];
        $this->patternResourceModel->updatePatternCodeQty($data, $patternId);
    }

    /**
     * Status
     *
     * @param int $ids
     * @param string $status
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updateStatus($ids, $status)
    {
        $data = [
            'status' => $status
        ];
        $condition = ['code_id IN (?)' => $ids];
        $this->getConnection()->update($this->getMainTable(), $data, $condition);
        return $this;
    }

    /**
     * Save
     *
     * @param AbstractModel $object
     * @return AbstractDb|void
     */
    protected function _afterSave(\Magento\Framework\Model\AbstractModel $object)
    {
        parent::_afterSave($object);
        $patternId = $object->getPatternId();
        $this->updatePattern($patternId);
    }

    /**
     * Delete
     *
     * @param AbstractModel $object
     * @return AbstractDb|void
     */
    protected function _afterDelete(\Magento\Framework\Model\AbstractModel $object)
    {
        parent::_afterDelete($object);
        $patternId = $object->getPatternId();
        $this->updatePattern($patternId);
    }

    /**
     * Get codes linked to patternId
     *
     * @param int $patternId
     * @return array
     */
    public function getByPattern($patternId)
    {
        return $this->getCodesDataByPattern($patternId);
    }

    /**
     * Get codes data by pattern
     *
     * @param int $patternId
     * @return array
     */
    public function getCodesDataByPattern($patternId)
    {
        $connection = $this->getConnection();
        $select = $connection->select()->from(
            $this->getMainTable()
        )->where(
            'pattern_id =?',
            $patternId
        );
        $codes = [];
        $query = $connection->query($select);
        while ($row = $query->fetch()) {
            $codes[] = $row;
        }

        return $codes;
    }
}
