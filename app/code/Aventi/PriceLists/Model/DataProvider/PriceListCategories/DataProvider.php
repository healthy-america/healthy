<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\PriceLists\Model\DataProvider\PriceListCategories;

use Aventi\PriceLists\Model\ResourceModel\PriceListCategory\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Registry;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Framework\View\Element\UiComponent\ContextInterface;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @inheritDoc
     */
    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var Registry
     */
    private Registry $coreRegistry;

    /**
     * @var ContextInterface
     */
    private ContextInterface $context;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param Registry $coreRegistry
     * @param ContextInterface $context
     * @param array $meta
     * @param array $data
     */
    public function __construct(//NOSONAR
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        Registry $coreRegistry,
        ContextInterface $context,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->coreRegistry = $coreRegistry;
        $this->context = $context;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @inheritDoc
     */
    public function getData()
    {
        $parentId = $this->context->getRequestParam('parent_id');
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $data = [
            'entity_id' => '',
            'price_list_id' => $parentId,
            'category_ids' => '',
            'category_price' => 0.0,
            'category_rule_type' => 'price',
        ];
        $this->loadedData[$data['entity_id']] = $data;
        $this->dataPersistor->clear(\Aventi\PriceLists\Model\PriceListCategory::TABLE);

        return $this->loadedData;
    }
}
