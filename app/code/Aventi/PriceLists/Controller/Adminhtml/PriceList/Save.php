<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\PriceLists\Controller\Adminhtml\PriceList;

use Aventi\PriceLists\Api\Data\PriceListGroupsInterface;
use Aventi\PriceLists\Api\Data\PriceListGroupsInterfaceFactory;
use Aventi\PriceLists\Api\Data\PriceListInterface;
use Aventi\PriceLists\Api\PriceListGroupsRepositoryInterface;
use Aventi\PriceLists\Model\ResourceModel\PriceListGroups\CollectionFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Psr\Log\LoggerInterface;

class Save extends \Magento\Backend\App\Action
{
    protected $dataPersistor;

    /**
     * @var PriceListGroupsRepositoryInterface
     */
    private PriceListGroupsRepositoryInterface $priceListGroupsRepository;

    /**
     * @var CollectionFactory
     */
    private CollectionFactory $priceListGroupsCollectionFactory;

    /**
     * @var PriceListGroupsInterfaceFactory
     */
    private PriceListGroupsInterfaceFactory $priceListGroupsFactory;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param CollectionFactory $priceListGroupsCollectionFactory
     * @param PriceListGroupsRepositoryInterface $priceListGroupsRepository
     * @param PriceListGroupsInterfaceFactory $priceListGroupsFactory
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        DataPersistorInterface $dataPersistor,
        CollectionFactory $priceListGroupsCollectionFactory,
        PriceListGroupsRepositoryInterface $priceListGroupsRepository,
        PriceListGroupsInterfaceFactory $priceListGroupsFactory,
        LoggerInterface $logger
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->priceListGroupsCollectionFactory = $priceListGroupsCollectionFactory;
        $this->priceListGroupsRepository = $priceListGroupsRepository;
        $this->priceListGroupsFactory = $priceListGroupsFactory;
        $this->logger = $logger;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Redirect|\Magento\Framework\App\ResponseInterface|ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $dataPost = $this->getRequest()->getPostValue();
        $data = $dataPost['general'];

        // Customer groups
        $groups = $data['customer_groups'];

        // Data model
        $dataModel = $data;
        unset($dataModel['customer_groups']);

        if ($dataModel) {
            $id = $this->getRequest()->getParam(PriceListInterface::ENTITY_ID);
            $model = $this->_objectManager->create(\Aventi\PriceLists\Model\PriceList::class)->load($id);
            if (!$model->getId() && $id) {
                $this->messageManager->addErrorMessage(__('This Pricelist no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            }
            $model->setData($dataModel);
            try {
                $model->save();
                $this->saveCustomerGroups($model, $groups);
                $this->messageManager->addSuccessMessage(__('You saved the Pricelist.'));
                $this->dataPersistor->clear(\Aventi\PriceLists\Model\PriceList::TABLE);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', [PriceListInterface::ENTITY_ID => $model->getId()]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addExceptionMessage($e, __('Something went wrong while saving the Pricelist.'));
            }
            $this->dataPersistor->set(\Aventi\PriceLists\Model\PriceList::TABLE, $data);
            return $resultRedirect->setPath(
                '*/*/edit',
                [
                    PriceListInterface::ENTITY_ID => $this->getRequest()->getParam(PriceListInterface::ENTITY_ID)
                ]
            );
        }

        return $resultRedirect->setPath('*/*/');
    }

    /**
     * @param PriceListInterface $priceList
     * @param $groups
     * @return void
     */
    private function saveCustomerGroups(\Aventi\PriceLists\Api\Data\PriceListInterface $priceList, $groups): void
    {
        $groups = !$groups ? [] : $groups;
        $collection = $this->priceListGroupsCollectionFactory->create();
        /** Get the collection of non existing ids in the new save */
        $collection->addFieldToFilter(
            PriceListGroupsInterface::PRICE_LIST_CUSTOMER_GROUP_ID,
            ['nin' => (empty($groups) ? ['-1'] : $groups)]
        );
        $collection->addFieldToFilter(
            PriceListGroupsInterface::PRICE_LIST_ID,
            $priceList->getEntityId()
        );
        /** @var PriceListGroupsInterface $oldItem */
        foreach ($collection as $oldItem) {
            try {
                /** delete the old item via the repository interface */
                $this->priceListGroupsRepository->delete($oldItem);
            } catch (\Exception $e) {
                $this->logger->debug('Delete the old item via the repository interface: ' . $e->getMessage());
            }
        }
        foreach ($groups as $group) {
            /** @var PriceListGroupsInterface $item */
            $item = $this->priceListGroupsFactory->create();
            try {
                $collection = $this->priceListGroupsCollectionFactory->create();
                $collection->addFieldToFilter(
                    PriceListGroupsInterface::PRICE_LIST_CUSTOMER_GROUP_ID,
                    $group
                );
                $collection->addFieldToFilter(
                    PriceListGroupsInterface::PRICE_LIST_ID,
                    $priceList->getEntityId()
                );
                $original = $collection->getFirstItem();
                if ($original->getEntityId()) {
                    /** Keep the original database id if it alredy exists as there should only be one group per list */
                    $item->setEntityId($original->getEntityId());
                }
                $item->setCustomerGroupId($group);
                $item->setPriceListId($priceList->getEntityId());
                $this->priceListGroupsRepository->save($item);
            } catch (\Exception $e) {
                $this->logger->debug('Save customer group: ' . $e->getMessage());
            }
        }
    }
}
