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
 * @category  BSS
 * @package   Bss_GiftCard
 * @author    Extension Team
 * @copyright Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license   http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\GiftCard\Pricing\Render;

use Bss\GiftCard\Pricing\Price\FinalPrice;
use Magento\Catalog\Model\Product\Pricing\Renderer\SalableResolverInterface;
use Magento\Catalog\Pricing\Render as CatalogRender;
use Magento\Framework\Pricing\Price\PriceInterface;
use Magento\Framework\Pricing\Render\RendererPool;
use Magento\Framework\Pricing\SaleableInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class for final_price rendering
 */
class FinalPriceBox extends CatalogRender\FinalPriceBox
{
    /**
     * @var Registry
     */
    private $registry;

    /**
     * FinalPriceBox constructor.
     *
     * @param Context                       $context
     * @param SaleableInterface             $saleableItem
     * @param PriceInterface                $price
     * @param RendererPool                  $rendererPool
     * @param Registry                      $registry
     * @param array                         $data
     * @param SalableResolverInterface|null $salableResolver
     */
    public function __construct(
        Context                  $context,
        SaleableInterface        $saleableItem,
        PriceInterface           $price,
        RendererPool             $rendererPool,
        Registry                 $registry,
        array                    $data = [],
        SalableResolverInterface $salableResolver = null
    ) {
        parent::__construct(
            $context,
            $saleableItem,
            $price,
            $rendererPool,
            $data,
            $salableResolver
        );
        $this->registry = $registry;
    }

    /**
     * Check bundle product has one ,more, customs options with different prices
     *
     * @return bool
     */
    public function showRangePrice()
    {
        $product = $this->registry->registry('product');
        if ($product) {
            return false;
        }
        $giftCardPrice = $this->getPriceType(
            FinalPrice::PRICE_CODE
        );
        return $giftCardPrice->getMinimalPrice()
            != $giftCardPrice->getMaximalPrice();
    }
}
