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

namespace Bss\GiftCard\Plugin\Block\Wishlist;

use Bss\GiftCard\Api\TemplateRepositoryInterface;
use Bss\GiftCard\Model\Product\Type\GiftCard\Price as GiftCardPrice;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Phrase;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Serialize\Serializer\Json as JsonSerialize;
use Magento\Wishlist\Block\Customer\Wishlist\Item\Options;

class GiftItemOptions
{
    /**
     * @var JsonSerialize
     */
    protected $jsonSerialize;

    /**
     * @var TemplateRepositoryInterface
     */
    protected $templateRepository;

    /**
     * @var GiftCardPrice
     */
    protected $giftCardPrice;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    /**
     * GiftItemOptions constructor.
     * @param JsonSerialize $jsonSerialize
     * @param TemplateRepositoryInterface $templateRepository
     * @param GiftCardPrice $giftCardPrice
     * @param PriceCurrencyInterface $priceCurrency
     */
    public function __construct(
        JsonSerialize $jsonSerialize,
        TemplateRepositoryInterface $templateRepository,
        GiftCardPrice $giftCardPrice,
        PriceCurrencyInterface $priceCurrency
    ) {
        $this->jsonSerialize = $jsonSerialize;
        $this->templateRepository = $templateRepository;
        $this->giftCardPrice = $giftCardPrice;
        $this->priceCurrency = $priceCurrency;
    }

    /**
     * Get template
     *
     * @param Options $options
     * @param string $template
     * @return string
     * @throws LocalizedException
     */
    public function afterGetTemplate(
        Options $options,
        $template
    ) {
        $type = $options->getItem()->getProduct()->getTypeId();
        if ($type === 'bss_giftcard') {
            return 'Bss_GiftCard::wishlist/options_list.phtml';
        }
        return $template;
    }

    /**
     * Get cofigured options
     *
     * @param Options $options
     * @param array $optionList
     * @return array
     * @throws LocalizedException
     */
    public function afterGetConfiguredOptions(
        Options $options,
        $optionList
    ) {
        $type = $options->getItem()->getProduct()->getTypeId();
        if ($type === 'bss_giftcard') {
            return $this->getValueInfoBuy($options);
        }
        return $optionList;
    }

    /**
     * Get value info buy
     *
     * @param Options $options
     * @return array
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function getValueInfoBuy($options)
    {
        $infoBuyRequest = $options->getItem()->getOptionByCode('info_buyRequest');
        $infoBuyArr = $infoBuyRequest ? $this->jsonSerialize->unserialize($infoBuyRequest->getValue()) : [];
        $itemId = $options->getItem()->getId();
        $qty = $infoBuyArr['qty'] ?? 1;
        $finalPrice = $this->giftCardPrice->getFinalPrice($qty, $options->getItem()->getProduct());
        // not use this key bss_giftcard_selected_image
        // bc it depend on key bss_giftcard_template
        $arrKey = [
            'bss_giftcard_sender_name' => 'Sender Name',
            'bss_giftcard_sender_email' => 'Sender Email',
            'bss_giftcard_recipient_name' => 'Recipient Name',
            'bss_giftcard_recipient_email' => 'Recipient Email',
            'bss_giftcard_template' => 'Template',
            'bss_giftcard_message_email' => 'Message',
            'bss_giftcard_delivery_date' => 'Delivery Date',
            'bss_giftcard_timezone' => 'Timezone'
        ];
        $optionList = [];
        foreach ($arrKey as $key => $label) {
            if (isset($infoBuyArr[$key]) && $infoBuyArr[$key]) {
                if ($key !== 'bss_giftcard_template') {
                    $optionList[] = [
                        'label' => $label,
                        'value' => $infoBuyArr[$key]
                    ];
                } else {
                    $template = $this->templateRepository->getTemplateById((int)$infoBuyArr[$key]);
                    if (isset($template['template_data']) && !empty($template['template_data'])) {
                        $optionList[] = [
                            'label' => $label,
                            'value' => $this->getTemplateDataName($template['template_data'])
                        ];
                        $selectedImageId = 0;
                        if (isset($infoBuyArr['bss_giftcard_selected_image'])) {
                            $selectedImageId = $infoBuyArr['bss_giftcard_selected_image'];
                        }
                        $optionList[] = [
                            'label' => 'Selected Image (click to view)',
                            'value' => $this->getTemplateDataUrlImage(
                                $template['template_data'],
                                $selectedImageId
                            ),
                            'image' => true
                        ];
                    }
                }
            }
        }
        $optionList['additional'] = [
            'label' => __('Additional Info'),
            'value' => $this->jsonSerialize->serialize([
                'item_id' => $itemId,
                'amount' => $this->priceCurrency->convertAndFormat($finalPrice)
            ])
        ];
        return $optionList;
    }

    /**
     * Get template data name
     *
     * @param array $templateData
     * @return Phrase|string
     */
    private function getTemplateDataName($templateData)
    {
        if (isset($templateData['name'])) {
            return $templateData['name'];
        }
        return __(' ');
    }

    /**
     * Get template data url image
     *
     * @param array $templateData
     * @param int|string $selectedImage
     * @return string
     */
    private function getTemplateDataUrlImage($templateData, $selectedImage)
    {
        if (isset($templateData['images'])) {
            foreach ($templateData['images'] as $image) {
                if (isset($image['id']) && $image['id'] == $selectedImage) {
                    return $image['url'];
                }
            }
        }
        return '#';
    }
}
