<?php

namespace Aventi\Servientrega\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Data extends AbstractHelper
{
    /**
     * @var Configuration
     */
    protected $_configuration;

    public function __construct(
        Context $context,
        \Aventi\Servientrega\Helper\Configuration $configuration
    ) {
        parent::__construct($context);
        $this->_configuration = $configuration;
    }

    /**
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
     * @return array
     * @throws \Exception
     */
    private function paramsHeader(): array
    {

//        $pwd = $this->EncriptarContrasena(['strcontrasena' => $this->_configuration->getUserPassword()]);
        $pwd = $this->_configuration->getUserPassword();

        return [
            'login' => $this->_configuration->getUserName(),
//            'pwd' => $pwd->EncriptarContrasenaResult,
            'pwd' => $pwd,
            'Id_CodFacturacion' => $this->_configuration->getBillingCode(),
            'Nombre_Cargue' => '' //Nombre Cargue, preguntar
        ];
    }

    /**
     * @param $name_function
     * @param $params
     * @param false $tracking
     * @return \SimpleXMLElement|string
     * @throws \Exception
     */
    public function getResource($name_function, $params, $tracking = false)
    {
        $result = null;
        try {
            if (!$tracking) {
                $headerData = strpos($name_function, 'Contrasena') !== false ? '' : $this->paramsHeader();
                $client = new \SoapClient($this->_configuration->getUrlWebservice(), $this->optionsSoap());
                $client->__setLocation($this->_configuration->getUrlWebservice());
                $header = new \SoapHeader($this->_configuration->getNameSpacesGuide(), 'AuthHeader', $headerData);
                $client->__setSoapHeaders($header);
            } else {
                $client = new \SoapClient($this->_configuration->getUrlTracking(), $this->optionsSoap());
            }
            $result = $client->$name_function($params);
        } catch (\SoapFault $e) {
            $this->_logger->error($e->getMessage());
            return false;
        }
        return $result;
    }

    public function EncriptarContrasena($params)
    {
        return $this->getResource(__FUNCTION__, $params);
    }
    public function DesencriptarContrasena($params)
    {
        return $this->getResource(__FUNCTION__, $params);
    }
}
