<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Model\Integration\Manager;

use Aventi\PriceLists\Api\Data\PriceListCustomersInterfaceFactory;
use Aventi\PriceLists\Api\Data\PriceListGroupsInterfaceFactory;
use Aventi\PriceLists\Api\Data\PriceListInterface;
use Aventi\PriceLists\Api\Data\PriceListInterfaceFactory;
use Aventi\PriceLists\Api\Data\PriceListProductsInterfaceFactory;
use Aventi\PriceLists\Api\PriceListCustomersRepositoryInterface;
use Aventi\PriceLists\Api\PriceListGroupsRepositoryInterface;
use Aventi\PriceLists\Api\PriceListProductsRepositoryInterface;
use Aventi\PriceLists\Api\PriceListRepositoryInterface;
use Exception;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Exception\LocalizedException;

class Price
{
    const LIST_NAMES = [
        'zekov',
        'esther',
        'group_henry'
    ];

    private $logger;

    /**
     * @param PriceListRepositoryInterface $priceListRepository
     * @param PriceListInterfaceFactory $priceListFactory
     * @param PriceListProductsRepositoryInterface $priceListProductsRepository
     * @param PriceListProductsInterfaceFactory $priceListProductsFactory
     * @param PriceListGroupsRepositoryInterface $priceListGroupsRepository
     * @param PriceListGroupsInterfaceFactory $priceListGroupsFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param GroupRepositoryInterface $groupRepository
     * @param PriceListCustomersRepositoryInterface $priceListCustomersRepository
     * @param PriceListCustomersInterfaceFactory $priceListCustomersFactory
     */
    public function __construct(//NOSONAR
        private PriceListRepositoryInterface $priceListRepository,
        private PriceListInterfaceFactory $priceListFactory,
        private PriceListProductsRepositoryInterface $priceListProductsRepository,
        private PriceListProductsInterfaceFactory $priceListProductsFactory,
        private PriceListGroupsRepositoryInterface $priceListGroupsRepository,
        private PriceListGroupsInterfaceFactory $priceListGroupsFactory,
        private SearchCriteriaBuilder $searchCriteriaBuilder,
        private GroupRepositoryInterface $groupRepository,
        private PriceListCustomersRepositoryInterface $priceListCustomersRepository,
        private PriceListCustomersInterfaceFactory $priceListCustomersFactory,
    ) {
    }

    /**
     * @param $list
     * @param $description
     * @return PriceListInterface|null
     */
    public function createListPrice($list, $description): ?PriceListInterface
    {
        try {
            $priceList = $this->getPriceListByName($list);
            if (!$priceList) {
                $objList = $this->priceListFactory->create();
                $objList->setName($list);
                $objList->setDescription($description);
                $priceList = $this->priceListRepository->save($objList);
            } elseif ($priceList->getDescription() !== $description
                && $priceList->getName() !== $description
            ) {
                $priceList->setName($list);
                $priceList = $this->priceListRepository->save($priceList);
            }
            return $priceList;
        } catch (Exception $e) {
            $this->logger->debug('SAP createListPrice method: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * @param $customerId
     * @param $priceList
     * @return string
     */
    public function addCustomerInPriceList($customerId, $priceList): string
    {
        $result = 'fail'; // error
        try {
            if (!$priceList) {
                return $result;
            }
            $list = $this->getPriceListByName($priceList);
            if (!$list) {
                $list = $this->createListPrice($priceList, $priceList);
            }
            $this->removeCustomerInOtherList($list->getEntityId(), $customerId);
            $priceListCustomer = $this->getPriceListCustomer($list->getEntityId(), $customerId);
            if (!$priceListCustomer) {
                $priceListCustomer = $this->priceListCustomersFactory->create();
                $priceListCustomer->setPriceListId($list->getEntityId());
                $priceListCustomer->setCustomerId($customerId);
                $this->priceListCustomersRepository->save($priceListCustomer);
                $result = 'new';
            } else {
                $result = 'check';
            }
        } catch (Exception $e) {
            $this->logger->debug('SAP addPriceList method: ' . $e->getMessage());
        }
        return $result;
    }

    /**
     * @param $listId
     * @param $customerId
     * @return void
     */
    public function removeCustomerInOtherList($listId, $customerId): void
    {
        try {
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('customer_id', $customerId)
                ->create();
            $list = $this->priceListCustomersRepository->getList($searchCriteria);
            if ($list->getTotalCount() > 0) {
                foreach ($list->getItems() as $item) {
                    if ($item->getPriceListId() === $listId) {
                        continue;
                    }
                    $this->priceListCustomersRepository->delete($item);
                }
            }
        } catch (Exception $e) {
            $this->logger->debug('SAP getPriceListCustomer method: ' . $e->getMessage());
        }
    }

    /**
     * @param $listId
     * @param $customerId
     * @return false|mixed|null
     */
    public function getPriceListCustomer($listId, $customerId): mixed
    {
        try {
            $firstItem = null;
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('price_list_id', $listId)
                ->addFilter('customer_id', $customerId)
                ->create();

            $list = $this->priceListCustomersRepository->getList($searchCriteria);

            if ($list->getTotalCount() > 0) {
                $items = $list->getItems();
                $firstItem = reset($items);
            }
            return $firstItem;
        } catch (Exception $e) {
            $this->logger->debug('SAP getPriceListCustomer method: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * @param $product
     * @param $priceObject
     * @return string
     */
    public function addPriceList($product, $priceObject): string
    {
        try {

            $list = $this->getPriceListByName($priceObject->list);

            if (!$list) {
                $list = $this->createListPrice($priceObject->list, $priceObject->description);
            }

            $priceListProduct = $this->getPriceListProduct($list->getEntityId(), $product->getId());

            $price = floatval($priceObject->price);
            $priceSug = floatval($priceObject->price_sug);

            if ($priceListProduct) {
                if ((float)$priceListProduct->getProductPrice() === $price
                    && (float)$priceListProduct->getProductpriceSug() === $priceSug) {
                    $result = 'check';
                } else {
                    $priceListProduct->setProductPrice($price);
                    $priceListProduct->setProductPriceSug($priceSug);
                    $this->priceListProductsRepository->save($priceListProduct);
                    $result = 'updated';
                }
            } else {
                $priceListProduct = $this->priceListProductsFactory->create();
                $priceListProduct->setPriceListId($list->getEntityId());
                $priceListProduct->setProductId($product->getId());
                $priceListProduct->setProductPrice($price);
                $priceListProduct->setProductPriceSug($priceSug);
                $priceListProduct->setProductRuleType('price');
                $this->priceListProductsRepository->save($priceListProduct);
                $result = 'new';
            }
            return $result;
        } catch (Exception $e) {
            $this->logger->debug('SAP addPriceList method: ' . $e->getMessage());
            return  'fail';
        }
    }

    /**
     * @param $name
     * @return PriceListInterface|null
     */
    public function getPriceListByName($name): ?PriceListInterface
    {
        try {
            return $this->priceListRepository->getByName($name);
        } catch (Exception $e) {
            $this->logger->debug('SAP getPriceListByName method: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * @param $listId
     * @param $productId
     * @return false|mixed|null
     */
    public function getPriceListProduct($listId, $productId): mixed
    {
        try {
            $firstItem = null;
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('price_list_id', $listId)
                ->addFilter('product_id', $productId)
                ->create();

            $list = $this->priceListProductsRepository->getList($searchCriteria);

            if ($list->getTotalCount() > 0) {
                $items = $list->getItems();
                $firstItem = reset($items);
            }
            return $firstItem;
        } catch (Exception $e) {
            $this->logger->debug('SAP getPriceListProduct method: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * @param $code
     * @return void
     * @throws LocalizedException
     */
    public function getCustomerGroupId($code)
    {
        $groupId = null;

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('customer_group_code', $code)
            ->create();

        $groups = $this->groupRepository->getList($searchCriteria);

        if ($groups->getTotalCount() > 0) {
            $groups = $groups->getItems();
            $firstItem = reset($groups);
            $groupId = $firstItem->getId();
        }

        return $groupId;
    }

    /**
     * @param mixed $logger
     */
    public function setLogger($logger): void
    {
        $this->logger = $logger;
    }
}
