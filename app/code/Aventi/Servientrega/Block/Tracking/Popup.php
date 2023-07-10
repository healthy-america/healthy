<?php

namespace Aventi\Servientrega\Block\Tracking;

use Magento\Framework\Stdlib\DateTime\DateTimeFormatterInterface;

class Popup extends \Magento\Shipping\Block\Tracking\Popup
{
    protected $_code = 'servientrega';

    /**
     * @var \Aventi\Servientrega\Helper\WebService
     */
    private $_webService;
    /**
     * @var \Magento\Shipping\Model\Shipping
     */
    protected $_shipping;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        DateTimeFormatterInterface $dateTimeFormatter,
        \Magento\Sales\Model\Order\Shipment $shipping,
        \Aventi\Servientrega\Helper\WebService $webService,
        array $data = []
    ) {
        parent::__construct($context, $registry, $dateTimeFormatter, $data);
        $this->_shipping = $shipping;
        $this->_webService = $webService;
    }

    /**
     * @inheridoc
     */
    public function getTrackingInfo(): array
    {
        $dataTracking = null;
        /* @var $info \Magento\Shipping\Model\Info */
        $info = $this->_registry->registry('current_shipping_info');
        $isCustom = $this->getIsCustomCarrier($info);
        if ($isCustom['response']) {
            $dataTracking = $this->_webService->ConsultarGuia($isCustom['trackNumber']);
            $this->_logger->debug(json_encode($dataTracking->ConsultarGuiaResult));
            return ['isCustomCarrier' => true, 'trackingInfo' => json_decode(json_encode($dataTracking->ConsultarGuiaResult), true)];
        } else {
            return ['isCustomCarrier' => false, 'trackingInfo' => $info->getTrackingInfo()];
        }
    }

    /**
     * Testing method.
     * @return string
     */
    public function getTest(): string
    {
        $this->_logger->debug("Estoy en el override block!");
        return "This is a proof!";
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        return $this->_code;
    }

    /**
     * @param \Magento\Shipping\Model\Info $info
     * @return false[]
     */
    private function getIsCustomCarrier(\Magento\Shipping\Model\Info $info): array
    {
        $incrementId = array_key_first($info->getTrackingInfo());
        $shipping = $this->_shipping->loadByIncrementId($incrementId);
        $shippingMethod = $shipping->getOrder()->getShippingMethod(true)->getData();
        if ($shippingMethod['carrier_code'] == $this->getCode()) {
            $trackNum = null;
            $tracks = $shipping->getTracksCollection()->getData();
            foreach ($tracks as $track) {
                $trackNum = $track['track_number'];
            }
            return ['response' => true, 'trackNumber' => $trackNum];
        }
        return ['response' => false];
    }

}
