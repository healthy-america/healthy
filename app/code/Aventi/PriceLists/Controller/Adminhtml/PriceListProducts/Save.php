<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\PriceLists\Controller\Adminhtml\PriceListProducts;

use Aventi\PriceLists\Api\Data\PriceListProductsInterface;
use Aventi\PriceLists\Api\Data\PriceListProductsInterfaceFactory;
use Aventi\PriceLists\Api\PriceListProductsRepositoryInterface;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Exception\LocalizedException;

class Save extends \Magento\Backend\App\Action
{
    protected $dataPersistor;

    /**
     * @var JsonFactory
     */
    private JsonFactory $jsonFactory;

    /**
     * @var PriceListProductsRepositoryInterface
     */
    private PriceListProductsRepositoryInterface $priceListProductsRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private SearchCriteriaBuilder $searchCriteriaBuilder;

    /**
     * @var PriceListProductsInterfaceFactory
     */
    private PriceListProductsInterfaceFactory $priceListProductsFactory;

    /**
     * @param Context $context
     * @param DataPersistorInterface $dataPersistor
     * @param JsonFactory $jsonFactory
     * @param PriceListProductsRepositoryInterface $priceListProductsRepository
     * @param PriceListProductsInterfaceFactory $priceListProductsFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     */
    public function __construct(
        Context                $context,
        DataPersistorInterface $dataPersistor,
        JsonFactory            $jsonFactory,
        PriceListProductsRepositoryInterface $priceListProductsRepository,
        PriceListProductsInterfaceFactory $priceListProductsFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder
    ) {
        $this->dataPersistor = $dataPersistor;
        $this->jsonFactory = $jsonFactory;
        $this->priceListProductsRepository = $priceListProductsRepository;
        $this->priceListProductsFactory = $priceListProductsFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
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
        $productsError = '';
        $error = false;
        $data = $this->getRequest()->getPostValue();
        if (!$data) {
            return  $resultJson->setData($messages);
        }
        foreach ($data['product_ids'] as $productId) {
            try {
                $productRegister = $this->getRowData($data['price_list_id'], $productId);
                if (!$productRegister) {
                    $productRegister = $this->priceListProductsFactory->create();
                    $productRegister->setPriceListId($data['price_list_id']);
                    $productRegister->setProductId($productId);
                }
                $productRegister->setProductPrice($data['product_price']);
                $productRegister->setProductPriceSug($data['product_price_sug']);
                $productRegister->setProductRuleType($data['product_rule_type']);
                $this->priceListProductsRepository->save($productRegister);
            } catch (\Exception $e) {
                $error = true;
                $productsError .= $productId . ', ';
            }
        }
        if (!$error) {
            $this->dataPersistor->clear(\Aventi\PriceLists\Model\PriceListProducts::TABLE);
            $messages['messages'] = __('You saved the product in price list.');
        } else {
            $this->dataPersistor->set(\Aventi\PriceLists\Model\PriceListProducts::TABLE, $data);
            $messages['messages'] = __('Products with error : ' . rtrim($productsError, ", "));
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }

    /**
     * @param $priceListId
     * @param $productId
     * @return false|mixed|null
     * @throws LocalizedException
     */
    private function getRowData($priceListId, $productId): mixed
    {
        $row = null;
        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter(PriceListProductsInterface::PRICE_LIST_ID, $priceListId)
            ->addFilter(PriceListProductsInterface::PRICE_LIST_PRODUCT_ID, $productId)
            ->create();
        $searchResults = $this->priceListProductsRepository->getList($searchCriteria);
        if ($searchResults->getTotalCount() > 0) {
            $items = $searchResults->getItems();
            $row = reset($items);
        }

        return $row;
    }
}
