<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Aventi\Servientrega\Cron;

class GenerateShipping
{
    protected $logger;
    /**
     * @var \Aventi\Servientrega\Model\ShipmentGeneration
     */
    private $shipment;

    /**
     * Constructor
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Aventi\Servientrega\Model\ShipmentGeneration $shipmentGeneration
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Aventi\Servientrega\Model\ShipmentGeneration $shipmentGeneration
    ) {
        $this->logger = $logger;
        $this->shipment = $shipmentGeneration;
    }

    /**
     * Execute the cron
     *
     * @return void
     */
    public function execute()
    {
        $this->logger->addInfo("Cronjob GenerateShipping is executed.");
        $this->shipment->ordersToShip();
    }
}
