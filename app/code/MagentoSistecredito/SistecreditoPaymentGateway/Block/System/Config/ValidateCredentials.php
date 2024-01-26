<?php
namespace MagentoSistecredito\SistecreditoPaymentGateway\Block\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use MagentoSistecredito\SistecreditoPaymentGateway\Gateway\Config\Config;

class ValidateCredentials extends Field
{
    /**
     * @var Config
     */
    private $_gatewayConfig;

    /**
     * @var string
     */
    protected $_template = 'MagentoSistecredito_SistecreditoPaymentGateway::system/config/validateCredentials.phtml';

    /**
     * @param Context $context
     * @param array $data
     * @param Config $gatewayConfig
     */
    public function __construct(
        Context $context,
        Config $gatewayConfig,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_gatewayConfig = $gatewayConfig;
    }

    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    public function getCustomUrl()
    {
        return $this->getUrl('router/controller/action');
    }

    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock('Magento\Backend\Block\Widget\Button')->setData(['id' => 'btn_validation_keys',
            'label' => __('Validate Credentials')
            ]);
        return $button->toHtml();
    }
}
