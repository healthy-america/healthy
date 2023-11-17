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

use Magento\Catalog\Pricing\Price\FinalPriceInterface;
use Magento\Framework\Pricing\Amount\AmountInterface;

/**
 * Final price model
 */
class FinalPrice extends \Magento\Catalog\Pricing\Price\FinalPrice implements FinalPriceInterface
{
    /**
     * @var AmountInterface
     */
    protected $maximalPrice;

    /**
     * @var AmountInterface
     */
    protected $minimalPrice;

    /**
     * Get max price, returns max price
     *
     * @return AmountInterface
     */
    public function getMaximalPrice()
    {
        if (!$this->maximalPrice) {
            $price = $this->product->getPriceModel()->getMaxAmount($this->product);
            $price = $this->priceCurrency->convertAndRound($price);
            $price = $price ? (float)$price : 0;
            $this->maximalPrice = $this->calculator->getAmount(
                $price,
                $this->product
            );
        }
        return $this->maximalPrice;
    }

    /**
     * Returns min price
     *
     * @return AmountInterface
     */
    public function getMinimalPrice()
    {
        if (!$this->minimalPrice) {
            $price = $this->product->getPriceModel()->getMinAmount($this->product);
            $price = $this->priceCurrency->convertAndRound($price);
            $price = $price ? (float)$price : 0;
            $this->minimalPrice = $this->calculator->getAmount(
                $price,
                $this->product
            );
        }
        return $this->minimalPrice;
    }
}
