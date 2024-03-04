<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Cron;

use Aventi\SAP\Logger\Logger;
use Aventi\SAP\Model\Integration\SAPStatus;
use Aventi\SAP\Model\Integration\Order;
use \Exception;

/**
 * @class OrderError
 */
class OrderError implements Cron
{
    /**
     * @constructor
     *
     * @param Logger $logger
     * @param Order $manageOrder
     */
    public function __construct(
        private readonly Logger $logger,
        private readonly Order  $manageOrder
    ) {
    }

    /**
     * @inerhitDoc
     */
    public function execute(): int
    {
        $this->logger->info("Cronjob Sincronice order is executed.");
        try {
            $this->manageOrder->process([SAPStatus::SYNCHRONIZATION_ERROR, SAPStatus::ERROR]);
        } catch (Exception $e) {
            $this->logger->debug('Error in order cronjob: ' . $e->getMessage());
        }
        $this->logger->info("Cronjob Sincronice order is finished.");

        return 0;
    }
}
