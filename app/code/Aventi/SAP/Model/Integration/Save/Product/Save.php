<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Model\Integration\Save\Product;

use Aventi\SAP\Model\Integration\Save\Save as InterfaceSave;

class Save implements InterfaceSave
{

    /**
     * @var \Aventi\SAP\Logger\Logger
     */
    private $logger;

    /**
     * @param \Aventi\SAP\Logger\Logger $logger
     */
    public function __construct(
        \Aventi\SAP\Logger\Logger $logger
    ) {
        $this->logger = $logger;
    }

    public function saveFields($params)
    {
        $this->logger->debug('save fields');
        $this->logger->debug($params->itemInterface->getSku());
        foreach ($params->checkData as $key => $data) {
            $this->logger->debug($key);
            $params->itemInterface->setData($key, $data);
            try {
                $this->logger->debug('dave params');
                $params->itemInterface->getResource()->saveAttribute($params->itemInterface, $key);
                $this->logger->debug('---');
            } catch (\Exception $e) {
                $this->logger->debug($e->getMessage());
            }
        }
    }
}
