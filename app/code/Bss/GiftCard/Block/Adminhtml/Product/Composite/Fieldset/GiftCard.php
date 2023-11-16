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
namespace Bss\GiftCard\Block\Adminhtml\Product\Composite\Fieldset;

use Bss\GiftCard\Model\Product\Type\GiftCard as GiftCardType;

/**
 * Class gift card
 * Bss\GiftCard\Block\Adminhtml\Product\Composite\Fieldset
 */
class GiftCard extends \Magento\ConfigurableProduct\Block\Adminhtml\Product\Composite\Fieldset\Configurable
{
    /**
     * Get amount data
     *
     * @return array
     */
    public function getAmountData()
    {
        $product = $this->getProduct();
        $amounts = [];
        $amountsData = $product->getData(GiftCardType::BSS_GIFT_CARD_AMOUNTS);
        if (!empty($amountsData)) {
            foreach ($amountsData as $amount) {
                if ($amount['website_id'] == 0 || $this->getCurrentStore()->getWebsiteId() == $amount['website_id']) {
                    $amounts['amountList'] = [
                        'price' => $this->priceCurrency->convert($amount['price']),
                        'value' => (int) $amount['amount_id'],
                    ];
                }
            }
        }
        $dynamicPrice = $product->getData(GiftCardType::BSS_GIFT_CARD_DYNAMIC_PRICE);
        $minAmount = $product->getData(GiftCardType::BSS_GIFT_CARD_OPEN_MIN_AMOUNT);
        $maxAmount = $product->getData(GiftCardType::BSS_GIFT_CARD_OPEN_MAX_AMOUNT);
        if ($dynamicPrice && $maxAmount && $minAmount) {
            $amounts['amountDynamic'] = [
                'minAmount' => $this->priceCurrency->convert($minAmount),
                'maxAmount' => $this->priceCurrency->convert($maxAmount)
            ];
            if ($product->getData(GiftCardType::BSS_GIFT_CARD_PERCENTAGE_TYPE)) {
                $amounts['amountDynamic']['percentageValue'] = $product->getData(
                    GiftCardType::BSS_GIFT_CARD_PERCENTAGE_VALUE
                );
            }
        }

        return $amounts;
    }
}
