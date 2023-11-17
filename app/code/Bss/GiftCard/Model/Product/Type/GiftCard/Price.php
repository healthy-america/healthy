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

namespace Bss\GiftCard\Model\Product\Type\GiftCard;

use Bss\GiftCard\Helper\Catalog\Product\Configuration;
use Bss\GiftCard\Model\AmountsFactory;
use Bss\GiftCard\Model\Product\Type\GiftCard as GiftCardType;
use Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory;
use Magento\Catalog\Model\Product\Type\Price as CatalogPrice;
use Magento\Catalog\Model\ResourceModel\Product;
use Magento\CatalogRule\Model\ResourceModel\RuleFactory;
use Magento\Customer\Api\GroupManagementInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class price
 *
 * Bss\GiftCard\Model\Product\Type\GiftCard
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Price extends CatalogPrice
{
    /**
     * @var AmountsFactory
     */
    private $amountsFactory;

    /**
     * Constructor
     *
     * @param RuleFactory $ruleFactory
     * @param StoreManagerInterface $storeManager
     * @param TimezoneInterface $localeDate
     * @param Session $customerSession
     * @param ManagerInterface $eventManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param GroupManagementInterface $groupManagement
     * @param ProductTierPriceInterfaceFactory $tierPriceFactory
     * @param ScopeConfigInterface $config
     * @param AmountsFactory $amountsFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        RuleFactory $ruleFactory,
        StoreManagerInterface $storeManager,
        TimezoneInterface $localeDate,
        Session $customerSession,
        ManagerInterface $eventManager,
        PriceCurrencyInterface $priceCurrency,
        GroupManagementInterface $groupManagement,
        ProductTierPriceInterfaceFactory $tierPriceFactory,
        ScopeConfigInterface $config,
        AmountsFactory $amountsFactory
    ) {
        parent::__construct(
            $ruleFactory,
            $storeManager,
            $localeDate,
            $customerSession,
            $eventManager,
            $priceCurrency,
            $groupManagement,
            $tierPriceFactory,
            $config
        );
        $this->amountsFactory = $amountsFactory;
    }

    /**
     * Get product final price
     *
     * @param   float $qty
     * @param   \Magento\Catalog\Model\Product $product
     * @return  float
     */
    public function getFinalPrice($qty, $product)
    {
        if ($qty === null && $product->getCalculatedFinalPrice() !== null) {
            return $product->getCalculatedFinalPrice();
        }

        $finalPrice = $this->getBasePrice($product, $qty);
        $product->setFinalPrice($finalPrice);
        $this->_eventManager->dispatch('catalog_product_get_final_price', ['product' => $product, 'qty' => $qty]);
        $finalPrice = $product->getData('final_price');

        $finalPrice = $this->_applyOptionsPrice($product, $qty, $finalPrice);
        if ($product->hasCustomOptions()) {
            $customOption = $product->getCustomOption(Configuration::GIFTCARD_AMOUNT);
            if ($customOption) {
                $amount = $customOption->getValue();
                $this->convertPrice($amount, $product);
                if ($amount) {
                    $finalPrice += $amount;
                }
            }
        }

        $finalPrice = max(0, $finalPrice);
        $product->setFinalPrice($finalPrice);
        return $finalPrice;
    }

    /**
     * Convert price
     *
     * @param   float $amount
     * @param   \Magento\Catalog\Model\Product $product
     * @return  void
     */
    private function convertPrice(&$amount, $product)
    {
        $productId = $product->getId();
        if ($amount == 'custom') {
            $price = $product->getCustomOption(Configuration::GIFTCARD_AMOUNT_DYNAMIC)->getValue();
            $product->getResource()->load($product, $product->getId());
            $percentage = (float)$product->getBssGiftCardPercentageValue();
            $amount = $price * $percentage / 100;
        } else {
            $amountModel = $this->amountsFactory->create()->load($amount);
            if ($amountModel && $productId == $amountModel->getProductId()) {
                $amount = $amountModel->getPrice();
            }
        }
    }

    /**
     * Return minimal amount for Giftcard product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return float|void
     */
    public function getMinAmount($product)
    {
        $amounts = $this->calcAmount($product);
        if (!empty($amounts)) {
            return $amounts['min'];
        }
    }

    /**
     * Return maximal amount for Giftcard product
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return float|void
     */
    public function getMaxAmount($product)
    {
        $amounts = $this->calcAmount($product);
        if (!empty($amounts)) {
            return $amounts['max'];
        }
    }

    /**
     * Calc amount
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return array
     */
    private function calcAmount($product)
    {
        $amounts = [];
        $result = [];
        $product->getResource()->load($product, $product->getId());
        $percentage = $product->getBssGiftCardPercentageType();
        $percentageValue = $percentage == 1 ? (float)$product->getBssGiftCardPercentageValue() : -1;
        if ($product->getData(GiftCardType::BSS_GIFT_CARD_DYNAMIC_PRICE)) {
            if ($product->getData(GiftCardType::BSS_GIFT_CARD_OPEN_MIN_AMOUNT)) {
                $minAmount = $product->getData(GiftCardType::BSS_GIFT_CARD_OPEN_MIN_AMOUNT);
                $minAmount = $percentageValue > -1 ? $minAmount * $percentageValue / 100 : $minAmount;
                $amounts[] = $this->priceCurrency->round($minAmount);
            }

            if ($product->getData(GiftCardType::BSS_GIFT_CARD_OPEN_MAX_AMOUNT)) {
                $maxAmount = $product->getData(GiftCardType::BSS_GIFT_CARD_OPEN_MAX_AMOUNT);
                $maxAmount = $percentageValue > -1 ? $maxAmount * $percentageValue / 100 : $maxAmount;
                $amounts[] = $this->priceCurrency->round($maxAmount);
            }
        }

        $this->getAmountsData($amounts, $product);
        if (!empty($amounts)) {
            $result = [
                'min' => min($amounts),
                'max' => max($amounts)
            ];
        }
        return $result;
    }

    /**
     * Get amounts data
     *
     * @param array $amounts
     * @param \Magento\Catalog\Model\Product $product
     */
    private function getAmountsData(&$amounts, $product)
    {
        $amountsData = $product->getData(GiftCardType::BSS_GIFT_CARD_AMOUNTS);
        if (!empty($amountsData)) {
            foreach ($amountsData as $amount) {
                $value = $this->priceCurrency->round($amount['price']);
                $amounts[] = $value;
            }
        }
    }
}
