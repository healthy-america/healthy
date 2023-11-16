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

namespace Bss\GiftCard\Controller\Adminhtml;

use Bss\GiftCard\Api\PatternRepositoryInterface;
use Bss\GiftCard\Api\TemplateRepositoryInterface;
use Bss\GiftCard\Model\Pattern\CodeFactory;
use Bss\GiftCard\Model\PatternFactory;
use Bss\GiftCard\Model\ResourceModel\Pattern\Code\CollectionFactory as CodeCollection;
use Bss\GiftCard\Model\ResourceModel\Pattern\CollectionFactory as PatternCollection;
use Bss\GiftCard\Model\ResourceModel\Template\CollectionFactory as TemplateCollection;
use Bss\GiftCard\Model\Template\Image\Config as ImageConfig;
use Bss\GiftCard\Model\TemplateFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Component\ComponentRegistrar;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\Result\RawFactory;
use Magento\Framework\File\Csv;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Framework\Image\AdapterFactory;
use Magento\Framework\Json\Helper\Data;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\View\Result\PageFactory;
use Magento\MediaStorage\Model\File\UploaderFactory;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface;

/**
 * Class abstract gift card
 *
 * Bss\GiftCard\Controller\Adminhtml
 * @SuppressWarnings(PHPMD.TooManyFields)
 * @SuppressWarnings(PHPMD.NumberOfChildren)
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
abstract class AbstractGiftCard extends Action
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    public const ADMIN_RESOURCE = 'Bss_GiftCard::giftcard';

    /**
     * @var PageFactory
     */
    public $resultPageFactory;

    /**
     * @var RawFactory
     */
    protected $resultRawFactory;

    /**
     * @var JsonFactory
     */
    protected $resultJsonFactory;

    /**
     * @var TemplateRepositoryInterface
     */
    protected $templateService;

    /**
     * @var PatternRepositoryInterface
     */
    protected $patternService;

    /**
     * @var CodeFactory
     */
    protected $codeFactory;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Data
     */
    public $jsonHelper;

    /**
     * @var LoggerInterface
     */
    public $logger;

    /**
     * @var TemplateFactory
     */
    protected $giftCardTemplate;

    /**
     * @var PatternFactory
     */
    protected $giftCardPattern;

    /**
     * @var FileFactory
     */
    protected $fileFactory;

    /**
     * @var UploaderFactory
     */
    protected $fileUploaderFactory;

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $csvProcessor;

    /**
     * @var DateTime
     */
    protected $dateTime;

    /**
     * Massactions filter
     *
     * @var Filter
     */
    protected $filter;

    /**
     * @var TemplateCollection
     */
    protected $templatesFactory;

    /**
     * @var PatternCollection
     */
    protected $patternFactory;

    /**
     * @var \Magento\Framework\Component\ComponentRegistrar
     */
    protected $componentRegistrar;

    /**
     * @var \Magento\Framework\Filesystem\Directory\ReadFactory
     */
    protected $readFactory;

    /**
     * @var ImageConfig;
     */
    protected $imageConfig;

    /**
     * @var Filesystem;
     */
    protected $fileSystem;

    /**
     * @var AdapterFactory;
     */
    protected $imageAdapter;

    /**
     * @var CodeCollection
     */
    protected $codeCollection;

    /**
     * @param TemplateFactory $giftCardTemplate
     * @param PatternFactory $giftCardPattern
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param RawFactory $resultRawFactory
     * @param LoggerInterface $logger
     * @param TemplateRepositoryInterface $templateService
     * @param PatternRepositoryInterface $patternService
     * @param Data $jsonHelper
     * @param FileFactory $fileFactory
     * @param UploaderFactory $fileUploaderFactory
     * @param CodeFactory $codeFactory
     * @param Csv $csvProcessor
     * @param Registry $registry
     * @param DateTime $dateTime
     * @param Filter $filter
     * @param TemplateCollection $templatesFactory
     * @param ComponentRegistrar $componentRegistrar
     * @param ReadFactory $readFactory
     * @param PatternCollection $patternFactory
     * @param ImageConfig $imageConfig
     * @param Filesystem $fileSystem
     * @param AdapterFactory $imageAdapter
     * @param JsonFactory $resultJsonFactory
     * @param CodeCollection $codeCollection
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        TemplateFactory $giftCardTemplate,
        PatternFactory $giftCardPattern,
        Context $context,
        PageFactory $resultPageFactory,
        RawFactory $resultRawFactory,
        LoggerInterface $logger,
        TemplateRepositoryInterface $templateService,
        PatternRepositoryInterface $patternService,
        Data $jsonHelper,
        FileFactory $fileFactory,
        UploaderFactory $fileUploaderFactory,
        CodeFactory $codeFactory,
        Csv $csvProcessor,
        Registry $registry,
        DateTime $dateTime,
        Filter $filter,
        TemplateCollection $templatesFactory,
        ComponentRegistrar $componentRegistrar,
        ReadFactory $readFactory,
        PatternCollection $patternFactory,
        ImageConfig $imageConfig,
        Filesystem $fileSystem,
        AdapterFactory $imageAdapter,
        JsonFactory $resultJsonFactory,
        CodeCollection $codeCollection
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
        $this->resultRawFactory = $resultRawFactory;
        $this->giftCardTemplate = $giftCardTemplate;
        $this->giftCardPattern = $giftCardPattern;
        $this->logger = $logger;
        $this->templateService = $templateService;
        $this->patternService = $patternService;
        $this->jsonHelper = $jsonHelper;
        $this->fileFactory = $fileFactory;
        $this->fileUploaderFactory = $fileUploaderFactory;
        $this->codeFactory = $codeFactory;
        $this->csvProcessor = $csvProcessor;
        $this->registry = $registry;
        $this->dateTime = $dateTime;
        $this->filter = $filter;
        $this->templatesFactory = $templatesFactory;
        $this->componentRegistrar = $componentRegistrar;
        $this->readFactory = $readFactory;
        $this->patternFactory = $patternFactory;
        $this->imageConfig = $imageConfig;
        $this->fileSystem = $fileSystem;
        $this->imageAdapter = $imageAdapter;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->codeCollection = $codeCollection;
    }
}
