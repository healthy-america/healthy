<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\PriceLists\Controller\Adminhtml\PriceListCategories;

use Aventi\PriceLists\Api\Data\PriceListCategoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Aventi\PriceLists\Api\Data\PriceListCategoryInterfaceFactory;
use Aventi\PriceLists\Api\PriceListCategoryRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Controller\Result\JsonFactory;

class Save extends \Magento\Backend\App\Action
{
    protected $dataPersistor;

    /**
     * @var PriceListCategoryRepositoryInterface
     */
    private PriceListCategoryRepositoryInterface $priceListCategoryRepository;

    /**
     * @var PriceListCategoryInterfaceFactory
     */
    private PriceListCategoryInterfaceFactory $priceListCategoryFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var JsonFactory
     */
    private JsonFactory $jsonFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor
     * @param PriceListCategoryRepositoryInterface $priceListCategoryRepository
     * @param PriceListCategoryInterfaceFactory $priceListCategoryFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param JsonFactory $jsonFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\App\Request\DataPersistorInterface $dataPersistor,
        PriceListCategoryRepositoryInterface $priceListCategoryRepository,
        PriceListCategoryInterfaceFactory  $priceListCategoryFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        JsonFactory            $jsonFactory
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->priceListCategoryRepository =$priceListCategoryRepository;
        $this->priceListCategoryFactory = $priceListCategoryFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->jsonFactory = $jsonFactory;
        parent::__construct($context);
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonFactory->create();
        $messages = [
            'message' => __('Please correct the data sent.'),
            'error' => false
        ];
        $categoriesError = '';
        $error = false;
        $data = $this->getRequest()->getPostValue();
        if (!$data) {
            return  $resultJson->setData($messages);
        }
        foreach ($data['category_ids'] as $categoryId) {
            try {
                $categoryRegister = $this->getRowData($data['price_list_id'], $categoryId);
                if (!$categoryRegister) {
                    $categoryRegister = $this->priceListCategoryFactory->create();
                    $categoryRegister->setPriceListId($data['price_list_id']);
                    $categoryRegister->setCategoryId($categoryId);
                }
                $categoryRegister->setCategiryPrice($data['category_price']);
                $categoryRegister->setCategoryRuleType($data['category_rule_type']);
                $this->priceListCategoryRepository->save($categoryRegister);
            } catch (\Exception $e) {
                $error = true;
                $categoriesError .= $categoryId . ', ';
            }
        }
        if (!$error) {
            $this->dataPersistor->clear(\Aventi\PriceLists\Model\PriceListCategory::TABLE);
            $messages['messages'] = __('You saved the category in price list.');
        } else {
            $this->dataPersistor->set(\Aventi\PriceLists\Model\PriceListProducts::TABLE, $data);
            $messages['messages'] = __('Categories with error : ' . rtrim($categoriesError, ", "));
        }


        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     * @param $priceListId
     * @param $categoryId
     * @return mixed
     * @throws LocalizedException
     */
    private function getRowData($priceListId, $categoryId): mixed
    {
        $row = null;
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(PriceListCategoryInterface::PRICE_LIST_ID, $priceListId)
            ->addFilter(PriceListCategoryInterface::PRICE_LIST_CATEGORY_ID, $categoryId)
            ->create();
        $searchResults = $this->priceListCategoryRepository->getList($searchCriteria);
        if ($searchResults->getTotalCount() > 0) {
            $items = $searchResults->getItems();
            $row = reset($items);
        }

        return $row;
    }
}
