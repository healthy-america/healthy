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

namespace Bss\GiftCard\Plugin\MultiShipping\Checkout;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Model\Quote\Address;

/**
 * Multishipping checkout overview information
 *
 * @api
 * @author     Magento Core Team <core@magentocommerce.com>
 * @since 100.0.2
 */
class Overview extends \Magento\Multishipping\Block\Checkout\Overview
{
    /**
     * Add Checkout Data to Checkout Overview page
     *
     * @return $this
     */
    protected function _toHtml()
    {
        return parent::_toHtml() .
            "<script>
                window.checkoutConfig = /* @noEscape */ {$this->getCheckoutData()->getSerializedCheckoutConfigs()};
            </script>";
    }
}
