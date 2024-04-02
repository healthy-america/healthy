<?php

namespace Aventi\Servientrega\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use SoapClient;
use SoapFault;
use SoapHeader;

/**
 * @class Data
 */
class Data extends AbstractHelper
{

    /**
     * @var bool
     */
    public bool $isCashOnDelivery = false;

    /**
     * Constructor
     *
     * @param Context $context
     * @param Configuration $_configuration
     */
    public function __construct(
        Context $context,
        protected Configuration $_configuration
    ) {
        parent::__construct($context);
    }

    /**
     * OptionsSoap
     *
     * @return array
     */
    public function optionsSoap(): array
    {
        return [
            "trace" => true,
            'exceptions' => false,
            "soap_version"  => SOAP_1_2,
            "connection_timeout"=> 60,
            "encoding"=> "utf-8",
            'stream_context' => stream_context_create([
                'ssl' => [
                    'verify_peer' => false,
                    'verify_peer_name' => false,
                    'allow_self_signed' => true,
                    'ciphers'=>'AES256-SHA'
                ]
            ]),
            'cache_wsdl' => WSDL_CACHE_NONE
        ];
    }

    /**
     * ParamsHeader
     *
     * @return array
     */
    private function paramsHeader(): array
    {
        //$pwd = $this->EncriptarContrasena(['strcontrasena' => $this->_configuration->getUserPassword()]);
        $pwd = $this->_configuration->getUserPassword();

        return [
            'login' => $this->_configuration->getUserName(),
            //'pwd' => $pwd->EncriptarContrasenaResult,
            'pwd' => $pwd,
            'Id_CodFacturacion' => $this->_configuration->getBillingCode($this->isCashOnDelivery),
            'Nombre_Cargue' => ''
        ];
    }

    /**
     * @param $name_function
     * @param $params
     * @param bool $tracking
     * @return mixed
     */
    public function getResource($name_function, $params, bool $tracking = false): mixed
    {
        try {
            if (!$tracking) {
                $headerData = str_contains($name_function, 'Contrasena') ? '' : $this->paramsHeader();
                $client = new SoapClient($this->_configuration->getUrlWebservice(), $this->optionsSoap());
                $client->__setLocation($this->_configuration->getUrlWebservice());
                $header = new SoapHeader($this->_configuration->getNameSpacesGuide(), 'AuthHeader', $headerData);
                $client->__setSoapHeaders($header);
            } else {
                $client = new SoapClient($this->_configuration->getUrlTracking(), $this->optionsSoap());
            }
            $result = $client->$name_function($params);
        } catch (SoapFault $e) {
            $this->_logger->error($e->getMessage());
            $result = false;
        }

        return $result;
    }

    /**
     * EncriptarContrasena
     *
     * @param $params
     * @return mixed
     */
    public function EncriptarContrasena($params): mixed
    {
        return $this->getResource(__FUNCTION__, $params);
    }

    /**
     * DesencriptarContrasena
     *
     * @param $params
     * @return mixed
     */
    public function DesencriptarContrasena($params): mixed
    {
        return $this->getResource(__FUNCTION__, $params);
    }
}
