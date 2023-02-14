<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Helper;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Model\Product;
use Magento\Eav\Model\Entity\Attribute\OptionLabel;
use Magento\Eav\Model\Entity\Attribute\Source\Table;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class Attribute extends AbstractHelper
{
    /**
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface
     */
    protected \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository;

    /**
     * @var array
     */
    protected array $attributeValues;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute\Source\TableFactory
     */
    protected \Magento\Eav\Model\Entity\Attribute\Source\TableFactory $tableFactory;

    /**
     * @var \Magento\Eav\Api\AttributeOptionManagementInterface
     */
    protected \Magento\Eav\Api\AttributeOptionManagementInterface $attributeOptionManagement;

    /**
     * @var \Magento\Eav\Api\Data\AttributeOptionLabelInterfaceFactory
     */
    protected \Magento\Eav\Api\Data\AttributeOptionLabelInterfaceFactory $optionLabelFactory;

    /**
     * @var \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory
     */
    protected \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory $optionFactory;

    /**
     * @var \Aventi\SAP\Model\ResourceModel\CatalogCategoryEntityVarchar\CollectionFactory
     */
    private \Aventi\SAP\Model\ResourceModel\CatalogCategoryEntityVarchar\CollectionFactory $_collectionFactory;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    private \Magento\Catalog\Model\CategoryFactory $_categoryFactory;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    private \Magento\Eav\Model\ResourceModel\Entity\Attribute $_attribute;

    /**
     * Data constructor.
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository
     * @param \Magento\Eav\Model\Entity\Attribute\Source\TableFactory $tableFactory
     * @param \Magento\Eav\Api\AttributeOptionManagementInterface $attributeOptionManagement
     * @param \Magento\Eav\Api\Data\AttributeOptionLabelInterfaceFactory $optionLabelFactory
     * @param \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory $optionFactory
     * @param \Aventi\SAP\Model\ResourceModel\CatalogCategoryEntityVarchar\CollectionFactory $collectionFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute $attribute
     * @param \Magento\Catalog\Model\CategoryFactory $categoryFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $attributeRepository,
        \Magento\Eav\Model\Entity\Attribute\Source\TableFactory $tableFactory,
        \Magento\Eav\Api\AttributeOptionManagementInterface $attributeOptionManagement,
        \Magento\Eav\Api\Data\AttributeOptionLabelInterfaceFactory $optionLabelFactory,
        \Magento\Eav\Api\Data\AttributeOptionInterfaceFactory $optionFactory,
        \Aventi\SAP\Model\ResourceModel\CatalogCategoryEntityVarchar\CollectionFactory $collectionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $attribute,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory
    ) {
        parent::__construct($context);

        $this->attributeRepository = $attributeRepository;
        $this->tableFactory = $tableFactory;
        $this->attributeOptionManagement = $attributeOptionManagement;
        $this->optionLabelFactory = $optionLabelFactory;
        $this->optionFactory = $optionFactory;
        $this->_collectionFactory = $collectionFactory;
        $this->_attribute = $attribute;
        $this->_categoryFactory = $categoryFactory;
    }

    /**
     * Get attribute by code.
     *
     * @param string $attributeCode
     * @return ProductAttributeInterface
     * @throws NoSuchEntityException
     */
    public function getAttribute(string $attributeCode): ProductAttributeInterface
    {
        return $this->attributeRepository->get($attributeCode);
    }

    /**
     * Find or create a matching attribute option
     *
     * @param string $attributeCode Attribute the option should exist in
     * @param string $label Label to find or add
     * @return int
     * @throws LocalizedException
     */
    public function createOrGetId(string $attributeCode, string $label)
    {
        if (strlen($label) < 1) {
            throw new LocalizedException(
                __('Label for %1 must not be empty.', $attributeCode)
            );
        }

        // Does it already exist?
        $optionId = $this->getOptionId($attributeCode, $label);

        if (!$optionId) {
            // If no, add it.

            /** @var OptionLabel $optionLabel */
            $optionLabel = $this->optionLabelFactory->create();
            $optionLabel->setStoreId(0);
            $optionLabel->setLabel($label);

            $option = $this->optionFactory->create();
            $option->setLabel($label);
            $option->setStoreLabels([$optionLabel]);
            $option->setSortOrder(0);
            $option->setIsDefault(false);

            $this->attributeOptionManagement->add(
                Product::ENTITY,
                $this->getAttribute($attributeCode)->getAttributeId(),
                $option
            );

            // Get the inserted ID. Should be returned from the installer, but it isn't.
            $optionId = $this->getOptionId($attributeCode, $label, true);
        }

        return $optionId;
    }

    /**
     * Find the ID of an option matching $label, if any.
     *
     * @param string $attributeCode Attribute code
     * @param string $label Label to find
     * @param bool $force If true, will fetch the options even if they're already cached.
     * @return int|false
     * @throws NoSuchEntityException
     */
    public function getOptionId(string $attributeCode, string $label, bool $force = false)
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attribute */
        $attribute = $this->getAttribute($attributeCode);
        //$this->brand->checkOrCreateMGSBrand($attributeCode); Pendiente para el tema.

        // Build option array if necessary
        if ($force === true || !isset($this->attributeValues[ $attribute->getAttributeId() ])) {
            $this->attributeValues[ $attribute->getAttributeId() ] = [];

            // We have to generate a new sourceModel instance each time through to prevent it from
            // referencing its _options cache. No other way to get it to pick up newly-added values.

            /** @var Table $sourceModel */
            $sourceModel = $this->tableFactory->create();
            $sourceModel->setAttribute($attribute);

            foreach ($sourceModel->getAllOptions() as $option) {
                $this->attributeValues[ $attribute->getAttributeId() ][ $option['label'] ] = $option['value'];
            }
        }

        // Return option ID if exists
        if (isset($this->attributeValues[ $attribute->getAttributeId() ][ $label ])) {
            return $this->attributeValues[ $attribute->getAttributeId() ][ $label ];
        }

        // Returns false if it does not exist
        return false;
    }

    /**
     * @param $category
     * @return array
     */
    public function getCategoryIds($category): array
    {
        $pCategory = $category['Cod_Categoria_3'] . '_3';

        if ($category['Des_Categoria_3'] === "" || $category['Des_Categoria_3'] === null) {
            $pCategory = $category['Cod_Categoria_2'] . '_2';
        }
        $categoryId = [];
        $parentCategories = [];
        $categoryFactory = $this->_categoryFactory->create();
        $sapAttributeId = $this->_attribute->getIdByCode('catalog_category', 'sap');
        $sapCategoryIds = $this->_collectionFactory->create()
            ->addFieldToFilter('attribute_id', ['eq' => $sapAttributeId])
            ->addFieldToFilter('value', ['eq' => $pCategory])
            ->getFirstItem();

        if ($sapCategoryIds->getData()) {
            $categoryId [] = $sapCategoryIds->getEntityId();
            $parentCategories = array_slice($categoryFactory->load($sapCategoryIds->getEntityId())->getParentIds(), 2);
        }

        return array_merge($categoryId, $parentCategories);
    }
}
