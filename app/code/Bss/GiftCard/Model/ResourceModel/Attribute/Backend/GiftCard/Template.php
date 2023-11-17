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
 * Class template
 *
 * Bss\GiftCard\Model\ResourceModel\Attribute\Backend\GiftCard
 */
class Template extends AbstractDb
{
    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('bss_giftcard_product_template', 'id');
    }

    /**
     * Load template data
     *
     * @param int $productId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadTemplateData($productId)
    {
        $select = $this->getConnection()->select()
            ->from($this->getMainTable(), 'template_id')
            ->where('product_id = ?', $productId);
        return $this->getConnection()->fetchCol($select);
    }

    /**
     * Get count by template
     *
     * @param int $templateId
     * @return mixed
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCountByTemplate($templateId)
    {
        $select = $this->getConnection()->select()
            ->from($this->getMainTable(), 'count(id) as count')
            ->where('template_id = ?', $templateId);
        $data = $this->getConnection()->fetchRow($select);
        return $data['count'];
    }

    /**
     * Save template data
     *
     * @param int $productId
     * @param array $params
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function saveTemplateData($productId, $params = [])
    {
        $data = [];
        $this->deleteTemplateData($productId);

        if (!empty($params)) {
            foreach ($params as $templateId) {
                $data[] = [
                    'product_id' => $productId,
                    'template_id' => $templateId
                ];
            }
        }

        if (!empty($data)) {
            $this->getConnection()->insertMultiple(
                $this->getMainTable(),
                $data
            );
        }

        return $this;
    }

    /**
     * Delete template data
     *
     * @param int $productId
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteTemplateData($productId)
    {
        $where = [
            'product_id = ?' => $productId
        ];
        $connection = $this->getConnection();
        $connection->delete($this->getMainTable(), $where);
        return $this;
    }
}
