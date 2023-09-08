<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAPHistoryRequest\Helper;

class Config
{
    const XML_PATH_ORDER_SAP_HISTORY = 'integration/history/order';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    private \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig;

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Is active order sap history request
     *
     * @return bool
     */
    public function isActiveOrderHistory(): bool
    {
        return (bool)$this->getConfig(self::XML_PATH_ORDER_SAP_HISTORY);
    }

    /**
     * @param string $path
     * @param string $store
     * @return mixed
     */
    public function getConfig(string $path, string $store = \Magento\Store\Model\ScopeInterface::SCOPE_STORE): mixed
    {
        return $this->scopeConfig->getValue($path, $store);
    }
}
