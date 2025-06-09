<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\Core\Plugin;

use Magento\Csp\Observer\Render;

/**
 * Disable CSP for performance issues
 */
class ByPassCSPValidation
{
    public function aroundExecute(Render $subject, callable $proceed)
    {
        // Do nothing
    }
}
