<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Logger;

class Handler extends \Magento\Framework\Logger\Handler\Base
{
    /**
     * Logging level.
     * @var int
     */
    protected $loggerType = \Monolog\Logger::DEBUG;

    /**
     * File name.
     * @var string
     */
    protected $fileName = "/var/log/aventi_sap_info.log";
}
