<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Cron;

use Aventi\SAP\Logger\Logger;
use Bcn\Component\Json\Exception\ReadingError;
use Magento\Framework\Exception\FileSystemException;

class Customer
{
    /**
     * Constructor
     *
     * @param Logger $logger
     * @param \Aventi\SAP\Model\Integration\Customer $customer
     */
    public function __construct(
        protected Logger $logger,
        protected \Aventi\SAP\Model\Integration\Customer $customer
    ) {
    }

    /**
     * Execute the cron
     *
     * @return void
     * @throws ReadingError
     * @throws FileSystemException
     */
    public function execute(): void
    {
        $this->logger->info("Cronjob Company - Customer is executed.");
        $this->customer->company();
    }
}
