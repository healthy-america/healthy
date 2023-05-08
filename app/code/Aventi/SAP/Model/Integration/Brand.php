<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Model\Integration;

use Bcn\Component\Json\Reader;
use Magento\Framework\Exception\NoSuchEntityException;

class Brand extends \Aventi\SAP\Model\Integration
{
    const TYPE_URI = 'brand';

    const DEFAULT_STORE = 1;

    private array $resTable = [
        'check' => 0,
        'fail' => 0,
        'new' => 0,
        'updated' => 0
    ];

    /**
     * @var \Aventi\SAP\Helper\Data
     */
    private $data;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $_resourceConnection;

    /**
     * @var \MGS\Brand\Model\Brand
     */
    private $_modelBrand;

    /**
     * @var \Aventi\SAP\Model\Integration\Check\Product\CheckBrand
     */
    private $_checkBrand;

    /**
     * @var \Aventi\SAP\Helper\Password
     */
    private $_password;

    /**
     * @var \MGS\Brand\Model\BrandFactory
     */
    private $_brandFactory;

    /**
     * @var \Magento\Framework\Setup\ModuleDataSetupInterface
     */
    private $_setup;

    /**
     * @var \Magento\Catalog\Api\ProductAttributeRepositoryInterface
     */
    private $_productAttributeRepositoryInterface;

    /**
     * @var \Magento\Backend\Model\Session
     */
    private $_session;

    public function __construct(
        \Aventi\SAP\Helper\Attribute $attributeDate,
        \Aventi\SAP\Logger\Logger $logger,
        \Magento\Framework\Filesystem\DriverInterface $driver,
        \Magento\Framework\Filesystem $filesystem,
        \Aventi\SAP\Helper\Data $data,
        \Magento\Framework\App\ResourceConnection $resourceConnection,
        \MGS\Brand\Model\Brand $modelBrand,
        \MGS\Brand\Model\BrandFactory $brandFactory,
        \Aventi\SAP\Model\Integration\Check\Product\CheckBrand $checkBrand,
        \Aventi\SAP\Helper\Password $password,
        \Magento\Framework\Setup\ModuleDataSetupInterface $setup,
        \Magento\Catalog\Api\ProductAttributeRepositoryInterface $productAttributeRepositoryInterface,
        \Magento\Backend\Model\Session $session
    ) {
        parent::__construct($attributeDate, $logger, $driver, $filesystem);

        $this->data = $data;
        $this->_resourceConnection = $resourceConnection;
        $this->_modelBrand = $modelBrand;
        $this->_brandFactory = $brandFactory;
        $this->_checkBrand = $checkBrand;
        $this->_password = $password;
        $this->_setup = $setup;
        $this->_productAttributeRepositoryInterface = $productAttributeRepositoryInterface;
        $this->_session = $session;
    }

    public function test(array $data = null): void
    {
        $start = 0;
        $rows = 1000;
        $flag = true;

        while ($flag) {
            $brands = \Aventi\SAP\Model\Integration\Generator\Brand::getBrands();

            $total = count($brands);

            $progressBar = $this->startProgressBar($total);

            foreach ($brands as $brand) {
                $objBrand = (object) [
                    'name' => $this->prepareBrandName($brand['FirmName']),
                    'firm_code' => $brand['FirmCode']
                ];
                $this->managerBrand($objBrand);
                $this->advanceProgressBar($progressBar);
                // Debug only
                $total--;
            }
            $start += $rows;
            $this->finishProgressBar($progressBar, $start, $rows);
            $progressBar = null;
            if ($total <= 0) {
                $flag = false;
            }
        }
        $this->printTable($this->resTable);
    }

    public function process(array $data = null): void
    {
        $start = 0;
        $rows = 1000;

        $jsonData = $this->data->getResource(self::TYPE_URI, 0, 0, false);
        $jsonPath = $this->getJsonPath($jsonData, self::TYPE_URI);
        if ($jsonPath) {
            $reader = $this->getJsonReader($jsonPath);
            $reader->enter(null, Reader::TYPE_OBJECT);
            $total = (int)$reader->read("total");
            $brands = $reader->read("data");

            $progressBar = $this->startProgressBar($total);

            foreach ($brands as $brand) {
                $objBrand = (object) [
                    'name' => $this->prepareBrandName($brand['NOMBRE']),
                    'firm_code' => $brand['U_LINEA']
                ];

                $this->managerBrand($objBrand);

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
     * @param $brand
     * @throws NoSuchEntityException
     */
    private function managerBrand($brand): void
    {
        if (empty($brand->firm_code)) {
            $this->resTable['empty']++;
        }

        $brandId = $this->getBrandIdByFirmCode($brand->firm_code);

        if ($brandId !== -1) {
            $this->_modelBrand->load($brandId);
            $checkData = $this->_checkBrand->checkData($brand, $this->_modelBrand);
            if ($checkData) {
                //save
                $this->saveDataUpdate($this->_modelBrand, $checkData);
                $this->resTable['updated']++;
            } else {
                $this->resTable['check']++;
            }
        } else {
            //create
            $this->createBrand($brand);
            $this->resTable['new']++;
        }
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
        $brand = $connection->fetchOne($selectQry);
        return $brand ? (int)$brand : -1;
    }

    private function saveDataUpdate($model, $checkData)
    {
        foreach ($checkData as $key => $val) {
            $model->setData($key, $val);
        }
        try {
            $optionId = $this->saveOption('mgs_brand', $model->getName(), (int)$model->getOptionId());
            $model->setOptionId($optionId);
            $model->save();
        } catch (\Exception $e) {
        }
    }

    /**
     * @param $brand
     * @return void
     * @throws NoSuchEntityException
     */
    private function createBrand($brand)
    {
        $modelBrand = $this->_brandFactory->create();
        $_data = $this->getDataCreate($brand);
        $modelBrand->setData($_data['brand']);
        $modelBrand->setStores($_data['stores']);
        $this->_session->setPageData($modelBrand->getData());
        $modelBrand->save();
        $brand = $this->_modelBrand->load($modelBrand->getId());
        $optionId = $this->saveOption('mgs_brand', $brand->getName(), (int)$brand->getOptionId());
        $brand->setOptionId($optionId);
        $brand->save();
    }

    /**
     * @param $brand
     * @return array
     */
    private function getDataCreate($brand)
    {
        return [
            'brand' => [
                'name' => $brand->name,
                'firm_code' => $brand->firm_code,
                'url_key' => $this->_password->generateUrl($brand->name, 4),
                'status' => 1,
                'is_featured' => 1,
                'sort_order' => 0,
            ],
            'stores' => [
                self::DEFAULT_STORE
            ]
        ];
    }

    /**
     * @param $attributeCode
     * @param $label
     * @param $value
     * @return int|string
     * @throws NoSuchEntityException
     */
    protected function saveOption($attributeCode, $label, $value)
    {
        $attribute = $this->_productAttributeRepositoryInterface->get($attributeCode);
        $options = $attribute->getOptions();
        $values = [];
        foreach ($options as $option) {
            if ($option->getValue() != '') {
                $values[] = (int)$option->getValue();
            }
        }
        if (!in_array($value, $values)) {
            return $this->addAttributeOption(
                [
                    'attribute_id' => $attribute->getAttributeId(),
                    'order' => [0],
                    'value' => [
                        [
                            0 => $label,
                        ],
                    ],
                ]
            );
        } else {
            return $this->updateAttributeOption($value, $label);
        }
    }

    /**
     * @param $option
     * @return int|string
     */
    protected function addAttributeOption($option)
    {
        $oId = 0;
        $optionTable = $this->_setup->getTable('eav_attribute_option');
        $optionValueTable = $this->_setup->getTable('eav_attribute_option_value');
        if (isset($option['value'])) {
            foreach ($option['value'] as $optionId => $values) {
                $intOptionId = (int)$optionId;
                if (!$intOptionId) {
                    $_data = [
                        'attribute_id' => $option['attribute_id'],
                        'sort_order' => isset($option['order'][$optionId]) ? $option['order'][$optionId] : 0,
                    ];
                    $this->_setup->getConnection()->insert($optionTable, $_data);
                    $intOptionId = $this->_setup->getConnection()->lastInsertId($optionTable);
                    $oId = $intOptionId;
                }
                $condition = ['option_id =?' => $intOptionId];
                $this->_setup->getConnection()->delete($optionValueTable, $condition);
                foreach ($values as $storeId => $value) {
                    $_data = ['option_id' => $intOptionId, 'store_id' => $storeId, 'value' => $value];
                    $this->_setup->getConnection()->insert($optionValueTable, $_data);
                }
            }
        }
        return $oId;
    }

    /**
     * @param $optionId
     * @param $value
     * @return mixed
     */
    protected function updateAttributeOption($optionId, $value)
    {
        $oId = $optionId;
        $optionValueTable = $this->_setup->getTable('eav_attribute_option_value');
        $_data = ['value' => $value];
        $this->_setup->getConnection()->update($optionValueTable, $_data, ['option_id=?' => $optionId]);
        return $oId;
    }

    /**
     * @param $brand
     * @return mixed
     */
    private function prepareBrandName($brand)
    {
        //TODO: process name
        return $brand;
    }
}
