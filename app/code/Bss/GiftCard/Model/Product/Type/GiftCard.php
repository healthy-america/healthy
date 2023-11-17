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
 * @copyright  Copyright (c) 2017-2022 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */
namespace Bss\GiftCard\Model\Product\Type;

use Magento\Framework\Exception\LocalizedException;
use Magento\Catalog\Model\Product\Type\AbstractType;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\Product\Option;
use Magento\Eav\Model\Config;
use Magento\Catalog\Model\Product\Type;
use Magento\Framework\Event\ManagerInterface;
use Magento\MediaStorage\Helper\File\Storage\Database;
use Magento\Framework\Filesystem;
use Magento\Framework\Registry;
use Psr\Log\LoggerInterface;
use Bss\GiftCard\Helper\Catalog\Product\Configuration;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Bss\GiftCard\Model\ResourceModel\Amounts;
use Bss\GiftCard\Model\TemplateFactory;
use Bss\GiftCard\Helper\Data;
use Magento\Framework\DataObjectFactory;
use Bss\GiftCard\Model\Template\ImageFactory;

/**
 * Class gift card
 *
 * Bss\GiftCard\Model\Product\Type
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GiftCard extends AbstractType
{
    public const BSS_GIFT_CARD = 'bss_giftcard';

    public const BSS_GIFT_CARD_GROUP = 'Bss Gift Card Information';

    public const BSS_GIFT_CARD_GROUP_CODE = 'bss-gift-card-information';

    public const BSS_GIFT_CARD_AMOUNTS = 'bss_gift_card_amounts';

    public const BSS_GIFT_CARD_DYNAMIC_PRICE = 'bss_gift_card_dynamic_price';

    public const BSS_GIFT_CARD_OPEN_MIN_AMOUNT = 'bss_gift_card_open_min_amount';

    public const BSS_GIFT_CARD_OPEN_MAX_AMOUNT = 'bss_gift_card_open_max_amount';

    public const BSS_GIFT_CARD_PERCENTAGE_TYPE = 'bss_gift_card_percentage_type';

    public const BSS_GIFT_CARD_PERCENTAGE_VALUE = 'bss_gift_card_percentage_value';

    public const BSS_GIFT_CARD_TEMPLATE = 'bss_gift_card_template';

    public const BSS_GIFT_CARD_CODE_PATTERN = 'bss_gift_card_code_pattern';

    public const BSS_GIFT_CARD_MESSAGE = 'bss_gift_card_message';

    public const BSS_GIFT_CARD_EXPIRES = 'bss_gift_card_expires';

    public const BSS_GIFT_CARD_VIRTUAL_TYPE = 1;

    public const BSS_GIFT_CARD_PHYSICAL_TYPE = 2;

    public const BSS_GIFT_CARD_COMBINED_TYPE = 3;

    public const BSS_GIFT_CARD_SAME_VALUE = 0;

    public const BSS_GIFT_CARD_PERCENTAGR_VALUE = 1;

    /**
     * @var Configuration
     */
    private $configurationGiftCard;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var Amounts
     */
    private $amountsResource;

    /**
     * @var TemplateFactory
     */
    private $templateFactory;

    /**
     * @var \Bss\GiftCard\Helper\Data
     */
    private $helper;

    /**
     * If product can be configured
     *
     * @var bool
     */
    protected $_canConfigure = true;

    /**
     * @var DataObjectFactory
     */
    private $dataObjectFactory;

    /**
     * @var ImageFactory
     */
    private $imageModel;

    /**
     * Construct
     *
     * @param Option $catalogProductOption
     * @param Config $eavConfig
     * @param Type $catalogProductType
     * @param ManagerInterface $eventManager
     * @param Database $fileStorageDb
     * @param Filesystem $filesystem
     * @param Registry $coreRegistry
     * @param LoggerInterface $logger
     * @param ProductRepositoryInterface $productRepository
     * @param Configuration $configurationGiftCard
     * @param PriceCurrencyInterface $priceCurrency
     * @param Amounts $amountsResource
     * @param DataObjectFactory $dataObjectFactory
     * @param TemplateFactory $templateFactory
     * @param ImageFactory $imageModel
     * @param Data $helper
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Option $catalogProductOption,
        Config $eavConfig,
        Type $catalogProductType,
        ManagerInterface $eventManager,
        Database $fileStorageDb,
        Filesystem $filesystem,
        Registry $coreRegistry,
        LoggerInterface $logger,
        ProductRepositoryInterface $productRepository,
        Configuration $configurationGiftCard,
        PriceCurrencyInterface $priceCurrency,
        Amounts $amountsResource,
        DataObjectFactory $dataObjectFactory,
        TemplateFactory $templateFactory,
        ImageFactory $imageModel,
        Data $helper
    ) {
        $this->configurationGiftCard = $configurationGiftCard;
        $this->priceCurrency = $priceCurrency;
        $this->amountsResource = $amountsResource;
        $this->templateFactory = $templateFactory;
        $this->helper = $helper;
        $this->dataObjectFactory = $dataObjectFactory;
        $this->imageModel = $imageModel;
        parent::__construct(
            $catalogProductOption,
            $eavConfig,
            $catalogProductType,
            $eventManager,
            $fileStorageDb,
            $filesystem,
            $coreRegistry,
            $logger,
            $productRepository
        );
    }

    /**
     * Delete data specific for Simple product type
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function deleteTypeSpecificData(\Magento\Catalog\Model\Product $product)
    {
        $productType = self::BSS_GIFT_CARD_PHYSICAL_TYPE;
    }

    /**
     * Is physical
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function isPhysical($product)
    {
        $productType = $this->getAttributeByProduct($product, self::BSS_GIFT_CARD);
        return $productType == self::BSS_GIFT_CARD_PHYSICAL_TYPE;
    }

    /**
     * Is combined
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function isCombined($product)
    {
        $productType = $this->getAttributeByProduct($product, self::BSS_GIFT_CARD);
        return $productType == self::BSS_GIFT_CARD_COMBINED_TYPE;
    }

    /**
     * Check is virtual product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function isVirtual($product)
    {
        $productType = $this->getAttributeByProduct($product, self::BSS_GIFT_CARD);
        return $productType == self::BSS_GIFT_CARD_VIRTUAL_TYPE;
    }

    /**
     * Check is product available for sale
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function isSalable($product)
    {
        if (!$this->helper->isEnabled()) {
            return false;
        }

        $salable = parent::isSalable($product);

        if ($salable !== false) {
            $productId = $product->getId();
            $templateData = $this->templateFactory->create()->loadProductTemplate($productId);
            $product->getResource()->load($product, $productId);
            $amountsData = $product->getData(self::BSS_GIFT_CARD_AMOUNTS);
            $dynamicPrice = $product->getData(self::BSS_GIFT_CARD_DYNAMIC_PRICE);
            $minAmount = $product->getData(self::BSS_GIFT_CARD_OPEN_MIN_AMOUNT);
            $maxAmount = $product->getData(self::BSS_GIFT_CARD_OPEN_MAX_AMOUNT);
            if (!empty($templateData) && (!empty($amountsData) || $dynamicPrice && $minAmount && $maxAmount)) {
                $salable = true;
            } else {
                $salable = false;
            }
        }

        return $salable;
    }

    /**
     * Prepare product
     *
     * @param \Magento\Framework\DataObject $buyRequest
     * @param \Magento\Catalog\Model\Product $product
     * @param string $processMode
     * @return \Magento\Framework\Phrase|array|string
     */
    protected function _prepareProduct(\Magento\Framework\DataObject $buyRequest, $product, $processMode)
    {
        $result = parent::_prepareProduct($buyRequest, $product, $processMode);
        if ($giftCardData = $buyRequest->getGiftcardOptions()) {
            $buyRequest = $this->dataObjectFactory->create(['data' => $giftCardData]);
        }
        if (is_array($result)) {
            try {
                if (!$buyRequest->getData('product_action')) {
                    $this->validateGiftCard($buyRequest, $product, $processMode);
                }
            } catch (LocalizedException $e) {
                return $e->getMessage();
            } catch (\Exception $e) {
                return __('An error has occurred while adding product to cart.');
            }
            $buyRequestOptions = $this->configurationGiftCard->getBuyRequestOptions();
            if ($this->isPhysical($product)) {
                unset($buyRequestOptions['bss_giftcard_sender_email']);
                unset($buyRequestOptions['bss_giftcard_recipient_email']);
            }
            foreach ($buyRequestOptions as $code) {
                $data = $buyRequest->getData($code);
                if ($data) {
                    $product->addCustomOption(
                        $code,
                        $data,
                        $product
                    );
                }
            }
        }

        return $result;
    }

    /**
     * Validate gift card
     *
     * @param \Magento\Framework\DataObject $buyRequest
     * @param \Magento\Catalog\Model\Product $product
     * @param string $processMode
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function validateGiftCard($buyRequest, $product, $processMode)
    {
        $isStrictProcessMode = $this->_isStrictProcessMode($processMode);

        if ($isStrictProcessMode) {
            $amount = $buyRequest->getBssGiftcardAmount();
            $amountDynamic = $buyRequest->getBssGiftcardAmountDynamic();

            if ($this->validateRequiredField($buyRequest, $product, $amount, $amountDynamic)) {
                throw new LocalizedException(
                    __('Please specify all the required information.')
                );
            }

            $templateId = $buyRequest->getBssGiftcardTemplate();
            if (!$this->validateTemplateNProduct($product, $templateId)) {
                throw new LocalizedException(
                    __('The template is not linked with the product.')
                );
            }

            $imageId = $buyRequest->getBssGiftcardSelectedImage();
            if (!$this->validateTemplateAndImage($templateId, $imageId)) {
                throw new LocalizedException(
                    __('The image is not linked with the template.')
                );
            }
        } else {
            $this->validateGiftCardLiteMode($buyRequest, $product);
        }
    }

    /**
     * Validate gift card product with lite mode.
     *
     * @param \Magento\Framework\DataObject $buyRequest
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function validateGiftCardLiteMode($buyRequest, $product)
    {
        $amount = $buyRequest->getBssGiftcardAmount();
        if ($amount !== null) {
            if ($amount == 'custom') {
                if (!$buyRequest->getBssGiftcardAmountDynamic() || !$product->getBssGiftCardDynamicPrice()) {
                    throw new LocalizedException(
                        __('Amount Dynamic is incorrect.')
                    );
                }
            } else {
                if (empty($this->amountsResource->validateAmounts($amount, $product->getId()))) {
                    throw new LocalizedException(
                        __('Amount is incorrect.')
                    );
                }
            }
        }

        $templateId = $buyRequest->getBssGiftcardTemplate();
        if ($templateId !== null) {
            if (!$this->validateTemplateNProduct($product, $templateId)) {
                throw new LocalizedException(
                    __('The template is not linked with the product.')
                );
            }
        }

        $imageId = $buyRequest->getBssGiftcardSelectedImage();
        if ($imageId !== null) {
            if (!$this->validateTemplateAndImage($templateId, $imageId)) {
                throw new LocalizedException(
                    __('The image is not linked with the template.')
                );
            }
        }
    }

    /**
     * Validate template and image
     *
     * @param int $templateId
     * @param int $imageId
     * @return bool|\Magento\Framework\DataObject
     */
    protected function validateTemplateAndImage($templateId, $imageId)
    {
        $image = $this->imageModel->create()->getById($imageId);
        return $image ? $image->getTemplateId() == $templateId : $image;
    }

    /**
     * Validate template and product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param int $templateId
     * @return bool
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function validateTemplateNProduct($product, $templateId)
    {
        //False if $templateId param is not found in $assignTemplates
        $templates = $this->templateFactory->create();
        $assignTemplates = $templates->loadProductTemplate($product->getId());
        $filteredTemplate = array_filter($assignTemplates, function ($item) use ($templateId) {
            return $item['template_id'] == $templateId;
        });

        if (empty($filteredTemplate)) {
            return false;
        }
        return true;
    }

    /**
     * Validate required field
     *
     * @param \Magento\Framework\DataObject $buyRequest
     * @param \Magento\Catalog\Model\Product $product
     * @param float $amount
     * @param mixed $amountDynamic
     * @return bool
     * @throws LocalizedException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function validateRequiredField($buyRequest, $product, $amount, $amountDynamic)
    {
        $dynamic = $product->getBssGiftCardDynamicPrice();
        $isPhysical = $product->getTypeInstance()->isPhysical($product);
        $productId = $product->getId();
        if (!$amount) {
            return true;
        }
        if ($amount == 'custom') {
            if (!$amountDynamic || !$dynamic) {
                return true;
            }
        } else {
            if (empty($this->amountsResource->validateAmounts($amount, $productId))) {
                return true;
            }
        }
        if (!$isPhysical && (!$buyRequest->getBssGiftcardSenderEmail()
            || !$buyRequest->getBssGiftcardRecipientEmail()
            || !$buyRequest->getBssGiftcardSenderName()
            || !$buyRequest->getBssGiftcardRecipientName())
        ) {
            return true;
        }
        return false;
    }

    /**
     * Validate dynamic
     *
     * @param mixed $amountDynamic
     * @param \Magento\Catalog\Model\Product $product
     * @return void
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function validateDynamic($amountDynamic, $product)
    {
        $minAmount = $this->priceCurrency->convert($product->getBssGiftCardOpenMinAmount());
        $maxAmount = $this->priceCurrency->convert($product->getBssGiftCardOpenMaxAmount());
        if ($amountDynamic > $maxAmount) {
            throw new LocalizedException(
                __('Max value is %1.', $maxAmount)
            );
        }
        if ($amountDynamic < $minAmount) {
            throw new LocalizedException(
                __('Min value is %1.', $minAmount)
            );
        }
    }

    /**
     * Process buy request
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param \Magento\Framework\DataObject $buyRequest
     * @return array
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function processBuyRequest($product, $buyRequest)
    {
        $options = [];
        foreach ($this->configurationGiftCard->getBuyRequestOptions() as $option) {
            if ($buyRequest->hasData($option)) {
                $options[$option] = $buyRequest->getData($option);
            }
        }
        return $options;
    }

    /**
     * Save
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return $this
     */
    public function beforeSave($product)
    {
        parent::beforeSave($product);
        $dynamicPrice = $product->getData(self::BSS_GIFT_CARD_DYNAMIC_PRICE);
        $dynamicMinAmount = $product->getData(self::BSS_GIFT_CARD_OPEN_MIN_AMOUNT);
        $dynamicMaxAmount = $product->getData(self::BSS_GIFT_CARD_OPEN_MAX_AMOUNT);

        if ($dynamicPrice) {
            if (!$dynamicMinAmount || !$dynamicMaxAmount) {
                throw new LocalizedException(__('You must fill out both Min and Max dynamic price fields'));
            } else {
                $dynamicMinAmount = str_replace(',', '', $dynamicMinAmount);
                $dynamicMinAmount = str_replace(' ', '', $dynamicMinAmount);
                $dynamicMaxAmount = str_replace(',', '', $dynamicMaxAmount);
                $dynamicMaxAmount = str_replace(' ', '', $dynamicMaxAmount);

                $dynamicMinAmount = (float)$dynamicMinAmount;
                $dynamicMaxAmount = (float)$dynamicMaxAmount;

                if ($dynamicMinAmount >= $dynamicMaxAmount) {
                    throw new LocalizedException(__('Max amount must be greater than Min amount'));
                }
            }
        }

        $product->setTypeHasOptions(true);
        $product->setTypeHasRequiredOptions(true);
        return $this;
    }

    /**
     * Get att by product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $code
     * @return mixed
     */
    private function getAttributeByProduct(\Magento\Catalog\Model\Product $product, $code)
    {
        if (!$product->hasData($code)) {
            $product->getResource()->load($product, $product->getId());
        }
        return $product->getData($code);
    }
}
