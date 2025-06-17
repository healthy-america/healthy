<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */

declare(strict_types=1);

namespace Aventi\PriceLists\Helper;

use Aventi\PriceLists\Api\Data\PriceListCustomersInterfaceFactory;
use Aventi\PriceLists\Api\Data\PriceListInterface;
use Aventi\PriceLists\Api\PriceListCustomersRepositoryInterface;
use Aventi\PriceLists\Api\PriceListRepositoryInterface;
use Aventi\PriceLists\Model\PriceListData;
use Exception;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

/**
 * Class PriceListAdmin
 */
class PriceListAdmin extends AbstractHelper
{
    /**
     * Constructor
     *
     * @param Context $context
     * @param PriceListData $priceListData
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param PriceListCustomersRepositoryInterface $priceListCustomersRepository
     * @param PriceListRepositoryInterface $priceListRepository
     * @param PriceListCustomersInterfaceFactory $priceListCustomersFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        private PriceListData $priceListData,
        private SearchCriteriaBuilder $searchCriteriaBuilder,
        private PriceListCustomersRepositoryInterface $priceListCustomersRepository,
        private PriceListRepositoryInterface $priceListRepository,
        private PriceListCustomersInterfaceFactory $priceListCustomersFactory,
        private LoggerInterface $logger
    ) {
        parent::__construct($context);
    }

    /**
     *  Get price list by customer
     *
     * @param array $data
     * @return float|null
     */
    public function getPriceListByCustomer(array $data)
    {
        $result = $data['originalPrice'] ?? null;
        $product = $data['product'] ?? null;
        $customer = $data['customer'] ?? null;
        if ($product && $result && $customer) {
            $priceList = $this->priceListData->getProductPrice($product, $result, $customer);
            if ($priceList) {
                $result = $priceList;
            }
        }

        return $result;
    }

    /**
     * Assign customer by parent
     *
     * @param CustomerInterface $parent
     * @param CustomerInterface $customer
     * @return void
     */
    public function asignarCustomerByParent(CustomerInterface $parent, CustomerInterface $customer): void
    {
        try {
            if ($parent->getId() !== $customer->getId()) {
                $parentPriceList = $this->getPriceListByCustomerObject($parent);
                $customerPriceList = $this->getPriceListByCustomerObject($customer);
                if (!$parentPriceList ||
                    ($customerPriceList && $customerPriceList->getEntityId() === $parentPriceList->getEntityId())
                ) {
                    return;
                }
                $this->addCustomerInPriceList($parentPriceList, $customer);
            }
        } catch (Exception $e) {
            $this->logger->debug('asignarCustomerByParent: ' . $e->getMessage());
        }
    }

    /**
     * Get customer price list
     *
     * @param CustomerInterface $customer
     * @return PriceListInterface|null
     * @throws LocalizedException
     */
    public function getPriceListByCustomerObject(CustomerInterface $customer): ?PriceListInterface
    {
        $priceList = null;
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('customer_id', $customer->getId())
            ->create();
        $list = $this->priceListCustomersRepository->getList($searchCriteria);
        $items = $list->getItems();
        $item = reset($items);
        if ($item) {
            $priceList = $this->priceListRepository->get($item->getPriceListId());
        }

        return $priceList;
    }

    /**
     * Add customer in price list
     *
     * @param PriceListInterface $priceList
     * @param CustomerInterface $customer
     * @return void
     * @throws LocalizedException
     */
    public function addCustomerInPriceList(PriceListInterface $priceList, CustomerInterface $customer)
    {
        $this->removeCustomerInOtherList($priceList->getEntityId(), $customer->getId());
        $priceListCustomer = $this->priceListCustomersFactory->create();
        $priceListCustomer->setPriceListId($priceList->getEntityId());
        $priceListCustomer->setCustomerId($customer->getId());
        $this->priceListCustomersRepository->save($priceListCustomer);
    }

    /**
     * Remove customer in other list
     *
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
}
