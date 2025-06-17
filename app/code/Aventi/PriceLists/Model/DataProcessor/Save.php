<?php
/**
 * Aventi Solutions
 * Julian D Amaya <jamaya@aventi.co>
 * 2022
 *
 */

namespace Aventi\PriceLists\Model\DataProcessor;

use Aventi\PriceLists\Api\Data\PriceListCategoryInterface;
use Aventi\PriceLists\Api\Data\PriceListCategoryInterfaceFactory;
use Aventi\PriceLists\Api\Data\PriceListCustomersInterface;
use Aventi\PriceLists\Api\Data\PriceListCustomersInterfaceFactory;
use Aventi\PriceLists\Api\Data\PriceListGroupsInterface;
use Aventi\PriceLists\Api\Data\PriceListGroupsInterfaceFactory;
use Aventi\PriceLists\Api\Data\PriceListProductsInterface;
use Aventi\PriceLists\Api\Data\PriceListProductsInterfaceFactory;

use Aventi\PriceLists\Api\PriceListCategoryRepositoryInterface;
use Aventi\PriceLists\Api\PriceListCustomersRepositoryInterface;
use Aventi\PriceLists\Api\PriceListGroupsRepositoryInterface;
use Aventi\PriceLists\Api\PriceListProductsRepositoryInterface;

use Aventi\PriceLists\Model\ResourceModel\PriceListCategory;
use Aventi\PriceLists\Model\ResourceModel\PriceListProducts\Collection;

class Save
{
    /**
     * @var PriceListCustomersRepositoryInterface
     */
    protected $priceListCustomersRepository;
    /**
     * @var PriceListProductsRepositoryInterface
     */
    protected $priceListProductsRepository;
    /**
     * @var PriceListProductsInterfaceFactory
     */
    protected $priceListProducts;
    /**
     * @var PriceListCustomersInterfaceFactory
     */
    protected $priceListCustomers;
    /**
     * @var \Aventi\PriceLists\Model\ResourceModel\PriceListCustomers\CollectionFactory
     */
    protected $priceListCustomersCollection;
    /**
     * @var \Aventi\PriceLists\Model\ResourceModel\PriceListProducts\CollectionFactory
     */
    protected $priceListProductsCollection;
    private PriceListCategoryInterfaceFactory $priceListCategory;
    private PriceListCategoryRepositoryInterface $priceListCategoryRepository;
    private \Aventi\PriceLists\Model\ResourceModel\PriceListGroups\CollectionFactory $priceListGroupsCollection;
    private PriceListCategory\CollectionFactory $priceListCategoryCollection;
    private PriceListGroupsRepositoryInterface $priceListGroupsRepository;

    /**
     * @param PriceListCustomersRepositoryInterface $priceListCustomersRepository
     * @param PriceListGroupsRepositoryInterface $priceListGroupsRepository
     * @param \Aventi\PriceLists\Model\ResourceModel\PriceListCustomers\CollectionFactory $priceListCustomersCollection
     * @param \Aventi\PriceLists\Model\ResourceModel\PriceListGroups\CollectionFactory $priceListGroupsCollection
     * @param \Aventi\PriceLists\Model\ResourceModel\PriceListProducts\CollectionFactory $priceListProductsCollection
     * @param \Aventi\PriceLists\Model\ResourceModel\PriceListCategory\CollectionFactory $priceListCategoryCollection
     * @param PriceListProductsRepositoryInterface $priceListProductsRepository
     * @param PriceListCategoryRepositoryInterface $priceListCategoryRepository
     * @param PriceListProductsInterfaceFactory $priceListProducts
     * @param PriceListCategoryInterfaceFactory $priceListCategory
     * @param PriceListCustomersInterfaceFactory $priceListCustomers
     * @param PriceListGroupsInterfaceFactory $priceListGroups
     */
    public function __construct(
        PriceListCustomersRepositoryInterface                                       $priceListCustomersRepository,
        PriceListGroupsRepositoryInterface                                          $priceListGroupsRepository,
        \Aventi\PriceLists\Model\ResourceModel\PriceListCustomers\CollectionFactory $priceListCustomersCollection,
        \Aventi\PriceLists\Model\ResourceModel\PriceListGroups\CollectionFactory    $priceListGroupsCollection,
        \Aventi\PriceLists\Model\ResourceModel\PriceListProducts\CollectionFactory  $priceListProductsCollection,
        \Aventi\PriceLists\Model\ResourceModel\PriceListCategory\CollectionFactory  $priceListCategoryCollection,
        PriceListProductsRepositoryInterface                                        $priceListProductsRepository,
        PriceListCategoryRepositoryInterface                                        $priceListCategoryRepository,
        PriceListProductsInterfaceFactory                                           $priceListProducts,
        PriceListCategoryInterfaceFactory                                           $priceListCategory,
        PriceListCustomersInterfaceFactory                                          $priceListCustomers,
        PriceListGroupsInterfaceFactory                                             $priceListGroups
    ) {
        $this->priceListCustomersRepository = $priceListCustomersRepository;
        $this->priceListGroupsRepository = $priceListGroupsRepository;
        $this->priceListProductsRepository = $priceListProductsRepository;
        $this->priceListCategoryRepository = $priceListCategoryRepository;
        $this->priceListCategory = $priceListCategory;
        $this->priceListProducts = $priceListProducts;
        $this->priceListCustomers = $priceListCustomers;
        $this->priceListGroups = $priceListGroups;
        $this->priceListCustomersCollection = $priceListCustomersCollection;
        $this->priceListGroupsCollection = $priceListGroupsCollection;
        $this->priceListProductsCollection = $priceListProductsCollection;
        $this->priceListCategoryCollection = $priceListCategoryCollection;
    }

    /***
     * Update the products table for the newly created price list
     *
     * @param $products
     * @param $id
     */
    public function updateProducts($products, $id)
    {
        /** new product ids */
        $selectedIds = [];
        $priceMap = [];
        foreach ($products as $product) {
            if (is_array($product['product_id'])) {
                foreach ($product['product_id'] as $ipd) {
                    $selectedIds[] = (int)$ipd;
                    $priceMap[$ipd] = [
                        'value'=> $product['product_price'],
                        'value_sug'=> $product['product_price_sug'],
                        'type' => $product['rule_type']
                    ];
                }
            } else {
                $selectedIds[] = $product['product_id'];
                $priceMap[ $product['product_id']] = [
                    'value'=> $product['product_price'],
                    'value_sug'=> $product['product_price_sug'],
                    'type' => $product['rule_type']
                ];
            }
        }

        /** @var Collection $collection */
        $collection = $this->priceListProductsCollection->create();
        /** Get the collection of non existing ids in the new save */
        $collection->addFieldToFilter('product_id', ['nin' => $selectedIds]);
        $collection->addFieldToFilter('price_list_id', $id);
        /** @var PriceListProductsInterface $oldItem */
        foreach ($collection as $oldItem) {
            try {
                /** delete the old item via the repository interface */
                $this->priceListProductsRepository->delete($oldItem->getDataModel());
            } catch (\Exception $E) {
            }
        }

        /** @var int $sid */
        foreach ($selectedIds as $sid) {
            /** @var PriceListProductsInterface $item */
            $item = $this->priceListProducts->create();
            try {
                /** @var Collection $collection */
                $collection = $this->priceListProductsCollection->create();
                $collection->addFieldToFilter('product_id', $sid);
                $collection->addFieldToFilter('price_list_id', $id);
                $original = $collection->getFirstItem();
                if ($original->getEntityId()) {
                    /** Keep the original database id if it alredy exists as there should only be one product per list */
                    /** @var  PriceListProductsInterface $item */
                    $item = $original->getDataModel();
                }
            } catch (\Exception $e) {
            }

            $item->setProductId($sid);
            $item->setPriceListId($id);
            if (isset($priceMap[$sid])) {
                $item->setProductPrice($priceMap[$sid]['value']);
                $item->setProductPriceSug($priceMap[$sid]['value_sug']);
                $item->setProductRuleType($priceMap[$sid]['type']);
            }
            try {
                $this->priceListProductsRepository->save($item);
            } catch (\Exception $E) {
            }
        }
    }

    /**
     * Update the customers table for the newly created price list
     *
     * @param $customers
     * @param $id
     */
    public function updateCustomers($customers, $id)
    {
        /** new product ids */
        $selectedIds = [];
        foreach ($customers as $customer) {
            if (is_array($customer)) {
                foreach ($customer as $cid) {
                    $selectedIds[] = (int)$cid;
                }
            } else {
                $selectedIds[] = $customer;
            }
        }

        /** @var Collection $collection */
        $collection = $this->priceListCustomersCollection->create();
        /** Get the collection of non existing ids in the new save */
        $collection->addFieldToFilter('customer_id', ['nin' => $selectedIds]);
        $collection->addFieldToFilter('price_list_id', $id);

        /** @var PriceListCustomersInterface $oldItem */
        foreach ($collection as $oldItem) {
            try {
                /** delete the old item via the repository interface */
                $this->priceListCustomersRepository->delete($oldItem->getDataModel());
            } catch (\Exception $E) {
            }
        }

        /** @var int $sid */
        foreach ($selectedIds as $sid) {
            /** @var PriceListCustomersInterface $item */
            $item = $this->priceListCustomers->create();
            try {
                /** @var Collection $collection */
                $collection = $this->priceListCustomersCollection->create();
                $collection->addFieldToFilter('customer_id', $sid);
                $collection->addFieldToFilter('price_list_id', $id);
                $original = $collection->getFirstItem();
                if ($original->getEntityId()) {
                    /** Keep the original database id if it alredy exists as there should only be one product per list */
                    /** @var  PriceListProductsInterface $item */
                    $item = $original->getDataModel();
                }
            } catch (\Exception $e) {
            }

            $item->setCustomerId($sid);
            $item->setPriceListId($id);

            try {
                $this->priceListCustomersRepository->save($item);
            } catch (\Exception $E) {
            }
        }
    }

    /**
     * Update the groups table for the newly created price list
     *
     * @param $groups
     * @param $id
     */
    public function updateGroups($groups, $id)
    {
        /** new product ids */
        $selectedIds = [];
        foreach ($groups as $group) {
            if (is_array($group)) {
                foreach ($group as $cid) {
                    $selectedIds[] = (int)$cid;
                }
            } else {
                $selectedIds[] = $group;
            }
        }

        /** @var Collection $collection */
        $collection = $this->priceListGroupsCollection->create();
        /** Get the collection of non existing ids in the new save */
        $collection->addFieldToFilter('customer_group_id', ['nin' => $selectedIds]);
        $collection->addFieldToFilter('price_list_id', $id);

        /** @var PriceListGroupsInterface $oldItem */
        foreach ($collection as $oldItem) {
            try {
                /** delete the old item via the repository interface */
                $this->priceListGroupsRepository->delete($oldItem->getDataModel());
            } catch (\Exception $E) {
            }
        }

        /** @var int $sid */
        foreach ($selectedIds as $sid) {
            /** @var PriceListGroupsInterface $item */
            $item = $this->priceListGroups->create();
            try {
                /** @var Collection $collection */
                $collection = $this->priceListGroupsCollection->create();
                $collection->addFieldToFilter('customer_group_id', $sid);
                $collection->addFieldToFilter('price_list_id', $id);
                $original = $collection->getFirstItem();

                if ($original->getEntityId()) {
                    /** Keep the original database id if it alredy exists as there should only be one product per list */
                    /** @var  PriceListProductsInterface $item */
                    $item = $original->getDataModel();
                }
            } catch (\Exception $e) {
            }

            $item->setCustomerGroupId($sid);
            $item->setPriceListId($id);

            try {
                $this->priceListGroupsRepository->save($item);
            } catch (\Exception $E) {
            }
        }
    }

    /***
     * Update the category products table for the newly created price list
     *
     * @param $categoryProducts
     * @param $id
     */
    public function updateCategory($categoryProducts, $id)
    {

        /** new product ids */
        $selectedIds = [];
        $priceMap = [];
        foreach ($categoryProducts as $category) {
            if (is_array($category['category_id'])) {
                foreach ($category['category_id'] as $ipd) {
                    $selectedIds[] = (int)$ipd;
                    $priceMap[$ipd] = [
                        'value'=> $category['category_price'],
                        'type' => $category['category_rule_type']
                    ];
                }
            } else {
                $selectedIds[] = $category['category_id'];
                $priceMap[$category['category_id']] = [
                    'value'=> $category['category_price'],
                    'type' => $category['category_rule_type']
                ];
            }
        }

        /** @var Collection $collection */
        $collection = $this->priceListCategoryCollection->create();
        /** Get the collection of non existing ids in the new save */
        $collection->addFieldToFilter('category_id', ['nin' => $selectedIds]);
        $collection->addFieldToFilter('price_list_id', $id);

        /** @var PriceListCategoryInterface $oldItem */
        foreach ($collection as $oldItem) {
            try {
                /** delete the old item via the repository interface */
                $this->priceListCategoryRepository->deleteById($oldItem->getEntityId());
            } catch (\Exception $E) {
                echo "<hr><h1>ERRORR</h1><br>";
                echo $E->getMessage();
            }
        }

        /** @var int $sid */
        foreach ($selectedIds as $sid) {
            /** @var PriceListCategoryInterface $item */
            $item = $this->priceListCategory->create();
            try {
                /** @var Collection $collection */
                $collection = $this->priceListCategoryCollection->create();
                $collection->addFieldToFilter('category_id', $sid);
                $collection->addFieldToFilter('price_list_id', $id);
                $original = $collection->getFirstItem();
                if ($original->getEntityId()) {
                    /** Keep the original database id if it alredy exists as there should only be one product per list */
                    /** @var  PriceListCategoryInterface $item */
                    $item = $original->getDataModel();
                }
            } catch (\Exception $e) {
            }

            $item->setCategoryId($sid);
            $item->setPriceListId($id);
            if (isset($priceMap[$sid])) {
                $item->setCategoryPrice($priceMap[$sid]['value']);
                $item->setCategoryRuleType($priceMap[$sid]['type']);
            }
            try {
                $this->priceListCategoryRepository->save($item);
            } catch (\Exception $E) {
            }
        }
    }
}
