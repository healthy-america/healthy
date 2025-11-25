<?php
namespace Healthy\FreeShippingBlock\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class FreeShipping extends Template
{
    /** @var ScopeConfigInterface */
    protected $scopeConfig;

    public function __construct(
        Template\Context $context,
        array $data = []
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        parent::__construct($context, $data);
    }

    /**
     * Retrieve free shipping threshold from store config
     *
     * @return float
     */
    public function getFreeShippingThreshold()
    {
        $value = $this->scopeConfig->getValue('healthy_freeshipping/settings/price', ScopeInterface::SCOPE_STORE);
        if ($value === null || $value === '') {
            return 200000; // default
        }
        return (float)preg_replace('/[^0-9.]/', '', $value);
    }

    /**
     * Retrieve messages as array
     *
     * @return array
     */
    public function getMessages()
    {
        $messageDefault = $this->scopeConfig->getValue('healthy_freeshipping/settings/message_default', ScopeInterface::SCOPE_STORE);
        $messageItemsInCart = $this->scopeConfig->getValue('healthy_freeshipping/settings/message_items_in_cart', ScopeInterface::SCOPE_STORE);
        $messageFreeShipping = $this->scopeConfig->getValue('healthy_freeshipping/settings/message_free_shipping', ScopeInterface::SCOPE_STORE);

        return [
            'messageDefault' => $messageDefault ?: 'Envio <strong>GRATIS</strong> en compras superiores a <strong>$x.xxx</strong>',
            'messageItemsInCart' => $messageItemsInCart ?: 'Estas a <strong>$x.xxx</strong> de tener envio <strong>GRATIS</strong>',
            'messageFreeShipping' => $messageFreeShipping ?: 'Tu pedido ahora tiene envio <strong>GRATIS</strong>'
        ];
    }

    /**
     * Return JSON encoded config for data-mage-init (wrapped with element selector)
     *
     * @return string
     */
    public function getInitConfigJson()
    {
        $messages = $this->getMessages();
        $inner = [
            'Magento_Ui/js/core/app' => [
                'components' => [
                    'free-shipping-banner' => [
                        'component' => 'Healthy_FreeShippingBlock/js/free-shipping-banner',
                        'freeShippingThreshold' => $this->getFreeShippingThreshold(),
                        'config' => $messages
                    ]
                ]
            ]
        ];

        $wrapper = [
            '#free-shipping-banner' => $inner
        ];

        return json_encode($wrapper, JSON_UNESCAPED_UNICODE);
    }
}
