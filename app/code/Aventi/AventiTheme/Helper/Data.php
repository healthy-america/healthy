<?php
namespace Aventi\AventiTheme\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const XML_PATH_MY_VARIABLE = 'themecolors/colors/theme_colors';

    public function getMyVariable($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_MY_VARIABLE,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}
