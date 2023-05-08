<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Model\Integration;

use Bcn\Component\Json\Reader;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Validation\ValidationException;
use Magento\InventoryApi\Api\Data\SourceItemInterface;

class Stock extends \Aventi\SAP\Model\Integration
{
    const TYPE_URI = 'stock';
    const DEFAULT_SOURCE = 'default';

    private array $resTable = [
        'check' => 0,
        'fail' => 0,
        'new' => 0,
        'updated' => 0
    ];

    /**
     * @var \Magento\InventoryApi\Api\SourceItemsSaveInterface
     */
    private \Magento\InventoryApi\Api\SourceItemsSaveInterface $sourceItemsSaveInterface;

    /**
     * @var \Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory
     */
    private \Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory $sourceItemFactory;

    /**
     * @var \Aventi\SAP\Helper\Data
     */
    private \Aventi\SAP\Helper\Data $data;

    /**
     * @var \Aventi\SAP\Model\Integration\Check\Product\CheckStock
     */
    private \Aventi\SAP\Model\Integration\Check\Product\CheckStock $check;

    /**
     * @var \Magento\Framework\Api\FilterBuilder
     */
    private \Magento\Framework\Api\FilterBuilder $_filterBuilder;
    /**
     * @var \Magento\Framework\Api\Search\FilterGroupBuilder
     */
    private \Magento\Framework\Api\Search\FilterGroupBuilder $_filterGroupBuilder;

    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private \Magento\Framework\Api\SearchCriteriaBuilder $_searchCriteriaBuilder;

    /**
     * @var \Magento\InventoryApi\Api\SourceItemRepositoryInterface
     */
    private \Magento\InventoryApi\Api\SourceItemRepositoryInterface $_sourceItemRepositoryInterface;

    /**
     * @var \Magento\InventoryApi\Api\Data\SourceInterfaceFactory
     */
    private \Magento\InventoryApi\Api\Data\SourceInterfaceFactory $sourceFactory;

    /**
     * @var \Magento\InventoryApi\Api\SourceRepositoryInterface
     */
    private \Magento\InventoryApi\Api\SourceRepositoryInterface $sourceRepository;

    public function __construct(
        \Aventi\SAP\Helper\Attribute $attributeDate,
        \Aventi\SAP\Logger\Logger $logger,
        \Magento\Framework\Filesystem\DriverInterface $driver,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\InventoryApi\Api\SourceItemsSaveInterface $sourceItemsSaveInterface,
        \Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory $sourceItemFactory,
        \Aventi\SAP\Helper\Data $data,
        \Aventi\SAP\Model\Integration\Check\Product\CheckStock $check,
        \Magento\Framework\Api\FilterBuilder $filterBuilder,
        \Magento\Framework\Api\Search\FilterGroupBuilder $filterGroupBuilder,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\InventoryApi\Api\SourceItemRepositoryInterface $sourceItemRepositoryInterface,
        \Magento\InventoryApi\Api\Data\SourceInterfaceFactory $sourceFactory,
        \Magento\InventoryApi\Api\SourceRepositoryInterface $sourceRepository
    ) {
        parent::__construct($attributeDate, $logger, $driver, $filesystem);
        $this->sourceItemsSaveInterface = $sourceItemsSaveInterface;
        $this->sourceItemFactory = $sourceItemFactory;
        $this->data = $data;
        $this->check = $check;
        $this->_filterBuilder = $filterBuilder;
        $this->_filterGroupBuilder = $filterGroupBuilder;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_sourceItemRepositoryInterface = $sourceItemRepositoryInterface;
        $this->sourceFactory = $sourceFactory;
        $this->sourceRepository = $sourceRepository;
    }

    public function test(array $data = null): void
    {
        $start = 0;
        $rows = 1000;

        $products = \Aventi\SAP\Model\Integration\Generator\Stock::getStock();
        $total = count($products);

        $progressBar = $this->startProgressBar($total);

        foreach ($products as $product) {
            $stockObject = (object) [
                'sku' => $product['ItemCode'],
                'qty' => ($product['Stock'] <= 0) ? 0 : $product['Stock'],
                'source' => $product['WhsCode'],
                'isInStock' => ($product['Stock'] <= 0) ? 0 : 1
            ];
            $this->managerStock($stockObject);
            $this->advanceProgressBar($progressBar);
            //Debug only
            $total--;
        }
        $start += $rows;
        $this->finishProgressBar($progressBar, $start, $rows);
        $progressBar = null;

        $this->printTable($this->resTable);
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException|\Bcn\Component\Json\Exception\ReadingError
     */
    public function process(array $data = null): void
    {
        $start = 0;
        $rows = 1000;
        $flag = true;

        while ($flag) {
            $jsonData = $this->data->getResource(self::TYPE_URI, $start, $rows, $data['fast']);
            $jsonPath = $this->getJsonPath($jsonData, self::TYPE_URI);
            if ($jsonPath) {
                $reader = $this->getJsonReader($jsonPath);
                $reader->enter(null, Reader::TYPE_OBJECT);
                $total = (int)$reader->read("total");
                $products = $reader->read("data");
                $progressBar = $this->startProgressBar($total);
                foreach ($products as $product) {
                    $stockObject = (object) [
                        'sku' => $product['ItemCode'],
                        'qty' => ($product['Stock'] <= 0) ? 0 : $product['Stock'],
                        'source' => $product['WhsCode'],
                        'sourceName' => $product['WhsName'],
                        'isInStock' => ($product['Stock'] <= 0) ? 0 : 1
                    ];
                    $this->managerStock($stockObject);
                    $this->advanceProgressBar($progressBar);
                    //Debug only
                    //$total--;
                }
                $start += $rows;
                $this->finishProgressBar($progressBar, $start, $rows);
                $progressBar = null;
                $this->closeFile($jsonPath);
                if ($total <= 0) {
                    $flag = false;
                }
            } else {
                $flag = false;
            }
        }
        $this->printTable($this->resTable);
    }

    /**
     * @param object $stockObject
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function managerStock(object $stockObject)
    {
        try {
            $this->checkSource($stockObject->source, $stockObject->sourceName);
            if (!$sourceItem = $this->getSourceBySku($stockObject->sku, $stockObject->source)) {
                $sourceItem = $this->sourceItemFactory->create();
            }
            $resultCheck = $this->check->checkData($stockObject, $sourceItem);
            if (!$resultCheck) {
                $this->resTable['check']++;
            } else {
                $this->resTable['updated']++;
                $sourceItem->setSourceCode($stockObject->source);
                $sourceItem->setSku($stockObject->sku);
                $sourceItem->setQuantity($stockObject->qty);
                $sourceItem->setStatus($stockObject->isInStock);
                $this->sourceItemsSaveInterface->execute([$sourceItem]);
            }
        } catch (NoSuchEntityException $e) {
            $this->resTable['fail']++;
        } catch (CouldNotSaveException | InputException | ValidationException $e) {
            $this->resTable['fail']++;
            $this->logger->error("An error has occurred creating stock: " . $e->getMessage());
        }
    }

    /**
     * @param $sku
     * @param $source
     * @return SourceItemInterface|null
     */
    public function getSourceBySku($sku, $source): ?\Magento\InventoryApi\Api\Data\SourceItemInterface
    {
        $filter1 = $this->_filterBuilder
            ->setField("sku")
            ->setValue($sku)
            ->setConditionType("eq")->create();

        $filterGroup1 = $this->_filterGroupBuilder
            ->addFilter($filter1)->create();

        $filter2 = $this->_filterBuilder
            ->setField("source_code")
            ->setValue($source)
            ->setConditionType("eq")->create();

        $filterGroup2 = $this->_filterGroupBuilder
            ->addFilter($filter2)->create();

        $searchCriteria = $this->_searchCriteriaBuilder
            ->setFilterGroups([$filterGroup1, $filterGroup2])
            ->create();
        $items = $this->_sourceItemRepositoryInterface->getList($searchCriteria)->getItems();

        $source = null;
        foreach ($items as $item) {
            $source = $item;
        }
        return $source;
    }

    public function checkSource($sourceCode, $sourceName)
    {
        try {
            $source = $this->sourceRepository->get($sourceCode);
            $source->setName($sourceName);
            $this->sourceRepository->save($source);
        } catch (NoSuchEntityException $e) {
            $this->createSource($sourceCode, $sourceName);
        } catch (CouldNotSaveException | InputException | ValidationException $e) {
            $this->logger->error("An error has occurred creating stock: " . $e->getMessage());
        }
    }

    public function createSource($sourceCode, $sourceName)
    {
        try {
            $source = $this->sourceFactory->create();
            $source->setSourceCode((string)$sourceCode);
            $source->setName($sourceName);
            $source->setCountryId("CO");
            $source->setRegionId(747);
            $source->setPostcode('760001');
            $source->setEnabled(true);
            $this->sourceRepository->save($source);
        } catch (CouldNotSaveException | InputException | ValidationException $e) {
            $this->logger->error("Este error");
            $this->logger->error($e);
        }
    }
}
