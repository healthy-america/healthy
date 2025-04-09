<?php
namespace MagentoSistecredito\SistecreditoPaymentGateway\Block;

use Magento\Framework\Phrase;
use Magento\Payment\Block\ConfigurableInfo;
use Magento\Framework\View\Asset\Repository as AssetRepository;
use Magento\Framework\View\Element\Template\Context;
use Magento\Payment\Gateway\ConfigInterface;
use Magento\Csp\Helper\CspNonceProvider;

class Info extends ConfigurableInfo
{

    /**
     *
     * @var AssetRepository
     */
    protected $_assetRepository;

    /**
     * @var CspNonceProvider
     */
    private $cspNonceProvider;

    public function __construct(
        AssetRepository $assetRepository,
        Context $context,
        ConfigInterface $config,
        CspNonceProvider $cspNonceProvider,
        array $data = []
    ) {
        parent::__construct($context, $config, $data);
        $this->_assetRepository = $assetRepository;
        $this->cspNonceProvider = $cspNonceProvider;
    }

    public function getSistecreditoConfig()
    {
        $output['logoImageUrl'] = $this->_getViewFileUrl('MagentoSistecredito_SistecreditoPaymentGateway::images/sistecredito.png');

        return $output;
    }

    /**
     * Returns label
     *
     * @param string $field
     * @return Phrase
     */
    protected function getLabel($field)
    {
        return __($field);
    }

    /**
     * Returns value view
     *
     * @param string $field
     * @param string|array $value
     * @return string | Phrase
     */
    protected function getValueView($field, $value)
    {
        return parent::getValueView($field, $value);
    }

    private function _getViewFileUrl($fileId, array $params = [])
    {
        return $this->_assetRepository->getUrlWithParams($fileId, $params);
    }

    /**
     * Get CSP Nonce
     *
     * @return String
     */
    public function getNonce(): string
    {
        return $this->cspNonceProvider->generateNonce();
    }
}
