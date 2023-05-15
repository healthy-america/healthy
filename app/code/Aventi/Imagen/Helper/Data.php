<?php


namespace Aventi\Imagen\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 *
 * @package Aventi\SAP\Helper
 */
class Data extends AbstractHelper
{
    /**
     * Definition of consts
     */
    const XML_PATH_IMAGE = 'imagen/options/path';



    public function getPathImage($store = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_IMAGE, ScopeInterface::SCOPE_STORE, $store);
    }


}
