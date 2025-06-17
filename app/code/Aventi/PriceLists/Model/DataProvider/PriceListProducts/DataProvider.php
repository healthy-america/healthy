<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\PriceLists\Model\DataProvider\PriceListProducts;

use Aventi\PriceLists\Model\ResourceModel\PriceListProducts\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

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
            'product_ids' => '',
            'product_price' => 0.0,
            'product_price_sug' => 0.0,
            'product_rule_type' => 'price',
        ];
        $this->loadedData[$data['entity_id']] = $data;
        $this->dataPersistor->clear(\Aventi\PriceLists\Model\PriceListProducts::TABLE);

        return $this->loadedData;
    }
}
