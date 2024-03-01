<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Cron;

use Aventi\SAP\Logger\Logger;
use Aventi\SAP\Model\Integration\Product as ProductIntegration;

/**
 * @class Product
 */
class Product implements Cron
{
    /**
     * @constructor
     *
     * @param Logger $logger
     * @param ProductIntegration $manageProduct
     */
    public function __construct(
        private readonly Logger    $logger,
        private readonly ProductIntegration $manageProduct
    ) {
    }

    /**
     * @inheritDoc
     */
    public function execute(): int
    {
        $this->logger->info("Cronjob Sincronice Product is executed.");
        try {
            $this->manageProduct->process(['fast' => false]);
        } catch (\Exception $e) {
            $this->logger->debug('Error in product cronjob: ' . $e->getMessage());
        }
        $this->logger->info("Cronjob Sincronice Product is finished.");

        return 0;
    }
}
