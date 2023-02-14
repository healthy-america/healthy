<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Model\Integration;

use Bcn\Component\Json\Exception\ReadingError;
use Bcn\Component\Json\Reader;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class Category extends \Aventi\SAP\Model\Integration
{
    const TYPE_URI = 'category';

    const  CATEGORY_INIT = 2;

    private array $resTable = [
        'check' => 0,
        'fail' => 0,
        'new' => 0,
        'updated' => 0
    ];

    /**
     * @var \Aventi\SAP\Helper\Password
     */
    private $_password;

    /**
     * @var \Magento\Catalog\Api\CategoryRepositoryInterface
     */
    private $_categoryRepositoryInterface;

    /**
     * @var \Magento\Catalog\Model\CategoryFactory
     */
    private $_categoryFactory;

    /**
     * @var \Aventi\SAP\Helper\Data
     */
    private $_helperData;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory
     */
    private $_categoryCollectionFactory;

    public function __construct(
        \Aventi\SAP\Helper\Attribute $attributeDate,
        \Aventi\SAP\Logger\Logger $logger,
        \Magento\Framework\Filesystem\DriverInterface $driver,
        \Magento\Framework\Filesystem $filesystem,
        \Aventi\SAP\Helper\Password $password,
        \Magento\Catalog\Api\CategoryRepositoryInterface $categoryRepositoryInterface,
        \Magento\Catalog\Model\CategoryFactory $categoryFactory,
        \Aventi\SAP\Helper\Data $helperData,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory
    ) {
        parent::__construct($attributeDate, $logger, $driver, $filesystem);
        $this->_password = $password;
        $this->_categoryRepositoryInterface = $categoryRepositoryInterface;
        $this->_categoryFactory = $categoryFactory;
        $this->_helperData = $helperData;
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
    }

    public function test(array $data = null): void
    {
        $start = 0;
        $rows = 1000;

        $categories = \Aventi\SAP\Model\Integration\Generator\Category::getCategories();
        $total = count($categories);

        $progressBar = $this->startProgressBar($total * 3);

        foreach ($categories as $category){
            $objCategory = (object) [
                'code_line' => $category['Cod_Categoria_1'] . '_1',
                'name_line' => $category['Des_Categoria_1'],
                'code_group' => $category['Cod_Categoria_2'] . '_2',
                'name_group' => $category['Des_Categoria_2'],
                'code_attribute' => $category['Cod_Categoria_3']. '_3',
                'name_attribute' => $category['Des_Categoria_3']
            ];

            $this->managerCategory($objCategory);

            $this->advanceProgressBar($progressBar);

            // Debug only
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
     */
    public function process(array $data = null): void
    {
        try {
            $this->updateCategories();
        } catch (\Exception $e) {
            $this->logger->debug('Error: Updated categories. ' . $e->getMessage());
        }
    }

    /**
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     * @throws ReadingError
     */
    private function updateCategories(): void
    {
        $start = 0;
        $rows = 1000;

        $jsonData = $this->_helperData->getResource(self::TYPE_URI, 0, 0, false);
        $jsonPath = $this->getJsonPath($jsonData, self::TYPE_URI);
        if ($jsonPath) {
            $reader = $this->getJsonReader($jsonPath);
            $reader->enter(null, Reader::TYPE_OBJECT);
            $total = $reader->read("total");
            $categories = $reader->read("data");

            $progressBar = $this->startProgressBar($total * 3);

            foreach ($categories as $category){
                $objCategory = (object) [
                    'code_line' => $category['Cod_Categoria_1'] . '_1',
                    'name_line' => $category['Des_Categoria_1'],
                    'code_group' => $category['Cod_Categoria_2'] . '_2',
                    'name_group' => $category['Des_Categoria_2'],
                    'code_attribute' => $category['Cod_Categoria_3']. '_3',
                    'name_attribute' => $category['Des_Categoria_3']
                ];

                $this->managerCategory($objCategory);

                $this->advanceProgressBar($progressBar);
            }
            $start += $rows;
            $this->finishProgressBar($progressBar, $start, $rows);
            $progressBar = null;
            $this->closeFile($jsonPath);
        }

        $this->printTable($this->resTable);
    }

    /**
     * @param $category
     */
    private function managerCategory($category)
    {
        try {
            $this->_createCategory($category->name_line, $category->code_line);
            $this->_createCategory($category->name_group, $category->code_group, $category->name_line, $category->code_line, 3);
            $this->_createCategory( $category->name_attribute, $category->code_attribute, $category->name_group, $category->code_group,  4);
        } catch (\Exception $e){
            $this->resTable['fail']++;
        }
    }

    /**
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function _addDefaultCategories()
    {
        $defaultCategories = [
            'Nuevos Productos' => -1,
            'Oferta del mes'  => -2,
            'Productos destacados'  => -3,
            'Más populares'  => -4,
            'Productos vistos' => -5
        ];
        foreach ($defaultCategories as $key => $sapId) {
            $this->_createCategory($key, $sapId);
        }
    }

    /**
     * @param $nameCategory
     * @param $idCategory
     * @param $nameParent
     * @param $idParent
     * @param $level
     * @return void
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function _createCategory($nameCategory, $idCategory, $nameParent = false, $idParent = -1 , $level = 2)
    {
        $parentCategory = false;
        $category = false;

        if ($level == 2) {
            $parentCategory = $this->_categoryRepositoryInterface->get(self::CATEGORY_INIT);
        } else if ($nameParent && $idParent){
            $parentCategory = $this->_getCategorySapId($idParent);
        } else {
            return;
        }

        $categoryKeyUrl = $this->_password->generateUrlCategory($nameCategory, 6);

        $category = $this->_getCategorySapId($idCategory);

        $this->_updateCategory($category, $idCategory, $nameCategory, $categoryKeyUrl, $parentCategory);
    }

    /**
     * Get category sap id
     *
     * @param string $sapId
     * @return DataObject
     * @throws LocalizedException
     */
    private function _getCategorySapId(string $sapId): \Magento\Framework\DataObject
    {
        $collection = $this->_categoryCollectionFactory->create();
        $collection->addAttributeToFilter('sap', $sapId);
        return $collection->getFirstItem();
    }

    /**
     * Get category by name and level
     *
     * @param string $name
     * @param string $level
     * @return DataObject
     * @throws LocalizedException
     */
    private function _getCategoryByNameAndLevel(string $name, string $level): \Magento\Framework\DataObject
    {
        $collection = $this->_categoryCollectionFactory->create();
        $collection->addAttributeToFilter('name', $name);
        $collection->addAttributeToFilter('level', $level);
        return $collection->getFirstItem();
    }

    /**
     * @param $category
     * @param $idCategory
     * @param $nameCategory
     * @param $categoryKeyUrl
     * @param $parentCategory
     * @return void
     */
    private function _updateCategory($category, $idCategory, $nameCategory, $categoryKeyUrl, $parentCategory) {
        try {
            $category = $this->_categoryRepositoryInterface->get($category->getId());
            $data = [
                'parent_id' => $parentCategory->getId(),
                'path' => $parentCategory->getPath(),
                'name' => $nameCategory,
                'sap' => $idCategory,
                'custom_use_parent_settings' => 1,
                'custom_apply_to_products' => 0,
                'cate_landing_type' => 1,
                'display_mode' => 'PRODUCTS',
                'page_layout' => '2columns-left',
                'active_subcategories' => 0
            ];
            $checkData = $this->_checkCategory($category, $data);
            if (!$checkData) {
                $this->_saveDataCategory($category, $checkData);
                $this->resTable['updated']++;
            } else {
                $this->resTable['check']++;
            }
        } catch (\Magento\Framework\Exception\NoSuchEntityException $e) {
            $category = $this->_categoryFactory->create();
            $category
                ->setParentId($parentCategory->getId())
                ->setName($nameCategory)
                ->setIsActive(true)
                ->setData('sap', $idCategory ?? '')
                ->setData('active_subcategories', 0)
                ->setData('custom_use_parent_settings', 0)
                ->setData('custom_apply_to_products', 0)
                ->setData('cate_landing_type', 1)
                ->setData('display_mode', 'PRODUCTS')
                ->setData('page_layout', '2columns-left')
                ->setUrlKey($categoryKeyUrl);
            $this->_saveCategory($category, 1);
            $this->resTable['new']++;
        }
    }

    /**
     * @param $item
     * @param $currentData
     * @return array|false
     */
    private function _checkCategory($item, $currentData) {
        $headData = [
            'parent_id' => $item->getParentId(),
            'path' => $item->getPath(),
            'name' => $item->getName(),
            'sap' => $item->getData('sap') ?? '',
            // category int
            'custom_use_parent_settings' => $item->getData('custom_use_parent_settings') -1,//0
            'custom_apply_to_products' => $item->getData('custom_apply_to_products') ?? -1,//0
            //carchar
            'cate_landing_type' => $item->getData('cate_landing_type') ?? -1,//1
            'display_mode' => $item->getData('display_mode') ?? '', //PRODUCTS
            'page_layout' => $item->getData('page_layout') ?? ''//2columns-left

        ];

        $checkData = array_diff_assoc($currentData, $headData);

        return empty($checkData) ? false : $checkData;
    }

    /**
     * @param $item
     * @param $data
     * @return void
     */
    private function _saveDataCategory($item, $data)
    {
        foreach ($data as $key => $value) {
            $item->setData($key, $value);
            try {
                $item->getResource()->saveAttribute($item, $key);
            } catch (\Exception $e) {
                $this->logger->debug($e->getMessage());
                echo $e->getMessage() . "\n";
            }
        }
    }

    /**
     * @param $category
     * @param $withRepository
     * @return void
     */
    private function _saveCategory($category, $withRepository = 0)
    {
        try {
            if ($withRepository == 1) {
                $this->_categoryRepositoryInterface->save($category);
            } else {
                $category->save();
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
