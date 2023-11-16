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

namespace Bss\GiftCard\Model\Attribute\Source\Backend;

use Bss\GiftCard\Model\Product\Type\GiftCard as GiftCardType;
use Bss\GiftCard\Model\ResourceModel\Attribute\Backend\GiftCard\Amounts as AmountsResourceModel;
use Magento\Catalog\Helper\Data;
use Magento\Catalog\Model\Product\Attribute\Backend\Price;
use Magento\Directory\Model\CurrencyFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Locale\FormatInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class amounts
 *
 * Bss\GiftCard\Model\Product\Attribute\Backend
 */
class Amounts extends Price
{
    /**
     * @var AmountsResourceModel
     */
    private $amountsResourceModel;

    /**
     * @var LoggerInterface
     */
    public $logger;

    /**
     * Initialize dependencies.
     *
     * @param CurrencyFactory $currencyFactory
     * @param StoreManagerInterface $storeManager
     * @param Data $catalogData
     * @param ScopeConfigInterface $config
     * @param FormatInterface $localeFormat
     * @param AmountsResourceModel $amountsResourceModel
     * @param LoggerInterface $logger
     */
    public function __construct(
        CurrencyFactory       $currencyFactory,
        StoreManagerInterface $storeManager,
        Data                  $catalogData,
        ScopeConfigInterface  $config,
        FormatInterface       $localeFormat,
        AmountsResourceModel  $amountsResourceModel,
        LoggerInterface       $logger
    ) {
        $this->amountsResourceModel = $amountsResourceModel;
        $this->logger = $logger;
        parent::__construct(
            $currencyFactory,
            $storeManager,
            $catalogData,
            $config,
            $localeFormat
        );
    }

    /**
     * Validate
     *
     * @param \Magento\Catalog\Model\Product $object
     * @return bool
     * @throws LocalizedException
     */
    public function validate($object)
    {
        $amounts = $object->getData($this->getAttribute()->getName());
        $dynamicPrice = $object->getData(GiftCardType::BSS_GIFT_CARD_DYNAMIC_PRICE);
        if (empty($amounts)) {
            if ($dynamicPrice) {
                return true;
            }
            return parent::validate($object);
        }

        $dup = [];
        foreach ($amounts as $amount) {
            $key1 = implode(
                '-',
                [
                    $amount['website_id'],
                    (float)$amount['value'],
                    (float)$amount['price']
                ]
            );
            if (!empty($dup[$key1])) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('We found a duplicate website, price and value.')
                );
            }
            $dup[$key1] = 1;
        }
        return true;
    }

    /**
     * Load
     *
     * @param \Magento\Catalog\Model\Product $object
     * @return $this|Amounts
     */
    public function afterLoad($object)
    {
        $data = [];
        try {
            $productId = (int)$object->getId();
            $data = $this->amountsResourceModel->loadAmountsData($productId);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
        $object->setData($this->getAttribute()->getName(), $data);
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function afterSave($object)
    {
        $current = $object->getData($this->getAttribute()->getName());
        if (!empty($current)) {
            foreach ($current as &$item) {
                if (isset($item['price'])) {
                    $item['price'] = str_replace(' ', '', trim($item['price']));
                }
            }
            usort($current, function ($a, $b) {
                return (float)$a['price'] - (float)$b['price'];
            });
        }
        try {
            $this->amountsResourceModel->saveAmountsData($object, $current);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function afterDelete($object)
    {
        try {
            $productId = (int)$object->getId();
            $this->amountsResourceModel->deleteAmountsData($productId);
        } catch (\Exception $e) {
            $this->logger->critical($e);
        }
        return $this;
    }
}
