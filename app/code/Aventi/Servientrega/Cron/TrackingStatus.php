<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Aventi\Servientrega\Cron;

class TrackingStatus
{

    protected $logger;
    /**
     * @var \Aventi\Servientrega\Model\Sync\Guide
     */
    protected $_guide;

    /**
     * Constructor
     *
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Aventi\Servientrega\Model\Sync\Guide $guide
     */
    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Aventi\Servientrega\Model\Sync\Guide $guide
    ) {
        $this->logger = $logger;
        $this->_guide = $guide;
    }

    /**
     * Execute the cron
     *
     * @return void
     * @throws \Exception
     */
    public function execute()
    {
        $this->logger->addInfo("Cronjob Tracking is executed.");
        $this->_guide->syncStatuses();
    }
}
