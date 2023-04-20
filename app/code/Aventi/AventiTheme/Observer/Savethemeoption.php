<?php

namespace Aventi\AventiTheme\Observer;

use Magento\Framework\App\Cache\Frontend\Pool;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Event\ObserverInterface;

class Savethemeoption implements ObserverInterface
{
    /**
     * @var \Aventi\AventiTheme\Model\Custom\Generator
     */
    protected $_css;

    /**
     * @var TypeListInterface
     */
    protected $cacheTypeList;

    /**
     * @var Pool
     */
    protected $cacheFrontendPool;

    public function __construct(
        \Aventi\AventiTheme\Model\Custom\Generator $css,
        TypeListInterface $cacheTypeList,
        Pool $cacheFrontendPool
    ) {
        $this->_css = $css;
        $this->cacheTypeList = $cacheTypeList;
        $this->cacheFrontendPool = $cacheFrontendPool;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->_css->generateCss($observer->getData("website"), $observer->getData("store"));

        $_types = [
            'config',
            'layout',
            'block_html',
            'full_page'
        ];

        foreach ($_types as $type) {
            $this->cacheTypeList->cleanType($type);
        }

        foreach ($this->cacheFrontendPool as $cacheFrontend) {
            $cacheFrontend->getBackend()->clean();
        }
    }
}
