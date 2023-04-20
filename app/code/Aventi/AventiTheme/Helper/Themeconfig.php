<?php

namespace Aventi\AventiTheme\Helper;

class Themeconfig extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var string
     */
    protected $cssFolder;

    /**
     * @var string
     */
    protected $cssPath;

    /**
     * @var string
     */
    protected $cssDir;

    /**
     * @var
     */
    protected $_themeoptionversion;

    /**
     * @var \Magento\Framework\App\State
     */
    protected $_appState;

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;

    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\App\State $appState,
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->_request = $request;
        $this->_appState = $appState;
        $this->_storeManager = $storeManager;
        $base = BP;
        $this->cssFolder = 'aventi/theme_option/';
        $this->cssPath = 'pub/media/' . $this->cssFolder;
        $this->cssDir = $base . '/' . $this->cssPath;
        parent::__construct($context);
    }

    public function getConfiguration($path, $storeId = null)
    {
        return $this->scopeConfig->getValue($path, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $storeId);
    }

    public function getCustomMenuColorCode($data, $color)
    {
        if ($this->issetDataCustom($data, 'magemenu', $color) != '' ) {
            return $this->getCodeColor($this->issetDataCustom($data, 'magemenu', $color));
        }
        return '';
    }

    public function getMainContentContainerBackgroundColor($data){
        if($this->issetDataCustom($data, 'main', 'main_bgcolor') != '' && $this->isEnableMainContentContainer($data)){
            return $this->formatBgColor($this->issetDataCustom($data, 'main', 'main_bgcolor'));
        }
        return '';
    }

    public function getCustomFooterTitleColor($data)
    {
        if ($this->issetDataCustom($data, 'footer', 'footer_title_color') != '') {
            return $this->getCodeColor($this->issetDataCustom($data, 'footer', 'footer_title_color'));
        }
        return '';
    }

    public function getCustomFooterColor($data)
    {
        if ($this->issetDataCustom($data, 'footer', 'footer_text_color') != '') {
            return $this->getCodeColor($this->issetDataCustom($data, 'footer', 'footer_text_color'));
        }
        return '';
    }

    public function getPageWrapperBackgroundColor($data){
        if($this->issetDataCustom($data, 'page', 'page_bgcolor') != ''){
            return $this->getCodeColor($this->issetDataCustom($data, 'page', 'page_bgcolor'));
        }
        return '';
    }

    public function getFooterBackgroundColor($data)
    {
        if ($this->issetDataCustom($data, 'footer', 'footer_background_color') != '') {
            return $this->getCodeColor($this->issetDataCustom($data, 'footer', 'footer_background_color'));
        }
        return '';
    }

    public function getTopbarTextColor($data)
    {
        if ($this->issetDataCustom($data, 'header', 'header_topbar_text_color') != '') {
            return $this->getCodeColor($this->issetDataCustom($data, 'header', 'header_topbar_text_color'));
        }
        return '';
    }

    public function getTopbarBackgroundColor($data)
    {
        if ($this->issetDataCustom($data, 'header', 'header_topbar_background_color') != '') {
            return $this->getCodeColor($this->issetDataCustom($data, 'header', 'header_topbar_background_color'));
        }
        return '';
    }

    public function getHeaderTextColor($data)
    {
        if ($this->issetDataCustom($data, 'header', 'header_text_color') != '') {
            return $this->getCodeColor($this->issetDataCustom($data, 'header', 'header_text_color'));
        }
        return '';
    }

    public function getHeaderBackgroundColor($data)
    {
        if ($this->issetDataCustom($data, 'header', 'header_background_color') != '') {
            return $this->getCodeColor($this->issetDataCustom($data, 'header', 'header_background_color'));
        }
        return '';
    }

    public function getButtonsColors($data, $color)
    {
        if ($this->issetDataCustom($data, 'colors', $color) != '') {
            return $this->getCodeColor($this->issetDataCustom($data, 'colors', $color));
        }
        return '';
    }

    public function formatBgColor($color)
    {
        if (strlen($color) == 6) {
            return 'background-color: #' . $color . ';';
        }
        return 'background-color: ' . $color . ';';
    }

    public function formatColor($color)
    {
        if (strlen($color) == 6) {
            return 'color: #' . $color . ';';
        }
        return 'color: ' . $color . ';';
    }

    public function getCodeColor($color)
    {
        if (strlen($color) == 6) {
            return '#' . $color;
        }
        return $color;
    }

    public function getBasicColors($data, $color)
    {
        if ($this->issetDataCustom($data, 'colors', $color) != '') {
            return $this->getCodeColor($this->issetDataCustom($data, 'colors', $color));
        }
        return '';
    }

    public function getThemeColors($data)
    {
        if ($this->issetDataCustom($data, 'colors', 'theme_colors') != '') {
            return $this->getCodeColor($this->issetDataCustom($data, 'colors', 'theme_colors'));
        }
        return '';
    }

    public function issetDataCustom($data, $key1, $key2)
    {
        if (isset($data[$key1][$key2]) && $data[$key1][$key2] != '') {
            return $data[$key1][$key2];
        }
        return '';
    }

    public function getBaseMediaUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }

    public function getConfigDir()
    {
        return $this->cssDir;
    }

    public function getThemeOption()
    {
        if($this->scopeConfig->getValue('themeoption/general/debug_mode') == 1){
            return $this->getBaseMediaUrl(). $this->cssFolder . 'store_' . $this->_storeManager->getStore()->getCode() . '.min.css';
        }
        return $this->getBaseMediaUrl(). $this->cssFolder . 'store_' . $this->_storeManager->getStore()->getCode() . '.css';
    }
}
