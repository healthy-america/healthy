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

namespace Bss\GiftCard\Plugin\MultiShipping\DataProviders;

use Magento\Framework\Session\SessionManagerInterface;
use Magento\Checkout\Model\CompositeConfigProvider;
use Magento\Framework\Serialize\Serializer\Json as Serializer;

/**
 * Class overview
 * Bss\GiftCard\Plugin\MultiShipping\DataProviders
 * @SuppressWarnings(PHPMD.CookieAndSessionMisuse)
 */
class Overview extends \Magento\Multishipping\Block\DataProviders\Overview
{
    /**
     * @var CompositeConfigProvider
     */
    private $configProvider;

    /**
     * @var Serializer
     */
    private $serializer;

    /**
     * Overview constructor.
     * @param SessionManagerInterface $session
     * @param CompositeConfigProvider $configProvider
     * @param Serializer $serializer
     */
    public function __construct(
        SessionManagerInterface $session,
        CompositeConfigProvider $configProvider,
        Serializer $serializer
    ) {
        parent::__construct($session);
        $this->configProvider = $configProvider;
        $this->serializer = $serializer;
    }

    /**
     * Returns serialized checkout config.
     *
     * @return string
     * @throws \InvalidArgumentException
     */
    public function getSerializedCheckoutConfigs(): string
    {
        return $this->serializer->serialize($this->configProvider->getConfig());
    }
}
