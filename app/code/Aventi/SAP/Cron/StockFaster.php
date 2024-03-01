<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Cron;

use Aventi\SAP\Logger\Logger;
use Magento\Framework\Exception\FileSystemException;
use Aventi\SAP\Model\Integration\Stock;

/**
 * @class StockFaster
 */
class StockFaster implements Cron
{
    /**
     * @constructor.
     *
     * @param Logger $logger
     * @param Stock $manageStock
     */
    public function __construct(
        private readonly Logger $logger,
        private readonly Stock $manageStock
    ) {
    }

    /**
     * @return int
     */
    public function execute(): int
    {
        $this->logger->info("Cronjob Sincronice stock faster is executed.");
        try {
            $this->manageStock->process(['fast' => true]);
        } catch (\Exception $e) {
            $this->logger->debug('Error in stock faster cronjob: ' . $e->getMessage());
        }
        $this->logger->info("Cronjob Sincronice stock faster is finished.");
        return 0;
    }
}
