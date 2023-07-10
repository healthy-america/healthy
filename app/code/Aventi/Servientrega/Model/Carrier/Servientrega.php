<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\Servientrega\Model\Carrier;

use Magento\Quote\Model\Quote\Address\RateRequest;

class Servientrega extends \Magento\Shipping\Model\Carrier\AbstractCarrier implements
    \Magento\Shipping\Model\Carrier\CarrierInterface
{
    protected $_code = 'servientrega';

    protected $_isFixed = true;

    protected $_rateResultFactory;

    protected $_rateMethodFactory;

    protected $_trackStatusFactory;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $_checkoutSession;
    /**
     * @var \Aventi\Servientrega\Helper\Configuration
     */
    private $_configuration;

    /**
     * Servientrega constructor.
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory
     * @param \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory
     * @param \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory
     * @param \Magento\Checkout\Model\Session $session
     * @param \Aventi\Servientrega\Helper\Configuration $configuration
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Model\Quote\Address\RateResult\ErrorFactory $rateErrorFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Shipping\Model\Rate\ResultFactory $rateResultFactory,
        \Magento\Quote\Model\Quote\Address\RateResult\MethodFactory $rateMethodFactory,
        \Magento\Shipping\Model\Tracking\Result\StatusFactory $trackStatusFactory,
        \Magento\Checkout\Model\Session $session,
        \Aventi\Servientrega\Helper\Configuration $configuration,
        array $data = []
    ) {
        $this->_rateResultFactory = $rateResultFactory;
        $this->_rateMethodFactory = $rateMethodFactory;
        $this->_trackStatusFactory = $trackStatusFactory;
        $this->_checkoutSession = $session;
        $this->_configuration = $configuration;
        parent::__construct($scopeConfig, $rateErrorFactory, $logger, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function collectRates(RateRequest $request)
    {
        $region = $request->getDestRegionId();
        if (!$this->getConfigFlag('active') || !$this->checkRegion($region)) {
            return false;
        }

        if ($this->checkFreeShipping()) {
            $request->setFreeShipping(true);
        }

        $shippingPrice = $this->getConfigData('price');

        $result = $this->_rateResultFactory->create();

        if ($shippingPrice !== false) {
            $method = $this->_rateMethodFactory->create();

            $method->setCarrier($this->_code);
            $method->setCarrierTitle($this->getConfigData('title'));

            $method->setMethod($this->_code);
            $method->setMethodTitle($this->getConfigData('name'));

            if ($request->getFreeShipping() === true || $request->getPackageQty() == $this->getFreeBoxes()) {
                $shippingPrice = '0.00';
            }

            $method->setPrice($shippingPrice);
            $method->setCost($shippingPrice);

            $result->append($method);
        }

        return $result;
    }

    /**
     * getAllowedMethods
     *
     * @return array
     */
    public function getAllowedMethods(): array
    {
        return [$this->_code => $this->getConfigData('name')];
    }

    /**
     * @return bool
     */
    public function isTrackingAvailable(): bool
    {
        return true;
    }

    /**
     * Checks if the quote total amount is valid for free shipping.
     *
     * @return bool Returns <b>TRUE</b> if it meets, <b>FALSE</b> otherwise.
     */
    private function checkFreeShipping(): bool
    {
        $isFree = $this->_configuration->isFreeShipping();
        if ($isFree) {
            $freeAmount = $this->_configuration->getFreeAmount();
            try {
                $cartAmount = $this->_checkoutSession->getQuote()->getGrandTotal();
                if ($cartAmount > $freeAmount) {
                    return true;
                }
            } catch (\Exception $e) {
                $this->_logger->error($e->getMessage());
            }
        }
        return false;
    }

    /**
     * Checks the allowed regions to ship.
     * @param $region
     * @return bool
     */
    private function checkRegion($region): bool
    {
        $regions = explode(',', $this->_configuration->getAllowRegions());
        if (in_array($region, $regions)) {
            return true;
        }
        return false;
    }
}
