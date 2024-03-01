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

/**
 * @class OrderNew
 */
class OrderNew implements Cron
{
    /**
     * @constructor
     *
     * @param Logger $logger
     * @param Order $manageOrder
     */
    public function __construct(
        private readonly Logger $logger,
        private readonly Order $manageOrder
    ) {
    }

    /**
     * @inheritDoc
     */
    public function execute(): int
    {
        $this->logger->info("Cronjob Sincronice order is executed.");
        try {
            $this->manageOrder->process(
                [
                    SAPStatus::PENDING,
                    SAPStatus::SYNC,
                    SAPStatus::PROCESSING,
                    SAPStatus::INCOMPLETE
                ]
            );
        } catch (\Exception $e) {
            $this->logger->debug('Error in order cronjob: ' . $e->getMessage());
        }
        $this->logger->info("Cronjob Sincronice order is finished.");

        return 0;
    }
}
