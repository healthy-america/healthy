<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\PriceLists\Plugin\Magento\ConfigurableProduct\Pricing\Price;

use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\Data\ProductInterfaceFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\CatalogInventory\Api\StockStateInterface;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\Api\DataObjectHelper;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Json\EncoderInterface;
use Magento\Framework\Module\Manager;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;
use Aventi\PriceLists\Model\PriceListData;

class ConfigurablePriceResolver
{
    /**
     * @var Manager
     */
    private Manager $moduleManager;

    /**
     * @var EncoderInterface
     */
    private EncoderInterface $jsonEncoder;

    /**
     * @var Registry
     */
    private Registry $registry;

    /**
     * @var ProductInterfaceFactory
     */
    private ProductInterfaceFactory $productFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private ProductRepositoryInterface $productRepository;

    /**
     * @var Configurable
     */
    private Configurable $configurableType;

    /**
     * @var DataObjectHelper
     */
    private DataObjectHelper $dataObjectHelper;

    /**
     * @var StockStateInterface
     */
    private StockStateInterface $stockState;

    /**
     * @var StoreManagerInterface
     */
    private StoreManagerInterface $storeManager;

    /**
     * @var PriceListData
     */
    private PriceListData $priceListData;

    /**
     * @param Manager $moduleManager
     * @param EncoderInterface $jsonEncoder
     * @param Registry $registry
     * @param ProductRepositoryInterface $productRepository
     * @param ProductInterfaceFactory $productFactory
     * @param Configurable $configurableType
     * @param DataObjectHelper $dataObjectHelper
     * @param StockStateInterface $stockState
     * @param StoreManagerInterface $storeManager
     * @param PriceListData $priceListData
     */
    public function __construct(
        Manager $moduleManager,
        EncoderInterface $jsonEncoder,
        Registry $registry,
        ProductRepositoryInterface $productRepository,
        ProductInterfaceFactory $productFactory,
        Configurable $configurableType,
        DataObjectHelper $dataObjectHelper,
        StockStateInterface $stockState,
        StoreManagerInterface $storeManager,
        PriceListData $priceListData,
    ) {
        $this->moduleManager = $moduleManager;
        $this->jsonEncoder = $jsonEncoder;
        $this->registry = $registry;
        $this->productFactory = $productFactory;
        $this->productRepository = $productRepository;
        $this->configurableType = $configurableType;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->stockState = $stockState;
        $this->storeManager = $storeManager;
        $this->priceListData = $priceListData;
    }

    /**
     * @param \Magento\Framework\Pricing\SaleableInterface|\Magento\Catalog\Model\Product $product
     * @return float
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function aroundResolvePrice(
        $subject,
        \Closure $proceed,
        \Magento\Framework\Pricing\SaleableInterface $product
    ) {
        if ($this->priceListData->getGeneralConfig('enable')) {
            $type = $this->priceListData->getGeneralConfig('type_price');
            switch ($type) {
                case 'min_greater_zero':
                    $price = $this->getMinGreaterZero($product);
                    break;
                case 'max':
                    $price = $this->getMax($product);
                    break;
                default:
                    $price = $proceed($product);
            }
            return $price;
        }
        return $proceed($product);
    }

    /**
     * @param $product
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getMinGreaterZero($product)
    {
        $price = null;
        $parentId = $product['entity_id'];
        $childObj = $this->getChildProductObj($parentId);
        foreach ($childObj as $child) {
            $productPrice = $child->getPrice();
            if ($productPrice > 0 && ($price === null || $productPrice < $price)) {
                $price = $productPrice;
            }
        }
        return $price;
    }

    /**
     * @param $product
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getMax($product)
    {
        $price = null;
        $parentId = $product['entity_id'];
        $childObj = $this->getChildProductObj($parentId);
        foreach ($childObj as $child) {
            $productPrice = $child->getPrice();
            $price = $price ? max($price, $productPrice) : $productPrice;
        }
        return $price;
    }

    /**
     * @param $id
     * @return ProductInterface|null
     * @throws NoSuchEntityException
     */
    public function getProductInfo($id)
    {
        $product = null;
        if (is_numeric($id)) {
            $product = $this->productRepository->getById($id);
        }
        return $product;
    }

    /**
     * @param $id
     * @return array|void
     * @throws NoSuchEntityException
     */
    public function getChildProductObj($id)
    {
        $product = $this->getProductInfo($id);

        //if quote with not proper id then return null and exit;
        if (!isset($product)) {
            return;
        }

        if ($product->getTypeId() != Configurable::TYPE_CODE) {
            return [];
        }

        $storeId = $this->storeManager->getStore()->getId();
        $productTypeInstance = $product->getTypeInstance();
        $productTypeInstance->setStoreFilter($storeId, $product);
        $childrenList = [];

        foreach ($productTypeInstance->getUsedProducts($product) as $child) {
            $attributes = [];
            $isSaleable = $child->isSaleable();

            //get only in stock product info
            if ($isSaleable) {
                foreach ($child->getAttributes() as $attribute) {
                    $attrCode = $attribute->getAttributeCode();
                    $value = $child->getDataUsingMethod($attrCode) ?: $child->getData($attrCode);
                    if (null !== $value && $attrCode != 'entity_id') {
                        $attributes[$attrCode] = $value;
                    }
                }

                $attributes['store_id'] = $child->getStoreId();
                $attributes['id'] = $child->getId();
                /** @var ProductInterface $productDataObject */
                $productDataObject = $this->productFactory->create();
                $this->dataObjectHelper->populateWithArray(
                    $productDataObject,
                    $attributes,
                    '\Magento\Catalog\Api\Data\ProductInterface'
                );
                $childrenList[] = $productDataObject;
            }
        }

        $childConfigData = [];
        foreach ($childrenList as $child) {
            $childConfigData[] = $child;
        }

        return $childConfigData;
    }
}
