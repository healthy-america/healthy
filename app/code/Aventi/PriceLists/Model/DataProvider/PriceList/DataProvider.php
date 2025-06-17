<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\PriceLists\Model\DataProvider\PriceList;

use Aventi\PriceLists\Api\Data\PriceListGroupsInterface;
use Aventi\PriceLists\Model\ResourceModel\PriceList\CollectionFactory;
use Aventi\PriceLists\Model\ResourceModel\PriceListGroups\CollectionFactory as CustomerGroupsCollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
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
     * @var CustomerGroupsCollectionFactory
     */
    private CustomerGroupsCollectionFactory $customerGroupsCollectionFactory;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param CustomerGroupsCollectionFactory $customerGroupsCollectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(//NOSONAR
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        CustomerGroupsCollectionFactory $customerGroupsCollectionFactory,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $collectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->customerGroupsCollectionFactory = $customerGroupsCollectionFactory;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * @inheritDoc
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        foreach ($items as $model) {
            $this->loadedData[$model->getId()]['general'] = $model->getData();
            $this->loadedData[$model->getId()]['general']['customer_groups'] = $this->getCustomerGroups($model);
        }
        $data = $this->dataPersistor->get(\Aventi\PriceLists\Model\PriceList::TABLE);
        if (!empty($data)) {
            $model = $this->collection->getNewEmptyItem();
            $model->setData($data);
            $this->loadedData[$model->getId()]['general'] = $model->getData();
            $this->loadedData[$model->getId()]['general']['customer_groups'] = $this->getCustomerGroups($model);
            $this->dataPersistor->clear(\Aventi\PriceLists\Model\PriceList::TABLE);
        }

        return $this->loadedData;
    }

    /**
     * @param $model
     * @return string
     */
    private function getCustomerGroups($model): string
    {
        $groups = '';
        $collection = $this->customerGroupsCollectionFactory->create();
        $collection->addFieldToFilter(
            PriceListGroupsInterface::PRICE_LIST_ID,
            $model->getId()
        );
        foreach ($collection->getItems() as $group) {
            $customerGroupId = $group->getCustomerGroupId();
            $groups .= $customerGroupId . ',';
        }
        return  rtrim($groups, ',');
    }
}
