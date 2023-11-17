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
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;

/**
 * Class template
 *
 * Bss\GiftCard\Model\ResourceModel
 */
class Template extends AbstractDb
{

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTimeFactory
     */
    private $dateFactory;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * Template constructor.
     * @param Context $context
     * @param DateTimeFactory $dateFactory
     * @param DataObjectFactory $dataObjectFactory
     */
    public function __construct(
        Context $context,
        DateTimeFactory $dateFactory,
        DataObjectFactory $dataObjectFactory
    ) {
        parent::__construct($context);
        $this->dateFactory = $dateFactory;
        $this->dataObjectFactory = $dataObjectFactory;
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('bss_giftcard_template', 'template_id');
    }

    /**
     * Before save callback
     *
     * @param AbstractModel $object
     * @return mixed
     */
    public function _beforeSave(AbstractModel $object)
    {
        if ($object->isObjectNew()) {
            $object->setCreatedAt($this->dateFactory->create()->gmtDate());
        }

        $object->setUpdateAt($this->dateFactory->create()->gmtDate());
        return parent::_beforeSave($object);
    }

    /**
     * Inserts gallery value to DB and retrieve last Id.
     *
     * @param array $data
     * @param int|null $templateId
     * @return int
     * @since 101.0.0
     */
    public function insertTemplateGeneral($data, $templateId = null)
    {
        $dataObject = $this->dataObjectFactory->create();
        $dataObject->setData($data);
        $data = $this->_prepareDataForTable(
            $dataObject,
            $this->getMainTable()
        );
        if ($templateId) {
            $this->updateTemplateGeneral($data, $templateId);
        } else {
            $this->getConnection()->insert($this->getMainTable(), $data);
            $templateId = $this->getConnection()->lastInsertId($this->getMainTable());
        }
        return $templateId;
    }

    /**
     * Update template general
     *
     * @param array $data
     * @param int $templateId
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function updateTemplateGeneral($data, $templateId)
    {
        $where = ['template_id = ?' => $templateId];
        $this->getConnection()->update($this->getMainTable(), $data, $where);
    }
}
