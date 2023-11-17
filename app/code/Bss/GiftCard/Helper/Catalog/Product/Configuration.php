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

namespace Bss\GiftCard\Helper\Catalog\Product;

use Bss\GiftCard\Model\AmountsFactory;
use Bss\GiftCard\Model\Template\ImageFactory;
use Bss\GiftCard\Model\TemplateFactory;
use Magento\Catalog\Helper\Product\Configuration as CatalogConfiguration;
use Magento\Catalog\Helper\Product\Configuration\ConfigurationInterface;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Escaper;
use Magento\Framework\Pricing\PriceCurrencyInterface;

/**
 * Class configuration
 *
 * Bss\GiftCard\Helper\Catalog\Product
 */
class Configuration extends AbstractHelper implements ConfigurationInterface
{
    public const GIFTCARD_AMOUNT = 'bss_giftcard_amount';

    public const GIFTCARD_AMOUNT_DYNAMIC = 'bss_giftcard_amount_dynamic';

    public const GIFTCARD_SENDER_NAME = 'bss_giftcard_sender_name';

    public const GIFTCARD_SENDER_EMAIL = 'bss_giftcard_sender_email';

    public const GIFTCARD_RECIPIENT_NAME = 'bss_giftcard_recipient_name';

    public const GIFTCARD_RECIPIENT_EMAIL = 'bss_giftcard_recipient_email';

    public const GIFTCARD_MESSAGE_EMAIL = 'bss_giftcard_message_email';

    public const GIFTCARD_DELIVERY_DATE = 'bss_giftcard_delivery_date';

    public const GIFTCARD_TIMEZONE = 'bss_giftcard_timezone';

    public const GIFTCARD_TEMPLATE = 'bss_giftcard_template';

    public const GIFTCARD_IMAGE = 'bss_giftcard_selected_image';

    /**
     * @var \Magento\Framework\Pricing\Helper\Data
     */
    protected $pricingHelper;

    /**
     * @var Configuration
     */
    protected $productConfiguration;

    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @var ImageFactory
     */
    private $imageFactory;

    /**
     * @var TemplateFactory
     */
    private $templateFactory;

    /**
     * @var PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var AmountsFactory
     */
    private $amountsFactory;

    /**
     * @param Context $context
     * @param CatalogConfiguration $productConfiguration
     * @param ImageFactory $imageFactory
     * @param TemplateFactory $templateFactory
     * @param Escaper $escaper
     * @param PriceCurrencyInterface $priceCurrency
     * @param AmountsFactory $amountsFactory
     */
    public function __construct(
        Context $context,
        CatalogConfiguration $productConfiguration,
        ImageFactory $imageFactory,
        TemplateFactory $templateFactory,
        Escaper $escaper,
        PriceCurrencyInterface $priceCurrency,
        AmountsFactory $amountsFactory
    ) {
        parent::__construct($context);
        $this->productConfiguration = $productConfiguration;
        $this->imageFactory = $imageFactory;
        $this->templateFactory = $templateFactory;
        $this->escaper = $escaper;
        $this->priceCurrency = $priceCurrency;
        $this->amountsFactory = $amountsFactory;
    }

    /**
     * Get buy request option
     *
     * @return array
     */
    public function getBuyRequestOptions()
    {
        return [
            self::GIFTCARD_AMOUNT,
            self::GIFTCARD_AMOUNT_DYNAMIC,
            self::GIFTCARD_SENDER_NAME,
            self::GIFTCARD_SENDER_EMAIL,
            self::GIFTCARD_RECIPIENT_NAME,
            self::GIFTCARD_RECIPIENT_EMAIL,
            self::GIFTCARD_MESSAGE_EMAIL,
            self::GIFTCARD_DELIVERY_DATE,
            self::GIFTCARD_TEMPLATE,
            self::GIFTCARD_TIMEZONE,
            self::GIFTCARD_IMAGE
        ];
    }

    /**
     * Returns array of options objects.
     *
     * Each option object will contain array of selections objects
     *
     * @param ItemInterface $item
     * @param bool $isRenderOptions
     * @return array
     */
    public function getGiftCardOptions(ItemInterface $item, $isRenderOptions = true)
    {
        $options = [];
        $requestOptions = $this->getBuyRequestOptions();
        foreach ($requestOptions as $code) {
            if ($optionValue = $this->getCustomOption($item, $code)) {
                $options[$code] = $optionValue;
            }
        }

        if ($isRenderOptions) {
            return $this->renderOptions($options);
        }
        return $options;
    }

    /**
     * Get custom option
     *
     * @param ItemInterface $item
     * @param string $code
     * @return string|null
     */
    private function getCustomOption(ItemInterface $item, $code)
    {
        $option = $item->getOptionByCode($code);
        if ($option) {
            $value = $option->getValue();
            return $value ? $value : null;
        }
        return null;
    }

    /**
     * Get option
     *
     * @param ItemInterface $item
     * @return array
     */
    public function getOptions(ItemInterface $item)
    {
        return array_merge(
            $this->getGiftCardOptions($item),
            $this->productConfiguration->getCustomOptions($item)
        );
    }

    /**
     * Render thumbnail
     *
     * @param int $value
     * @return string
     */
    private function renderThumbnail($value)
    {
        $image = $this->imageFactory->create()->load($value);
        return '<img src="' . $image->getThumbnail() . '" alt="' . $image->getLabel() . '" class="bss-gc-img"/>';
    }

    /**
     * Render option
     *
     * @param array $options
     * @param int|null $storeId
     * @return array
     */
    public function renderOptions($options, $storeId = null)
    {
        $data = [];
        if ($amount = $this->renderAmount($options)) {
            if ($storeId) {
                $valueConverted = $this->priceCurrency->convertAndFormat(
                    $amount,
                    true,
                    \Magento\Framework\Pricing\PriceCurrencyInterface::DEFAULT_PRECISION,
                    $storeId
                );
            } else {
                $valueConverted = $this->priceCurrency->convertAndFormat($amount);
            }
            $data[] = [
                'label' => __('Value'),
                'value' => $valueConverted,
                'option_type' => 'html'
            ];
        }

        if (isset($options[self::GIFTCARD_SENDER_NAME])) {
            $senderName = $options[self::GIFTCARD_SENDER_NAME];
            if (isset($options[self::GIFTCARD_SENDER_EMAIL])) {
                $senderEmail = $options[self::GIFTCARD_SENDER_EMAIL];
                $sender = "{$senderName} &lt;{$senderEmail}&gt;";
            } else {
                $sender = $senderName;
            }
            $data[] = [
                'label' => __('Sender'),
                'value' => $this->escaper->escapeHtml($sender),
                'option_type' => 'html'
            ];
        }

        if (isset($options[self::GIFTCARD_RECIPIENT_NAME])) {
            $recipientName = $options[self::GIFTCARD_RECIPIENT_NAME];
            if (isset($options[self::GIFTCARD_RECIPIENT_EMAIL])) {
                $recipientEmail = $options[self::GIFTCARD_RECIPIENT_EMAIL];
                $recipient = "{$recipientName} &lt;{$recipientEmail}&gt;";
            } else {
                $recipient = $recipientName;
            }
            $data[] = [
                'label' => __('Recipient'),
                'value' => $this->escaper->escapeHtml($recipient),
                'option_type' => 'html'
            ];
        }

        if (isset($options[self::GIFTCARD_TEMPLATE])) {
            $templateId = $options[self::GIFTCARD_TEMPLATE];
            $template = $this->templateFactory->create()->load($templateId);
            $data[] = [
                'label' => __('Template'),
                'value' => $this->escaper->escapeHtml($template->getName())
            ];
        }

        if (isset($options[self::GIFTCARD_IMAGE]) && $options[self::GIFTCARD_IMAGE]) {
            $image = $options[self::GIFTCARD_IMAGE];
            $data[] = [
                'label' => __('Image'),
                'value' => $this->renderThumbnail($image),
                'option_type' => 'html'
            ];
        }
        $data = $this->renderOtherOptions($options, $data);
        return $data;
    }

    /**
     * Render other option
     *
     * @param array $options
     * @param array $data
     * @return array
     */
    private function renderOtherOptions($options, $data)
    {
        if (isset($options[self::GIFTCARD_MESSAGE_EMAIL])) {
            $message = $options[self::GIFTCARD_MESSAGE_EMAIL];
            $data[] = [
                'label' => __('Message'),
                'value' => $this->escaper->escapeHtml($message)
            ];
        }
        if (isset($options[self::GIFTCARD_DELIVERY_DATE])) {
            $delivery = $options[self::GIFTCARD_DELIVERY_DATE];
            $data[] = [
                'label' => __('Delivery Date'),
                'value' => $this->escaper->escapeHtml($delivery)
            ];
        }

        if (isset($options[self::GIFTCARD_TIMEZONE])) {
            $timezone = $options[self::GIFTCARD_TIMEZONE];
            $data[] = [
                'label' => __('TimeZone'),
                'value' => $this->escaper->escapeHtml($timezone)
            ];
        }
        return $data;
    }

    /**
     * Render amount
     *
     * @param array $options
     * @return mixed
     */
    public function renderAmount($options)
    {
        if (isset($options[self::GIFTCARD_AMOUNT])) {
            $amount = $options[self::GIFTCARD_AMOUNT];
            if ($amount == 'custom') {
                $amount = $options[self::GIFTCARD_AMOUNT_DYNAMIC];
            } else {
                $amountModel = $this->amountsFactory->create()->load($amount);
                if ($amountModel) {
                    $amount = $amountModel->getValue();
                }
            }
            return $amount;
        }
        return false;
    }
}
