<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Cron;

interface Cron
{
    /**
     * @return int
     */
    public function execute(): int;
}
