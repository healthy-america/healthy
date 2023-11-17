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

namespace Bss\GiftCard\Model\Attribute\Source;

use Bss\GiftCard\Model\Product\Type\GiftCard as GiftCardType;
use Magento\Eav\Model\Entity\Attribute\Source\AbstractSource;

/**
 * Class type
 *
 * Bss\GiftCard\Model\Attribute\Source
 */
class Type extends AbstractSource
{
    /**
     * @inheritdoc
     */
    public function getAllOptions()
    {
        if (null === $this->_options) {
            $this->_options = [
                [
                    'label' => __('Virtual'),
                    'value' => GiftCardType::BSS_GIFT_CARD_VIRTUAL_TYPE
                ],
                [
                    'label' => __('Physical'),
                    'value' => GiftCardType::BSS_GIFT_CARD_PHYSICAL_TYPE
                ],
                [
                    'label' => __('Combined'),
                    'value' => GiftCardType::BSS_GIFT_CARD_COMBINED_TYPE
                ]
            ];
        }
        return $this->_options;
    }
}
