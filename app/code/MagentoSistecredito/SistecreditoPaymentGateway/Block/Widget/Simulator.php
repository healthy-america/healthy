<?php

namespace MagentoSistecredito\SistecreditoPaymentGateway\Block\Widget;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;
use Magento\Widget\Block\BlockInterface;

/**
 * Sample widget
 * Class Simulator
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Simulator extends Template implements BlockInterface
{

    protected $_template = 'widget/simulator.phtml';

    private $objectManager;

    private $storeConfig;

    const simulatorJs = [
        "Development" => "https://stonprdeu2appsimulator.blob.core.windows.net/integraciones-dev/simulator/simulator.js",
        "Qa" => "https://stonprdeu2appsimulator.blob.core.windows.net/integraciones-qa/simulator/simulator.js",
        "Staging" => "https://stostgeu2appsimulator.blob.core.windows.net/integraciones/simulator/simulator.js",
        "Production" => "https://stoprdeu2appsimulator.blob.core.windows.net/integraciones/simulator/simulator.js",
    ];

    protected function _construct()
    {
        parent::_construct();
        $this->objectManager = ObjectManager::getInstance();
        $this->storeConfig = ScopeInterface::SCOPE_STORES;
        $this->setTemplate($this->_template);
    }

    public function getProductPrice()
    {
        return $this->objectManager->get("Magento\Framework\Registry")->registry("current_product")->getFinalPrice();
    }

    public function getActiveSimulator()
    {
        return $this->objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('payment/sistecredito_gateway/active_simulator', $this->storeConfig);
    }

    public function getSistecreditoStoreId()
    {
        return $this->objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('payment/sistecredito_gateway/store_id', $this->storeConfig);
    }

    public function getUrlSimulatorJs()
    {
        $environment = $this->objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface')->getValue('payment/sistecredito_gateway/environment', $this->storeConfig);
        return self::simulatorJs[$environment];
    }

}
