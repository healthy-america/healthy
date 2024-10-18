<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Cron;

use Aventi\SAP\Logger\Logger;
use Aventi\SAP\Model\Integration\Price as PriceIntegration;
use Exception;

/**
 * @class Price
 */
class PriceFast implements Cron
{
    /**
     * @constructor
     *
     * @param Logger $logger
     * @param PriceIntegration $managePrice
     */
    public function __construct(
        private readonly Logger           $logger,
        private readonly PriceIntegration $managePrice
    ) {
    }

    /**
     * @inheritDoc
     */
    public function execute(): int
    {
        $this->logger->info("Cronjob Sincronice Price fast is executed.");
        try {
            $this->managePrice->process(['fast' => true]);
        } catch (Exception $e) {
            $this->logger->debug('Error in price cronjob: ' . $e->getMessage());
        }
        $this->logger->info("Cronjob Sincronice Price fast is finished.");

        return 0;
    }
}
