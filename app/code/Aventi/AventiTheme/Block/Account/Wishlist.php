<?php

namespace Aventi\AventiTheme\Block\Account;

use Magento\Framework\App\Http\Context;
use Magento\Wishlist\Block\Link;
use Magento\Wishlist\Helper\Data;

class Wishlist extends Link
{
    protected $_template = 'Aventi_AventiTheme::account/my-wishlist.phtml';

    /**
     * Customer session
     *
     * @var Context
     */
    protected $httpContext;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param Data $wishlistHelper
     * @param Context $httpContext
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        Data $wishlistHelper,
        Context $httpContext,
        array $data = []
    ) {
        parent::__construct($context, $wishlistHelper, $data);
        $this->httpContext = $httpContext;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->_wishlistHelper->isAllow()) {
            return parent::_toHtml();
        }
        return '';
    }

    /**
     * Is logged in
     *
     * @return bool
     */
    public function isLoggedIn(): bool
    {
        return $this->httpContext->getValue(\Magento\Customer\Model\Context::CONTEXT_AUTH);
    }
}
