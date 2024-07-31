<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Aventi\Servientrega\Cron;

use Aventi\Servientrega\Model\ShipmentGeneration;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;
use Psr\Log\LoggerInterface;

/**
 * Class GenerateShipping
 */
class GenerateShipping
{
    /**
     * Constructor
     *
     * @param LoggerInterface $logger
     * @param ShipmentGeneration $shipment
     */
    public function __construct(
        protected LoggerInterface $logger,
        private readonly ShipmentGeneration $shipment
    ) {
    }

    /**
     * Execute the cron
     *
     * @return void
     * @throws AlreadyExistsException
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function execute()
    {
        $this->logger->info("Cronjob GenerateShipping is executed.");
        $this->shipment->ordersToShip();
    }
}
