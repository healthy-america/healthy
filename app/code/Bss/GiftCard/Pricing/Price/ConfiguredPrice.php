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

namespace Bss\GiftCard\Pricing\Price;

use Bss\GiftCard\Model\Product\Type\GiftCard\Price as GiftCardPrice;
use Magento\Catalog\Model\Product\Configuration\Item\ItemInterface;
use Magento\Framework\Pricing\Adjustment\CalculatorInterface;
use Magento\Framework\Pricing\Price\AbstractPrice;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Pricing\SaleableInterface;

class ConfiguredPrice extends AbstractPrice
{
    /**
     * Price type configured
     */
    public const PRICE_CODE = 'configured_price';

    /**
     * @var ItemInterface
     */
    private $item;

    /**
     * @var GiftCardPrice
     */
    protected $giftCardPrice;

    /**
     * ConfiguredPrice constructor.
     *
     * @param SaleableInterface      $saleableItem
     * @param float                  $quantity
     * @param CalculatorInterface    $calculator
     * @param PriceCurrencyInterface $priceCurrency
     * @param GiftCardPrice          $giftCardPrice
     */
    public function __construct(
        SaleableInterface $saleableItem,
        $quantity,
        CalculatorInterface $calculator,
        PriceCurrencyInterface $priceCurrency,
        GiftCardPrice $giftCardPrice
    ) {
        $this->giftCardPrice = $giftCardPrice;
        parent::__construct(
            $saleableItem,
            $quantity,
            $calculator,
            $priceCurrency
        );
    }

    /**
     * Get value
     *
     * @return bool|float|int
     */
    public function getValue()
    {
        return 0;
    }

    /**
     * Get final Gift card price
     *
     * @return string
     */
    public function getGiftCardPrice()
    {
        if ($product = $this->item->getProduct()) {
            return $this->priceCurrency->convertAndFormat(
                $this->giftCardPrice->getFinalPrice(
                    $this->item->getData('qty'),
                    $product
                )
            );
        }

        return $this->priceCurrency->convertAndFormat(0);
    }

    /**
     * Set item
     *
     * @param ItemInterface $item
     *
     * @return $this
     */
    public function setItem(ItemInterface $item)
    {
        $this->item = $item;
        return $this;
    }
}
