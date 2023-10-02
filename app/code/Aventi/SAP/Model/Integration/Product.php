<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Model\Integration;

use Aventi\SAP\Helper\Attribute;
use Aventi\SAP\Helper\Data;
use Aventi\SAP\Logger\Logger;
use Aventi\SAP\Model\Integration\Check\Product\CheckFields;
use Aventi\SAP\Model\Integration\Save\Product\Save;
use Bcn\Component\Json\Reader;
use Magento\Catalog\Api\CategoryLinkManagementInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ProductFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\DriverInterface;
use Magento\Store\Api\StoreRepositoryInterface;
use Magento\Store\Api\WebsiteRepositoryInterface;
use Magento\Tax\Model\TaxRuleRepository;

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
     * @var \Magento\Framework\App\ResourceConnection
     */
    private \Magento\Framework\App\ResourceConnection $_resourceConnection;

    private \Magento\Store\Api\StoreRepositoryInterface $storeRepository;

    private \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository;

    /**
     * @param Attribute $attributeDate
     * @param Logger $logger
     * @param DriverInterface $driver
     * @param Filesystem $filesystem
     * @param Data $data
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param TaxRuleRepository $taxRuleRepository
     * @param ManagerInterface $eventManager
     * @param ProductRepositoryInterface $productRepository
     * @param CheckFields $checkFields
     * @param Save $saveProduct
     * @param CategoryLinkManagementInterface $categoryLinkManagement
     * @param ProductFactory $productFactory
     * @param ResourceConnection $resourceConnection
     * @param StoreRepositoryInterface $storeRepository
     * @param WebsiteRepositoryInterface $websiteRepository
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
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \Magento\Store\Api\StoreRepositoryInterface $storeRepository,
        \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepository,
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
        $this->_resourceConnection = $resourceConnection;
        $this->storeRepository = $storeRepository;
        $this->websiteRepository = $websiteRepository;
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
                $reader->enter(null, Reader::TYPE_OBJECT);
                $total = (int)$reader->read('total');
                $products = $reader->read('data');

                $progressBar = $this->startProgressBar($total);
                foreach ($products as $product) {
                    $itemObject = (object) [
                        'sku' => $product['ItemCode'],
                        'name' => strtoupper(!empty($product['ItemName']) ? $product['ItemName'] : $product['ItemCode']),
                        'tax_class_id' => $this->getTax($product['TaxCodeAR']),
                        'status' => $this->getStatus($product['frozenFor']),
                        'mgs_brand' => $this->getBrandIdByFirmCode($product['U_LINEA']),
                        'short_description' => "",//$product['Description'],
                        'description' => $product['FrgnName'],
//                        'category_ids' => $this->attributeDate->getCategoryIds($product),
                        'custom_attributes' => [
                            'presentation' => $product['SalUnitMsr'],
                            'invima_registration' => ''//$product['U_invima']
                        ],
                        'website_code' => $product['U_LINEA1']
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
//            $checkCategories = $this->checkFields->checkCategories($itemObject, $item);

//            if (!$resultCheck && !$checkCategories) {
            if (!$resultCheck) {
                $this->resTable['check']++;
            } else {
                if ($resultCheck) {
                    $this->saveProduct->saveFields($this->getDataCheck($item, $resultCheck));
                    $this->eventManager->dispatch('sap_product_save_after', [
                        'product' => $item
                    ]);
                }
//                if ($checkCategories) {
//                    $this->categoryLinkManagement->assignProductToCategories(
//                        $itemObject->sku,
//                        $itemObject->category_ids
//                    );
//                }
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

        try {
            $websiteId = $this->getWebsiteIds($itemObject->website_code);
            $newProduct->setWebsiteIds([$websiteId]);
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $this->logger->error("Website is not found: " . $e->getMessage());
        }

        $newProduct->setSku($itemObject->sku);
        $newProduct->setName($itemObject->name);
        $newProduct->setAttributeSetId(4);
        $newProduct->setVisibility(4);
        $newProduct->setTaxClassId($itemObject->tax_class_id);
        $newProduct->setStatus($status);
        $newProduct->setPrice(0);
        $newProduct->setQty(0);
        $newProduct->setTypeId(\Magento\Catalog\Model\Product\Type::TYPE_SIMPLE);
//        $newProduct->setDescription($itemObject->long_description);
        $newProduct->setShortDescription($itemObject->short_description);
        $newProduct->setCustomAttributes($itemObject->custom_attributes);
        $newProduct->setUrlKey($urlKey);

        try {
            $this->productRepository->save($newProduct);
            $this->eventManager->dispatch('sap_product_save_after', [
                'product' => $newProduct
            ]);

//            $this->categoryLinkManagement->assignProductToCategories($itemObject->sku, $itemObject->category_ids);
            $this->resTable['new']++;
        } catch (
            \Magento\Framework\Exception\CouldNotSaveException |
            \Magento\Framework\Exception\InputException |
            \Magento\Framework\Exception\StateException $e) {
            $this->logger->error("An error has occurred creating product: " . $e->getMessage());
        }
    }

    /**
     * @param $websiteCode
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getWebsiteIds($websiteCode)
    {
        return match ($websiteCode) {
            'HEALTHY SPORTS' => $this->websiteRepository->get('healthy_sports')->getId(),
            'NUTRIVITA' => $this->websiteRepository->get('nutrivita')->getId(),
            default => $this->websiteRepository->get('base')->getId(),
        };
    }

    /**
     * @param $tax
     * @return int
     * @throws \Magento\Framework\Exception\InputException
     */
    private function getTax($tax)
    {
        $taxClassId = null;
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('code', $tax, 'eq')
            ->create();

        $rateItems = $this->taxRuleRepository->getList($searchCriteria)->getItems();

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
        return $url . '-' . 'as' . $rand . $randomString;
    }

    /**
     * @param $firmCode
     * @return int
     */
    private function getBrandIdByFirmCode($firmCode)
    {
        $connection = $this->_resourceConnection->getConnection();
        $tableName = $this->_resourceConnection->getTableName('mgs_brand');
        $selectQry = $connection->select()->from($tableName)->where('firm_code = ?', $firmCode);
        $brand = $connection->fetchRow($selectQry);
        return $brand ? (int)$brand['option_id'] : -1;
    }
}
