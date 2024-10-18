<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Model\Integration;

use Bcn\Component\Json\Reader;

class Price extends \Aventi\SAP\Model\Integration
{
    const TYPE_URI = 'price';

    private array $resTable = [
        'check' => 0,
        'fail' => 0,
        'new' => 0,
        'updated' => 0
    ];

    /**
     * @var \Aventi\SAP\Helper\Data
     */
    private \Aventi\SAP\Helper\Data $data;

    /**
     * @var \Aventi\SAP\Model\Integration\Check\Product\CheckPrice
     */
    private \Aventi\SAP\Model\Integration\Check\Product\CheckPrice $check;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private \Magento\Catalog\Api\ProductRepositoryInterface $productRepository;

    /**
     * @var \Aventi\SAP\Model\Integration\Save\Product\Save
     */
    private \Aventi\SAP\Model\Integration\Save\Product\Save $saveProduct;

    /**
     * @param \Aventi\SAP\Helper\Attribute $attributeDate
     * @param \Aventi\SAP\Logger\Logger $logger
     * @param \Magento\Framework\Filesystem\DriverInterface $driver
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Aventi\SAP\Helper\Data $data
     * @param \Aventi\SAP\Model\Integration\Check\Product\CheckPrice $check
     * @param \Aventi\SAP\Model\Integration\Save\Product\Save $saveProduct
     */
    public function __construct(
        \Aventi\SAP\Helper\Attribute $attributeDate,
        \Aventi\SAP\Logger\Logger $logger,
        \Magento\Framework\Filesystem\DriverInterface $driver,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Aventi\SAP\Helper\Data $data,
        \Aventi\SAP\Model\Integration\Check\Product\CheckPrice $check,
        \Aventi\SAP\Model\Integration\Save\Product\Save $saveProduct,
    ) {
        parent::__construct($attributeDate, $logger, $driver, $filesystem);
        $this->productRepository = $productRepository;
        $this->data = $data;
        $this->check = $check;
        $this->saveProduct = $saveProduct;
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
     *  @param array $data
     * @return void
     * @throws \Bcn\Component\Json\Exception\ReadingError
     * @throws \Magento\Framework\Exception\FileSystemException
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
                    $priceObject = (object) [
                        'sku' => $product['ItemCode'],
                        'price' => !empty($product['Price']) ? $product['Price'] : 0
                    ];
                    $this->managerPrice($priceObject);
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
    private function managerPrice(object $priceObject)
    {
        try {
            $item = $this->productRepository->get($priceObject->sku);
            $resultCheck = $this->check->checkData($priceObject, $item);
            if (!$resultCheck) {
                $this->resTable['check']++;
            } else {
                $this->resTable['updated']++;
                $this->saveProduct->saveFields($item, $resultCheck);
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->resTable['fail']++;
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->resTable['fail']++;
            $this->logger->error("An error has occurred creating price: " . $e->getMessage());
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
