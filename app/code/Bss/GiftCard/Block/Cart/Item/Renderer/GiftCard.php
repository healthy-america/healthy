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

namespace Bss\GiftCard\Block\Cart\Item\Renderer;

use Magento\Framework\View\Element\Template\Context;
use Magento\Catalog\Helper\Product\Configuration as ConfigurationCatalog;
use Magento\Checkout\Model\Session;
use Magento\Catalog\Block\Product\ImageBuilder;
use Magento\Framework\Url\Helper\Data;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Module\Manager;
use Magento\Framework\View\Element\Message\InterpretationStrategyInterface;
use Magento\Checkout\Block\Cart\Item\Renderer as ItemRenderer;
use Bss\GiftCard\Helper\Catalog\Product\Configuration as ConfigurationGiftCard;

/**
 * Class gift card
 *
 * Bss\GiftCard\Block\Cart\Item\Renderer
 */
class GiftCard extends ItemRenderer
{
    /**
     * @var ConfigurationGiftCard
     */
    private $configurationGiftCard;

    /**
     * @param Context $context
     * @param ConfigurationCatalog $productConfig
     * @param Session $checkoutSession
     * @param ImageBuilder $imageBuilder
     * @param Data $urlHelper
     * @param ManagerInterface $messageManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param Manager $moduleManager
     * @param InterpretationStrategyInterface $messageInterpretationStrategy
     * @param ConfigurationGiftCard $configurationGiftCard
     * @param array $data
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @codeCoverageIgnore
     */
    public function __construct(
        Context $context,
        ConfigurationCatalog $productConfig,
        Session $checkoutSession,
        ImageBuilder $imageBuilder,
        Data $urlHelper,
        ManagerInterface $messageManager,
        PriceCurrencyInterface $priceCurrency,
        Manager $moduleManager,
        InterpretationStrategyInterface $messageInterpretationStrategy,
        ConfigurationGiftCard $configurationGiftCard,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $productConfig,
            $checkoutSession,
            $imageBuilder,
            $urlHelper,
            $messageManager,
            $priceCurrency,
            $moduleManager,
            $messageInterpretationStrategy,
            $data
        );
        $this->configurationGiftCard = $configurationGiftCard;
    }

    /**
     * Get list of all options for product
     *
     * @return array
     */
    public function getOptionList()
    {
        $item = $this->getItem();
        return $this->configurationGiftCard->getOptions($item);
    }
}
