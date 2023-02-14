<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Model\Integration;

class Product extends \Aventi\SAP\Model\Integration
{
    const TYPE_URI = 'product';

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
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var \Magento\Tax\Model\TaxRuleRepository
     */
    private \Magento\Tax\Model\TaxRuleRepository $taxRuleRepository;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private \Magento\Framework\Event\ManagerInterface $eventManager;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    private \Magento\Catalog\Api\ProductRepositoryInterface $productRepository;

    /**
     * @var \Aventi\SAP\Model\Integration\Check\Product\CheckFields
     */
    private \Aventi\SAP\Model\Integration\Check\Product\CheckFields $checkFields;

    /**
     * @var \Aventi\SAP\Model\Integration\Save\Product\Save
     */
    private \Aventi\SAP\Model\Integration\Save\Product\Save $saveProduct;

    /**
     * @var \Magento\Catalog\Api\CategoryLinkManagementInterface
     */
    private \Magento\Catalog\Api\CategoryLinkManagementInterface $categoryLinkManagement;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    private \Magento\Catalog\Model\ProductFactory $productFactory;

    /**
     * @param \Aventi\SAP\Helper\Attribute $attributeDate
     * @param \Aventi\SAP\Logger\Logger $logger
     * @param \Magento\Framework\Filesystem\DriverInterface $driver
     * @param \Magento\Framework\Filesystem $filesystem
     * @param \Aventi\SAP\Helper\Data $data
     * @param \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder
     * @param \Magento\Tax\Model\TaxRuleRepository $taxRuleRepository
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Aventi\SAP\Model\Integration\Check\Product\CheckFields $checkFields
     * @param \Aventi\SAP\Model\Integration\Save\Product\Save $saveProduct
     * @param \Magento\Catalog\Api\CategoryLinkManagementInterface $categoryLinkManagement
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     */
    public function __construct(
        \Aventi\SAP\Helper\Attribute $attributeDate,
        \Aventi\SAP\Logger\Logger $logger,
        \Magento\Framework\Filesystem\DriverInterface $driver,
        \Magento\Framework\Filesystem $filesystem,
        \Aventi\SAP\Helper\Data $data,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Tax\Model\TaxRuleRepository $taxRuleRepository,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Aventi\SAP\Model\Integration\Check\Product\CheckFields $checkFields,
        \Aventi\SAP\Model\Integration\Save\Product\Save $saveProduct,
        \Magento\Catalog\Api\CategoryLinkManagementInterface $categoryLinkManagement,
        \Magento\Catalog\Model\ProductFactory $productFactory
    ) {
        parent::__construct($attributeDate, $logger, $driver, $filesystem);

        $this->data = $data;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->taxRuleRepository = $taxRuleRepository;
        $this->eventManager = $eventManager;
        $this->productRepository = $productRepository;
        $this->checkFields = $checkFields;
        $this->saveProduct = $saveProduct;
        $this->categoryLinkManagement = $categoryLinkManagement;
        $this->productFactory = $productFactory;
    }

    public function test(array $data = null): void
    {
        $start = 0;
        $rows = 1000;

        $products = \Aventi\SAP\Model\Integration\Generator\Product::getProducts();
        $total = count($products);

        $progressBar = $this->startProgressBar($total);

        foreach ($products as $product) {
            $itemObject = (object) [
                'sku' => $product['Sku'],
                'name' => strtoupper($product['Name']),
                'tax_class_id' => $this->getTax($product['Tax']),
                'status' => $this->getStatus($product['frozenFor']),
                'mgs_brand' => $this->getBrandId($product['FirmCode']),
                'short_description' => $product['Description'],
                'long_description' => $product['LongDescription'],
                'category_ids' => $this->attributeDate->getCategoryIds($product),
                'custom_attributes' => [
                ]
            ];

            $this->managerProduct($itemObject);

            $this->advanceProgressBar($progressBar);
            // Debug only
            $total--;
        }
        $this->finishProgressBar($progressBar, $start, $rows);
        $progressBar = null;

        $this->printTable($this->resTable);
    }

    /**
     * Main procedure
     *
     * @param array|null $data
     * @return void
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
                $reader->enter(null, \Bcn\Component\Json\Reader::TYPE_OBJECT);
                $total = $reader->read('total');
                $products = $reader->read('data');
                $progressBar = $this->startProgressBar($total);
                foreach ($products as $product) {
                    $itemObject = (object) [
                        'sku' => $product['Sku'],
                        'name' => strtoupper($product['Name']),
                        'tax_class_id' => $this->getTax($product['Tax']),
                        'status' => $this->getStatus($product['frozenFor']),
                        'mgs_brand' => $this->getBrandId($product['FirmCode']),
                        'short_description' => $product['Description'],
                        //'long_description' => $product['LongDescription'],
                        'category_ids' => $this->attributeDate->getCategoryIds($product),
                        'custom_attributes' => [
                        ]
                    ];
                    $this->managerProduct($itemObject);
                    $this->advanceProgressBar($progressBar);
                    // Debug only
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
     * @param $itemObject
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function managerProduct($itemObject)
    {
        try {
            $item = $this->productRepository->get($itemObject->sku);
            $resultCheck = $this->checkFields->checkData($itemObject, $item);
            $checkCategories = $this->checkFields->checkCategories($itemObject, $item);

            if (!$resultCheck && !$checkCategories) {
                $this->resTable['check']++;
            } else {
                if ($resultCheck) {
                    $this->logger->debug(json_encode($this->getDataCheck($item, $resultCheck)));
                    $this->saveProduct->saveFields($this->getDataCheck($item, $resultCheck));
                    $this->eventManager->dispatch('sap_product_save_after', [
                        'product' => $item
                    ]);
                }
                if ($checkCategories) {
                    $this->categoryLinkManagement->assignProductToCategories(
                        $itemObject->sku,
                        $itemObject->category_ids
                    );
                }
                $this->resTable['updated']++;
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->createProduct($itemObject);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->resTable['fail']++;
            $this->logger->error("An error has occurred: " . $e->getMessage());
        }
    }

    /**
     * @param $itemObject
     * @return void
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     * @throws \Magento\Framework\Exception\InputException
     * @throws \Magento\Framework\Exception\StateException
     */
    private function createProduct($itemObject)
    {
        $status = $itemObject->status;

        $urlKey = $this->generateURL($itemObject->name);

        $newProduct = $this->productFactory->create();
        $newProduct->setSku($itemObject->sku);
        $newProduct->setName($itemObject->name);
        $newProduct->setAttributeSetId(4);
        $newProduct->setVisibility(4);
        $newProduct->setTaxClassId($itemObject->tax_class_id);
        $newProduct->setStatus($status);
        $newProduct->setPrice(0);
        $newProduct->setQty(0);
        $newProduct->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
        $newProduct->setDescription($itemObject->long_description);
        $newProduct->setShortDescription($itemObject->short_description);
        $newProduct->setCustomAttributes($itemObject->custom_attributes);
        $newProduct->setUrlKey($urlKey);

        try {
            $this->productRepository->save($newProduct);
            $this->eventManager->dispatch('sap_product_save_after', [
                'product' => $newProduct
            ]);

            $this->categoryLinkManagement->assignProductToCategories($itemObject->sku, $itemObject->category_ids);
            $this->resTable['new']++;
        } catch (
            \Magento\Framework\Exception\CouldNotSaveException |
            \Magento\Framework\Exception\InputException |
            \Magento\Framework\Exception\StateException $e) {
            $this->logger->error("An error has occurred creating product: " . $e->getMessage());
        }
    }

    /**
     * @param $tax
     * @return int
     * @throws \Magento\Framework\Exception\InputException
     */
    private function getTax($tax): int
    {
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('code', $tax, 'eq')
            ->create();

        $rateItems = $this->taxRuleRepository->getList($searchCriteria)->getItems();

        $taxClassId = 2;
        foreach ($rateItems as $rateItem) {
            $taxClassId = $rateItem->getProductTaxClassIds()[0];
        }

        return $taxClassId;
    }

    /**
     * Returns status Magento.
     * @param string $frozenFor Status from SAP (Y/N).
     * @return int Status Magento (0,1).
     */
    private function getStatus(string $frozenFor): int
    {
        if ($frozenFor == "N") {
            $status = \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED;
        } else {
            $status = \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED;
        }

        return $status;
    }

    /**
     * @param string $brand
     * @return mixed
     */
    private function getBrandId(string $brand)
    {
        $mgsBrand = [
            'optionId' => $this->getOptionId($brand, 'mgs_brand'),
            'optionLabel' => $brand
        ];
        $this->eventManager->dispatch('sap_attribute_save_after', [
            'brand' => $mgsBrand
        ]);
        return $mgsBrand['optionId'];
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

    /**
     * @param $name
     * @return string
     */
    private function generateURL($name): string
    {
        $url = preg_replace('#[^\da-z]+#i', '-', $name);
        $url = strtolower($url);
        return $this->generateNewUrl($url);
    }

    /**
     * @param string $url
     * @return string
     */
    private function generateNewUrl(string $url): string
    {
        $randomString = substr(str_shuffle("abcdefghijklmnopqrstuvwxyz"), 0, 5);
        $rand = rand(100, 999);
        return $url . '-' . 'as' . $rand  . $randomString;
    }

}
