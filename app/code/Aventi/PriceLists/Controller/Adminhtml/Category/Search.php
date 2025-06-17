<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\PriceLists\Controller\Adminhtml\Category;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory;
use Magento\Catalog\Api\Data\CategoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SortOrderBuilder;
use Magento\Framework\Controller\Result\JsonFactory;

/**
 * Class SearchCategory
 * @package Aventi\PriceLists\Controller\Adminhtml\PriceList
 */
class Search extends Action
{

    /**
     * @var CollectionFactory
     */
    private CollectionFactory $categoryProductCollection;

    /**
     * @var JsonFactory
     */
    private JsonFactory $jsonResultFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var SortOrderBuilder
     */
    private $sortOrderBuilder;

    public function __construct(
        Context $context,
        JsonFactory $jsonResultFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CollectionFactory $categoryProductCollection,
        SortOrderBuilder $sortOrderBuilder,
        ProductRepositoryInterface $productRepository
    ) {
        $this->jsonResultFactory = $jsonResultFactory;
        $this->categoryProductCollection = $categoryProductCollection;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->productRepository = $productRepository;
        $this->sortOrderBuilder = $sortOrderBuilder;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $searchKey = $this->getRequest()->getParam('searchKey');
        $pageNum = (int)$this->getRequest()->getParam('page');
        $limit = (int)$this->getRequest()->getParam('limit');
        $categoryFactory = $this->categoryProductCollection->create();
        $categoryFactory->addAttributeToSelect('*');

        if ($searchKey) {
            $categoryFactory->addFieldToFilter('name', ['like' => '%' . $searchKey . '%']);
        }
        $categoryFactory->addOrder('name', SortOrder::SORT_ASC);
        $categoryFactory->setPageSize($limit);
        $categoryFactory->setCurPage($pageNum);
        $categories = $categoryFactory->getItems();
        $totalValues = count($categories);
        $customerById = [];
        /** @var  CategoryInterface $category */
        foreach ($categories as $category) {
            $categoryId = $category->getId();
            $customerById[$categoryId] = [
                'value' => $categoryId,
                'label' => $category->getName(),
                'is_active' => $category->getIsActive(),
                'path' => $category->getPosition(),
                'optgroup' => false
            ];
        }
        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->jsonResultFactory->create();
        return $resultJson->setData([
            'options' => $customerById,
            'total' => empty($customerById) ? 0 : $totalValues
        ]);
    }
}
