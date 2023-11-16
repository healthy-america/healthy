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

namespace Bss\GiftCard\Model\Config\Source;

use Magento\Directory\Helper\Data;
use Magento\Framework\Option\ArrayInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Class website
 *
 * Bss\GiftCard\Model\Config\Source
 */
class Website implements ArrayInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Directory\Helper\Data
     */
    private $directoryHelper;

    /**
     * @param Data $directoryHelper
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Data $directoryHelper,
        StoreManagerInterface $storeManager
    ) {
        $this->directoryHelper = $directoryHelper;
        $this->storeManager = $storeManager;
    }

    /**
     * To option array
     *
     * @return array
     */
    public function toOptionArray()
    {
        $websites = [
            [
                'label' => __('All Websites') . ' [' . $this->directoryHelper->getBaseCurrencyCode() . ']',
                'value' => 0,
            ]
        ];
        $websitesList = $this->storeManager->getWebsites();

        foreach ($websitesList as $website) {
            $websites[] = [
                'label' => $website->getName() . '[' . $website->getBaseCurrencyCode() . ']',
                'value' => $website->getId(),
            ];
        }

        return $websites;
    }
}
