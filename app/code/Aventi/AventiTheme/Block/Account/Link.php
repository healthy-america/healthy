<?php
/**
  * Copyright  Aventi SAS All rights reserved.
  * See COPYING.txt for license details.
*/
declare(strict_types=1);

namespace Aventi\AventiTheme\Block\Account;

use Magento\Customer\Model\Context;
use Magento\Customer\Model\Url;

/**
 * Customer account navigation link
 */
class Link extends \Magento\Customer\Block\Account\Link
{
    /**
     * Customer session
     *
     * @var \Magento\Framework\App\Http\Context
     */
    protected $httpContext;

    /**
     * Link Constructor
     * 
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param Url $customerUrl
     * @param \Magento\Framework\App\Http\Context $httpContext
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        Url                                              $customerUrl,
        \Magento\Framework\App\Http\Context              $httpContext,
        array                                            $data = []
    ) {
        parent::__construct($context, $customerUrl, $data);
        $this->httpContext = $httpContext;
    }

    /**
     * Is logged in
     *
     * @return bool
     */
    public function isLoggedIn(): bool
    {
        return $this->httpContext->getValue(Context::CONTEXT_AUTH);
    }
}
