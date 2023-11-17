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

use Magento\Framework\DataObjectFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * Class pattern
 *
 * Bss\GiftCard\Model\ResourceModel
 */
class Pattern extends AbstractDb
{
    /**
     * Date model
     *
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $date;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @param Context $context
     * @param DateTime $date
     * @param DataObjectFactory $dataObjectFactory
     */
    public function __construct(
        Context $context,
        DateTime $date,
        DataObjectFactory $dataObjectFactory
    ) {
        parent::__construct($context);
        $this->date = $date;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('bss_giftcard_pattern', 'pattern_id');
    }

    /**
     * Inserts gallery value to DB and retrieve last Id.
     *
     * @param array $data
     * @param null|int $patternId
     * @throws LocalizedException
     * @since 101.0.0
     */
    public function insertPatternGeneral($data, $patternId = null)
    {
        $dataObject = $this->dataObjectFactory->create();
        $data = $this->_prepareDataForTable(
            $dataObject->setData($data),
            $this->getMainTable()
        );
        if ($patternId) {
            unset($data['pattern_code_qty_max']);
            $this->updateTemplateGeneral($data, $patternId);
        } else {
            $this->getConnection()->insert($this->getMainTable(), $data);
            $patternId = $this->getConnection()->lastInsertId($this->getMainTable());
        }
        return $patternId;
    }

    /**
     * Update template general
     *
     * @param array $data
     * @param int $patternId
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function updateTemplateGeneral($data, $patternId)
    {
        $where = ['pattern_id = ?' => $patternId];
        $this->getConnection()->update($this->getMainTable(), $data, $where);
    }

    /**
     * Update pattern code qtu
     *
     * @param array $data
     * @param int $patternId
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function updatePatternCodeQty($data, $patternId)
    {
        $where = ['pattern_id = ?' => $patternId];
        $this->getConnection()->update($this->getMainTable(), $data, $where);
    }

    /**
     * Validate pattern code
     *
     * @param string $patternCode
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validatePatternCode($patternCode)
    {
        $select = $this->getConnection()->select()->from(
            $this->getMainTable()
        )->where(
            'pattern = ?',
            $patternCode
        );
        return $this->getConnection()->fetchRow($select);
    }
}
