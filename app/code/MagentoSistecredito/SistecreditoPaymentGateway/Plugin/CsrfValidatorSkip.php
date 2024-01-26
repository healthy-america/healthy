<?php

namespace MagentoSistecredito\SistecreditoPaymentGateway\Plugin;

use Psr\Log\LoggerInterface;

class CsrfValidatorSkip
{

    /**
     * 
     * @var LoggerInterface
     */
    private $_logger;

    public function __construct(
        LoggerInterface $logger
    ) {
        $this->_logger = $logger;
    }

    /**
     * @param \Magento\Framework\App\Request\CsrfValidator $subject
     * @param \Closure $proceed
     * @param \Magento\Framework\App\Request\Http $request
     * @param \Magento\Framework\App\ActionInterface $action
     */
    public function aroundValidate(
        $subject,
        \Closure $proceed,
        $request,
        $action
    ) {
        if ($request->getControllerModule() == 'MagentoSistecredito_SistecreditoPaymentGateway') {
            $this->_logger->debug("Skipping CSRF Validation for Sistecredito POST Confirmation Request");
            return; // Skip CSRF check
        }
        $proceed($request, $action); // Proceed Magento 2 core functionalities
    }
}
