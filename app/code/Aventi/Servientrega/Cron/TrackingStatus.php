<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Aventi\Servientrega\Cron;

use Aventi\Servientrega\Model\Sync\Guide;
use Exception;
use Psr\Log\LoggerInterface;

/**
 * Class TrackingStatus
 */
class TrackingStatus
{

    /**
     * Constructor
     *
     * @param LoggerInterface $logger
     * @param Guide $guide
     */
    public function __construct(
        protected LoggerInterface $logger,
        protected Guide $guide
    ) {
    }

    /**
     * Execute the cron
     *
     * @return void
     * @throws Exception
     */
    public function execute()
    {
        $this->logger->info("Cronjob Tracking is executed.");
        $this->guide->syncStatuses();
    }
}
