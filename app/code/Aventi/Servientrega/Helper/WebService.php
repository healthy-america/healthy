<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\Servientrega\Helper;

use Exception;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use SimpleXMLElement as SimpleXMLElementAlias;

/**
 * @class WebService
 */
class WebService extends AbstractHelper
{
    /**
     * Constructor
     *
     * @param Context $context
     * @param Data $_data
     * @param Configuration $_configuration
     */
    public function __construct(
        Context                        $context,
        private readonly Data          $_data,
        private readonly Configuration $_configuration
    ) {
        parent::__construct($context);
    }

    /**
     * IsCashOnDelivery
     *
     * @return void
     */
    public function isCashOnDelivery(): void
    {
        $this->_data->isCashOnDelivery = true;
    }

    /**
     * CargueMasivoExterno
     *
     * @param $params
     * @return mixed
     * @throws Exception
     */
    public function CargueMasivoExterno($params): mixed
    {
        $body = [
            'envios' => [
                'CargueMasivoExternoDTO' => [
                    'objEnvios' => [
                        'EnviosExterno' => $params
                    ]
                ]
            ]
        ];

        return $this->_data->getResource(__FUNCTION__, $body);
    }

    /**
     * AnularGuias
     *
     * @param $data
     * @return false|SimpleXMLElementAlias|string
     * @throws Exception
     */
    public function AnularGuias($data): mixed
    {
        $params = [
            'num_Guia' => '2052660119', //Numero de guía.
            'num_GuiaFinal' => '2052660119' //Numero de guia
        ];

        return $this->_data->getResource(__FUNCTION__, $params);
    }

    /**
     * GenerarGuiaSticker
     * Sends a request to generate the Sticker Guide PDF.
     *
     * @param array $param
     * @return false|SimpleXMLElementAlias|string
     * @throws Exception
     */
    public function GenerarGuiaSticker(array $param): mixed
    {
        $body = [
            'num_Guia' => $param['numberGuide'],
            'num_GuiaFinal' => $param['numberGuide'],
            'sFormatoImpresionGuia' => $param['formatGuide'],
            'Id_ArchivoCargar' => '0',
            'interno' => false,
            'ide_CodFacturacion' => $this->_configuration->getBillingCode($this->_data->isCashOnDelivery)
        ];

        return $this->_data->getResource(__FUNCTION__, $body);
    }

    /**
     * GenerarGuiaStickerTiendasVirtuales
     *
     * @param array $params
     * @return mixed
     * @throws Exception
     */
    public function GenerarGuiaStickerTiendasVirtuales(array $params): mixed
    {
        $body = array_merge($params, [
            'ide_CodFacturacion' => $this->_billing_code
        ]);

        return $this->_data->getResource(__FUNCTION__, $body);
    }

    /**
     * ConsultarGuia
     *
     * @param $param
     * @return mixed
     * @throws Exception
     */
    public function ConsultarGuia($param): mixed
    {
        $body = [
            'NumeroGuia' => $param
        ];

        return $this->_data->getResource(__FUNCTION__, $body, true);
    }

    /**
     * EstadoGuiasXML
     *
     * @param $guides
     * @return mixed
     * @throws Exception
     */
    public function EstadoGuiasXML($guides): mixed
    {
        $body = [
            'ID_Cliente' => $this->_configuration->getClientID(),
            'RelacionGuias' => $guides
        ];

        return $this->_data->getResource(__FUNCTION__, $body, true);
    }
}
