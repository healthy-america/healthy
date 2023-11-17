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

namespace Bss\GiftCard\Model;

use Bss\GiftCard\Api\Data\TemplateInterface;
use Bss\GiftCard\Api\TemplateRepositoryInterface;
use Bss\GiftCard\Model\ResourceModel\Attribute\Backend\GiftCard\Template as AttributeTemplate;
use Bss\GiftCard\Model\ResourceModel\Template as TemplateResourceModel;
use Bss\GiftCard\Model\Template\ImageFactory;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;

/**
 * Class template
 *
 * Bss\GiftCard\Model
 */
class Template extends AbstractModel implements TemplateInterface
{

    /**
     * @var AttributeTemplate
     */
    private $attributeTemplate;

    /**
     * @var TemplateRepositoryInterface
     */
    private $templateRepository;

    /**
     * @var ImageFactory
     */
    private $imageModel;

    /**
     * Template constructor.
     * @param Context $context
     * @param Registry $registry
     * @param ImageFactory $imageModel
     * @param AttributeTemplate $attributeTemplate
     * @param TemplateRepositoryInterface $templateRepository
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ImageFactory $imageModel,
        AttributeTemplate $attributeTemplate,
        TemplateRepositoryInterface $templateRepository
    ) {
        parent::__construct(
            $context,
            $registry
        );
        $this->imageModel = $imageModel;
        $this->attributeTemplate = $attributeTemplate;
        $this->templateRepository = $templateRepository;
    }

    /**
     * Construct
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(TemplateResourceModel::class);
    }

    /**
     * Insert template
     *
     * @param array $data
     * @return mixed
     */
    public function insertTemplate($data)
    {
        $template = [
            'name' => $data['name'],
            'status' => $data['status'],
            'code_color' => $data['code_color'],
            'message_color' => $data['message_color'],
        ];
        $templateId = $this->getResource()->insertTemplateGeneral($template, $data['template_id']);
        if (isset($data['bssGiftCard']) && !empty($data['bssGiftCard']) && $templateId) {
            $this->imageModel->create()->insertData($data['bssGiftCard'], $templateId);
        }
        return $templateId;
    }

    /**
     * Load product
     *
     * @param int $productId
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function loadProductTemplate($productId)
    {
        $data = [];
        $templateIds = $this->attributeTemplate->loadTemplateData($productId);
        if (!empty($templateIds)) {
            foreach ($templateIds as $templateId) {
                $template = $this->templateRepository->getTemplateById($templateId)['template_data'];
                if ($template['status']) {
                    $data[] = $template;
                }
            }
        }
        return $data;
    }

    /**
     * Before delete
     *
     * @return AbstractModel
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function beforeDelete()
    {
        //Check if there any product has already used this Template
        $templateAssignedCount = $this->attributeTemplate->getCountByTemplate($this->getId());
        if ($templateAssignedCount > 0) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __(
                    'There are some products using this Template. Please delete them first. (Template Id: %1)',
                    $this->getId()
                )
            );
        }
        return parent::beforeDelete();
    }

    /**
     * Check before disable template
     *
     * @param array $params
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function checkBeforeDisableTemplate($params)
    {
        if ($params['status'] == 0) {
            $templateAssignedCount = $this->attributeTemplate->getCountByTemplate($params['template_id']);
            if ($templateAssignedCount > 0) {
                return false;
            }
        }
        return true;
    }

    /**
     * @inheritDoc
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * @inheritDoc
     */
    public function setId($val)
    {
        return $this->setData(self::ID, $val);
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * @inheritDoc
     */
    public function setName($val)
    {
        return $this->setData(self::NAME, $val);
    }

    /**
     * @inheritDoc
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @inheritDoc
     */
    public function setStatus($val)
    {
        return $this->setData(self::STATUS, $val);
    }

    /**
     * @inheritDoc
     */
    public function getCodeColor()
    {
        return $this->getData(self::CODE_COLOR);
    }

    /**
     * @inheritDoc
     */
    public function setCodeColor($val)
    {
        return $this->setData(self::CODE_COLOR, $val);
    }

    /**
     * @inheritDoc
     */
    public function getMessageColor()
    {
        return $this->getData(self::MESSAGE_COLOR);
    }

    /**
     * @inheritDoc
     */
    public function setMessageColor($val)
    {
        return $this->setData(self::MESSAGE_COLOR, $val);
    }

    /**
     * @inheritDoc
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setCreatedAt($val)
    {
        return $this->setData(self::CREATED_AT, $val);
    }

    /**
     * @inheritDoc
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @inheritDoc
     */
    public function setUpdatedAt($val)
    {
        return $this->setData(self::UPDATED_AT, $val);
    }
}
