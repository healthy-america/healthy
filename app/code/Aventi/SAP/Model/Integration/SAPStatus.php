<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Model\Integration;

class SAPStatus
{
    // Status
    const COMPLETE = 'complete';
    const SYNCHRONIZATION_ERROR =  'synchronization_error';
    const ERROR =  'error';
    const INCOMPLETE = 'incomplete';
    const PENDING = 'pending';
    const SYNC = 'syncing';
    const PROCESSING = 'processing';
}
