<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Model\Integration;

use Aventi\SAP\Helper\Attribute;
use Aventi\SAP\Helper\Configuration;
use Aventi\SAP\Helper\Data;
use Aventi\SAP\Logger\Logger;
use Aventi\SAP\Model\Integration;
use Aventi\SAP\Model\Integration\Check\Product\CheckPrice;
use Aventi\SAP\Model\Integration\Manager\Price as PriceManager;
use Aventi\SAP\Model\Integration\Save\Product\Save;
use Bcn\Component\Json\Exception\ReadingError;
use Bcn\Component\Json\Reader;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\DriverInterface;

class Price extends Integration
{
    const TYPE_URI = 'price';

    private array $resTable = [
        'check' => 0,
        'fail' => 0,
        'new' => 0,
        'updated' => 0
    ];

    private mixed $defaultPriceList;

    /**
     * @var Data
     */
    private Data $data;

    /**
     * @var CheckPrice
     */
    private CheckPrice $check;

    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * @var Save
     */
    private Save $saveProduct;

    /**
     * @var PriceManager $priceManager
     */
    private PriceManager $priceManager;

    /**
     * @param Attribute $attributeDate
     * @param Logger $logger
     * @param DriverInterface $driver
     * @param Filesystem $filesystem
     * @param ProductRepositoryInterface $productRepository
     * @param Data $data
     * @param CheckPrice $check
     * @param Save $saveProduct
     * @param Configuration $configuration
     * @param PriceManager $priceManager
     */
    public function __construct(
        Attribute $attributeDate,
        Logger $logger,
        DriverInterface $driver,
        Filesystem $filesystem,
        ProductRepositoryInterface $productRepository,
        Data $data,
        CheckPrice $check,
        Save $saveProduct,
        Configuration $configuration,
        PriceManager  $priceManager,
    ) {
        parent::__construct($attributeDate, $logger, $driver, $filesystem);
        $this->productRepository = $productRepository;
        $this->data = $data;
        $this->check = $check;
        $this->saveProduct = $saveProduct;
        $this->priceManager = $priceManager;
        $this->defaultPriceList = $configuration->getDefaultPrice();
        $this->priceManager->setLogger($logger);
    }

    public function test(array $data = null): void
    {
        $start = 0;
        $rows = 1000;

        $products = \Aventi\SAP\Model\Integration\Generator\Price::getPrices();
        $total = count($products);

        $progressBar = $this->startProgressBar($total);

        foreach ($products as $product) {
            $priceObject = (object) [
                'sku' => $product['Sku'],
                'price' => $product['Price']
            ];
            $this->managerPrice($priceObject);
            $this->advanceProgressBar($progressBar);
            // Debug only.
            $total--;
        }
        $start += $rows;
        $this->finishProgressBar($progressBar, $start, $rows);
        $progressBar = null;
        $this->printTable($this->resTable);
    }

    /**
     * @param array|null $data
     * @return void
     * @throws FileSystemException
     * @throws ReadingError
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
                $total = (int)$reader->read('total');
                $products = $reader->read('data');
                $progressBar = $this->startProgressBar($total);
                foreach ($products as $product) {
                    foreach ($product['Detalle'] as $priceList) {
                        $priceObject = (object) [
                            'sku' => $product['ItemCode'],
                            'price' => (float)(!empty($priceList['Price']) ? $priceList['Price'] : 0),
                            'price_sug' => (float)$priceList['PriceList'],
                            'list' => $priceList['PriceList'],
                            'description' => $priceList['ListName']
                        ];
                        $this->managerPrice($priceObject);
                    }
                    $this->advanceProgressBar($progressBar);
                    // Debug only.
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
     * @param object $priceObject
     * @return void
     */
    private function managerPrice(object $priceObject): void
    {
        try {
            $item = $this->productRepository->get($priceObject->sku);
            if ($priceObject->list === $this->defaultPriceList) {
                $resultCheck = $this->check->checkData($priceObject, $item);
                if (!$resultCheck) {
                    $this->resTable['check']++;
                } else {
                    $this->saveProduct->saveFields($item, $resultCheck);
                    $this->resTable['updated']++;
                }
            }
            $index = $this->priceManager->addPriceList($item, $priceObject);

            $this->resTable[$index]++;
        } catch (\Exception $e) {
            $this->logger->error('SAP managerPrice: ' . $e->getMessage());
            $this->resTable['fail']++;
        }
    }

    /**
     * @param $item
     * @param $checkData
     * @return object
     */
    public function getDataCheck($item, $checkData): object
    {
        return (object)[
            'itemInterface' => $item,
            'itemRepositoryInterface' => $this->productRepository,
            'checkData' => $checkData
        ];
    }
}
