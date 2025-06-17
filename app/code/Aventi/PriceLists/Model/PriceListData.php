<?php

namespace Aventi\PriceLists\Model;

use Magento\Catalog\Model\Product;
use Magento\Customer\Model\SessionFactory as Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Store\Model\ScopeInterface;
use Aventi\PriceLists\Api\Data\PriceListProductsInterface;
use Aventi\PriceLists\Model\ResourceModel\PriceListCustomers\CollectionFactory as PriceCustomersCollection;
use Aventi\PriceLists\Model\ResourceModel\PriceListGroups\CollectionFactory as PriceGroupsCollection;
use Aventi\PriceLists\Model\ResourceModel\PriceListProducts\CollectionFactory as PriceProductsCollection;
use Aventi\PriceLists\Model\ResourceModel\PriceListCategory\CollectionFactory as PriceCategoryCollection;
use function PHPUnit\Framework\isNull;

/**
 * Class PriceListData
 * @package Aventi\PriceLists\Model
 */
class PriceListData extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var Session
     */
    protected $session;
    /**
     * @var PriceCustomersCollection
     */
    protected $priceListCustomersCollection;
    /**
     * @var PriceGroupsCollection
     */
    protected $priceListGroupsCollection;
    /**
     * @var PriceProductsCollection
     */
    protected $priceListProductsCollection;
    /**
     * @var PriceCategoryCollection
     */
    protected $priceListCategoryCollection;

    const XML_PATH_PRICELISTS = 'price_lists/';

    /**
     * @var ScopeConfigInterface|ScopeInterface
     */
    private $scopeConfig;

    /**
     * PriceListData constructor.
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param Registry $registry
     * @param Session $customerSession
     * @param PriceCustomersCollection $priceListCustomersCollection
     * @param PriceGroupsCollection $priceListGroupsCollection
     * @param PriceProductsCollection $priceListProductsCollection
     * @param PriceCategoryCollection $priceListCategoryCollection
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        Registry $registry,
        Session $customerSession,
        PriceCustomersCollection $priceListCustomersCollection,
        PriceGroupsCollection $priceListGroupsCollection,
        PriceProductsCollection $priceListProductsCollection,
        PriceCategoryCollection $priceListCategoryCollection
    ) {
        $this->session = $customerSession;
        $this->priceListCustomersCollection = $priceListCustomersCollection;
        $this->priceListGroupsCollection = $priceListGroupsCollection;
        $this->priceListProductsCollection = $priceListProductsCollection;
        $this->priceListCategoryCollection = $priceListCategoryCollection;
        $this->scopeConfig = $scopeConfig;

        parent::__construct($context, $registry, null, null);
    }

    /**
     * @return array|null
     */
    public function getCustomerProductIds(): ?array
    {
        /**
         * @var $productIds array
         */
        $productIds = [];
        $listIds = $this->getCustomerLists();
        $products = $this->priceListProductsCollection->create()
            ->addFieldToFilter('price_list_id', ['in' => $listIds]);
        /**
         * @var PriceListProductsInterface $product
         */
        foreach ($products as $product) {
            $productIds[] = $product->getEntityId();
        }
        /** Make sure the array only holds the product id once */
        return array_unique($productIds);
    }

    /**
     * return if logged in
     * @return bool
     */
    public function isLoggedInId(): bool
    {
        return is_null($this->session->create()->getCustomer()->getEntityId()) ? false : true;
    }

    /**
     * @param null $idCustumer
     * @return array|null
     */
    public function getCustomerLists($idCustumer = null): ?array
    {
        $cid = $idCustumer ?? $this->session->create()->getCustomer()->getId();
        $customerOnLists = $this->priceListCustomersCollection->create()
            ->addFieldToFilter('customer_id', $cid);

        $listIds = [];
        foreach ($customerOnLists as $list) {
            $listIds[] = $list->getPriceListId();
        }
        return $listIds;
    }

    /**
     * @return array|null
     */
    public function getGroupsLists(): ?array
    {
        $gid = $this->session->create()->getCustomer()->getGroupId();
        $cid = is_null($this->session->create()->getCustomer()->getEntityId()) ? 0 : $gid;
        $groupOnLists = $this->priceListGroupsCollection->create()
            ->addFieldToFilter('customer_group_id', $cid);

        $listIds = [];
        foreach ($groupOnLists as $list) {
            $listIds[] = $list->getPriceListId();
        }
        return $listIds;
    }

    /**
     * @param Product $product
     * @param $originalPrice
     * @param null $idCustumer
     * @return float|null
     */
    public function getProductPrice(Product $product, $originalPrice , $idCustumer = null): ?float
    {
        $productId = $product->getId();
        $listIds = $this->getCustomerLists($idCustumer);
        if (!count($listIds)) {
            $listIds = $this->getGroupsLists();
        }

        $prices = $this->priceListProductsCollection->create()
            ->addFieldToFilter('product_id', $productId)
            ->addFieldToFilter('price_list_id', ['in' => $listIds]);

        $listPrice = 0;
        $cantReg = $prices->getSize();

        ## if there cant products discount will search by category
        if ($cantReg == 0) {
            $categories = $product->getCategoryIds();

            $prices = $this->priceListCategoryCollection->create()
                ->addFieldToFilter('category_id', ['in' => $categories])
                ->addFieldToFilter('price_list_id', ['in' => $listIds]);

            foreach ($prices as $price) {
                $newPrice = $price->getCategoryPrice();

                if ($price->getCategoryRuleType() === 'discount') {
                    $newPrice = ($newPrice == 100 || $originalPrice == 0) ? 0 :
                        $originalPrice * (100 - $newPrice) / 100;
                }

                ## Only update the price if the new price is less
                ## this to form of using the cheapest for final price.
                if ($newPrice > $listPrice) {
                    $listPrice = $newPrice;
                }
            }
        } else {
            foreach ($prices as $price) {
                $newPrice = $price->getProductPrice();

                if ($price->getProductRuleType() === 'discount') {
                    $newPrice = ($newPrice == 100 || $originalPrice == 0) ? 0 :
                        $originalPrice * (100 - $newPrice) / 100;
                }

                ## Only update the price if the new price is less
                ## this to form of using the cheapest for final price.
                if ($newPrice > $listPrice) {
                    $listPrice = $newPrice;
                }
            }
        }
        //NO STRICT TYPE
        return ($listPrice != 0 ? $listPrice : $originalPrice);
    }

    /**
     * @param $field
     * @param null $storeId
     * @return mixed
     */
    public function getConfigValue($field, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param $code the system config code to loads
     * @param null $storeId the store id if required
     * @return mixed return the config data
     */
    public function getGeneralConfig($code, $storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_PRICELISTS . 'general/' . $code, $storeId);
    }
}
