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
        foreach ($params->checkData as $key => $data) {
            if (empty($data)) {
                continue;
            }

            if ($key === 'website_code') {
                $data = explode(',', $data);
                $params->itemInterface->setWebsiteIds($data);
            } else {
                $params->itemInterface->setData($key, $data);
            }

            try {
                if ($key === 'website_code') {
                    continue;
                } else {
                    $params->itemInterface->getResource()->saveAttribute($params->itemInterface, $key);
                }
            } catch (\Exception$e) {
                $this->logger->debug($e->getMessage());
            }
        }
    }
}
