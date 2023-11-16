<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_GiftCard
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\GiftCard\Model;

use Bss\GiftCard\Api\TemplateRepositoryInterface;
use Bss\GiftCard\Model\Template\ImageFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessor;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Api\SearchResultsInterfaceFactory;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class template repository
 *
 * Bss\GiftCard\Model
 */
class TemplateRepository implements TemplateRepositoryInterface
{
    /**
     * @var ImageFactory
     */
    private $imageModel;

    /**
     * @var TemplateFactory
     */
    private $templateFactory;

    /**
     * @var ResourceModel\Template\CollectionFactory
     */
    private $templateCollection;

    /**
     * @var CollectionProcessor
     */
    private $collectionProcessor;

    /**
     * @var SearchResultsInterfaceFactory
     */
    private $searchResultsFactory;

    /**
     * TemplateRepository constructor.
     *
     * @param ImageFactory $imageModel
     * @param ResourceModel\Template\CollectionFactory $templateCollection
     * @param TemplateFactory $templateFactory
     * @param SearchResultsInterfaceFactory $searchResultsFactory
     * @param CollectionProcessor $collectionProcessor
     */
    public function __construct(
        ImageFactory $imageModel,
        ResourceModel\Template\CollectionFactory $templateCollection,
        TemplateFactory $templateFactory,
        SearchResultsInterfaceFactory $searchResultsFactory,
        CollectionProcessor $collectionProcessor
    ) {
        $this->imageModel = $imageModel;
        $this->templateFactory = $templateFactory;
        $this->templateCollection = $templateCollection;
        $this->collectionProcessor = $collectionProcessor;
        $this->searchResultsFactory = $searchResultsFactory;
    }

    /**
     * Get template id
     *
     * @param int $templateId
     * @return array|mixed
     * @throws NoSuchEntityException
     */
    public function getTemplateById($templateId)
    {
        $template = $this->templateFactory->create();
        if (!$templateId) {
            throw new NoSuchEntityException(__('Template with id "%1" does not exist.', $templateId));
        }
        $templateData = $template->load($templateId)->getData();
        $templateData['images'] = $this->imageModel->create()->loadByTemplate($templateId);
        return [
            'template_data' => $templateData
        ];
    }

    /**
     * @inheritDoc
     */
    public function getList(SearchCriteriaInterface $criteria)
    {
        $searchResult = $this->searchResultsFactory->create();
        $collection = $this->templateCollection->create();
        $this->collectionProcessor->process($criteria, $collection);
        $searchResult->setItems($collection->getItems());
        $searchResult->setTotalCount($collection->getSize());

        return $searchResult;
    }
}
