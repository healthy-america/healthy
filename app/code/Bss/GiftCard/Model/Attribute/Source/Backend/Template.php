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

namespace Bss\GiftCard\Model\Attribute\Source\Backend;

use Bss\GiftCard\Model\ResourceModel\Attribute\Backend\GiftCard\Template as TemplateAttribute;
use Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend;
use Psr\Log\LoggerInterface;

/**
 * Class template
 *
 * Bss\GiftCard\Model\Attribute\Source\Backend
 */
class Template extends AbstractBackend
{
    /**
     * @var TemplateAttribute
     */
    private $templateAttribute;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param TemplateAttribute $templateAttribute
     * @param LoggerInterface $logger
     */
    public function __construct(
        TemplateAttribute $templateAttribute,
        LoggerInterface $logger
    ) {
        $this->templateAttribute = $templateAttribute;
        $this->logger = $logger;
    }

    /**
     * Save
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    public function afterSave($object)
    {
        $attributeCode = $this->getAttribute()->getName();
        $params = $object->getData($attributeCode);
        if (!empty($params)) {
            try {
                $productId = (int)$object->getId();
                $this->templateAttribute->saveTemplateData($productId, $params);
            } catch (\Exception $e) {
                $this->logger->critical($e);
            }
        }
        return $this;
    }

    /**
     * Load
     *
     * @param \Magento\Framework\DataObject $object
     * @return $this
     */
    public function afterLoad($object)
    {
        $attributeCode = $this->getAttribute()->getName();
        $data = [];
        try {
            $productId = (int)$object->getId();
            $data = $this->templateAttribute->loadTemplateData($productId);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
        if (!empty($data)) {
            $object->setData($attributeCode, $data);
        }
        return $this;
    }
}
