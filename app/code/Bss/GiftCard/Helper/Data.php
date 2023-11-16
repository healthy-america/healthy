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

namespace Bss\GiftCard\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Pricing\Helper\Data as PricingData;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\ScopeInterface;

/**
 * Class data
 *
 * Bss\GiftCard\Helper
 */
class Data extends AbstractHelper
{
    public const ENABLE = 'giftcard/general/active';

    public const CONFIG_EMAIL = 'giftcard/email/';

    public const CONFIG_SETTING = 'giftcard/setting/';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    public $scopeConfig;

    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * @var PricingData
     */
    private $priceHelper;

    /**
     * Data constructor.
     * @param TimezoneInterface $localeDate
     * @param PricingData $priceHelper
     * @param Context $context
     */
    public function __construct(
        TimezoneInterface $localeDate,
        PricingData $priceHelper,
        Context $context
    ) {
        parent::__construct($context);
        $this->localeDate = $localeDate;
        $this->priceHelper = $priceHelper;
        $this->scopeConfig = $context->getScopeConfig();
    }

    /**
     * Convert update time
     *
     * @param string $time
     * @return string|void
     */
    public function formatDateTime($time)
    {
        if ($time) {
            return $this->localeDate->formatDateTime(
                $time,
                \IntlDateFormatter::MEDIUM,
                \IntlDateFormatter::MEDIUM
            );
        }
    }

    /**
     * Convert price with currency
     *
     * @param   float $price
     * @return  string
     */
    public function convertPrice($price)
    {
        return $this->priceHelper->currency($price, true, false);
    }

    /**
     * Is module enabled
     *
     * @param null|int $storeId
     * @return bool
     */
    public function isEnabled($storeId = null)
    {
        return $this->scopeConfig->isSetFlag(
            self::ENABLE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get config email
     *
     * @param string $field
     * @param null|int $storeId
     * @return mixed
     */
    public function getConfigEmail($field, $storeId = null)
    {
        if (!$this->isEnabled($storeId)) {
            return false;
        }
        return $this->scopeConfig->getValue(
            self::CONFIG_EMAIL . $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Get config setting
     *
     * @param string $field
     * @param null|int $storeId
     * @return mixed
     */
    public function getConfigSetting($field, $storeId = null)
    {
        if (!$this->isEnabled($storeId)) {
            return false;
        }
        return $this->scopeConfig->getValue(
            self::CONFIG_SETTING . $field,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Hide code
     *
     * @param string $code
     * @return string
     */
    public function hideCode($code)
    {
        if ($numberChar = $this->getConfigSetting('number_character')) {
            $replaceChar = $this->getConfigSetting('replace_character');
            if (!$replaceChar) {
                $replaceChar = 'XXX';
            }
            $code = substr_replace($code, $replaceChar, $numberChar);
        }
        return $code;
    }
}
