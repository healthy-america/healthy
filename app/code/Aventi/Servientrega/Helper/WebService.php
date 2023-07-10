<?php

namespace Aventi\Servientrega\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use SimpleXMLElement as SimpleXMLElementAlias;

class WebService extends AbstractHelper
{
    /**
     * @var Data
     */
    private $_data;
    /**
     * @var Configuration
     */
    private $_configuration;

    /**
     * WebService constructor.
     * @param Context $context
     * @param Data $data
     * @param Configuration $configuration
     */
    public function __construct(
        Context $context,
        \Aventi\Servientrega\Helper\Data $data,
        \Aventi\Servientrega\Helper\Configuration $configuration
    ) {
        $this->_data = $data;
        $this->_configuration = $configuration;
        parent::__construct($context);
    }

    /**
     * @param array $params
     * @return false|SimpleXMLElementAlias|string
     * @throws \Exception
     */
    public function CargueMasivoExterno($params)
    {
        $response = null;
        $body = [
            'envios' => [
                'CargueMasivoExternoDTO' => [
                    'objEnvios' => [
                        'EnviosExterno' => $params
                    ]
                ]
            ]
        ];

        try {
            $response = $this->_data->getResource(__FUNCTION__, $body);
        } catch (\SoapFault $e) {
            $this->_logger->error($e->getMessage());
            $response = false;
        }
        return $response;
    }

    /**
     * @param $data
     * @return SimpleXMLElementAlias|string|null
     * @throws \Exception
     */
    public function AnularGuias($data)
    {
        $params = [
            'num_Guia' => '2052660119', //Numero de guía.
            'num_GuiaFinal' => '2052660119' //Numero de guia
        ];
        return $this->_data->getResource(__FUNCTION__, $params);
    }

    /**
     * Sends a request to generate the Sticker Guide PDF.
     * @param array $param
     * @return SimpleXMLElementAlias|string|null
     * @throws \Exception
     */
    public function GenerarGuiaSticker(array $param)
    {
        // '292825253'
        $body = [
            'num_Guia' => $param['numberGuide'],
            'num_GuiaFinal' => $param['numberGuide'],
            'sFormatoImpresionGuia' => $param['formatGuide'],
            'Id_ArchivoCargar' => '0',
            'interno' => false,
            'ide_CodFacturacion' => $this->_configuration->getBillingCode()
        ];

        return $this->_data->getResource(__FUNCTION__, $body);
    }

    public function GenerarGuiaStickerTiendasVirtuales(array $params)
    {
        $body = array_merge($params, [
            'ide_CodFacturacion' => $this->_billing_code
        ]);

        return $this->_data->getResource(__FUNCTION__, $body);
    }

    /**
     * @param $param
     * @return SimpleXMLElementAlias|string|null
     * @throws \Exception
     */
    public function ConsultarGuia($param)
    {
        $body = [
            'NumeroGuia' => $param
        ];
        return $this->_data->getResource(__FUNCTION__, $body, true);
    }

    /**
     * @param $guides
     * @return SimpleXMLElementAlias|string|null
     * @throws \Exception
     */
    public function EstadoGuiasXML($guides)
    {
        $body = [
            'ID_Cliente' => $this->_configuration->getClientID(),
            'RelacionGuias' => $guides
        ];
        return $this->_data->getResource(__FUNCTION__, $body, true);
    }

}
