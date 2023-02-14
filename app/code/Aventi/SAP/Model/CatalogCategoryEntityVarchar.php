<?php
/**
 * Copyright © Aventi All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Model;

use Aventi\SAP\Api\Data\CatalogCategoryEntityVarcharInterface;
use Aventi\SAP\Api\Data\CatalogCategoryEntityVarcharInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;

class CatalogCategoryEntityVarchar extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var string
     */
    protected $_eventPrefix = 'catalog_category_entity_varchar';

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * @var CatalogCategoryEntityVarcharInterfaceFactory
     */
    protected $catalog_category_entity_varcharDataFactory;

    /**
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param CatalogCategoryEntityVarcharInterfaceFactory $catalog_category_entity_varcharDataFactory
     * @param DataObjectHelper $dataObjectHelper
     * @param \Aventi\SAP\Model\ResourceModel\CatalogCategoryEntityVarchar $resource
     * @param \Aventi\SAP\Model\ResourceModel\CatalogCategoryEntityVarchar\Collection $resourceCollection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        CatalogCategoryEntityVarcharInterfaceFactory $catalog_category_entity_varcharDataFactory,
        DataObjectHelper $dataObjectHelper,
        \Aventi\SAP\Model\ResourceModel\CatalogCategoryEntityVarchar $resource,
        \Aventi\SAP\Model\ResourceModel\CatalogCategoryEntityVarchar\Collection $resourceCollection,
        array $data = []
    ) {
        $this->catalog_category_entity_varcharDataFactory = $catalog_category_entity_varcharDataFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Retrieve catalog_category_entity_varchar model with catalog_category_entity_varchar data
     * @return CatalogCategoryEntityVarcharInterface
     */
    public function getDataModel()
    {
        $catalog_category_entity_varcharData = $this->getData();

        $catalog_category_entity_varcharDataObject = $this->catalog_category_entity_varcharDataFactory->create();
        $this->dataObjectHelper->populateWithArray(
            $catalog_category_entity_varcharDataObject,
            $catalog_category_entity_varcharData,
            CatalogCategoryEntityVarcharInterface::class
        );

        return $catalog_category_entity_varcharDataObject;
    }
}
