<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Cron;

use Aventi\SAP\Logger\Logger;
use Aventi\SAP\Model\Integration\Stock as StockIntegration;
use Exception;

/**
 * @class Stock
 */
class Stock implements Cron
{
    /**
     * @constructor
     *
     * @param Logger $logger
     * @param StockIntegration $manageStock
     */
    public function __construct(
        private readonly Logger  $logger,
        private readonly StockIntegration $manageStock
    ) {
    }

    /**
     * @inheritDoc
     */
    public function execute(): int
    {
        $this->logger->info("Cronjob Sincronice stock is executed.");
        try {
            $this->manageStock->process(['fast' => false]);
        } catch (Exception $e) {
            $this->logger->debug('Error in stock cronjob: ' . $e->getMessage());
        }
        $this->logger->info("Cronjob Sincronice stock is finished.");

        return 0;
    }
}
