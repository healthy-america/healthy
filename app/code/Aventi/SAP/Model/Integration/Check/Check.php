<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Model\Integration\Check;

abstract class Check
{
    protected $currentTable;

    public function checkData(object $data, object $item)
    {
        $currentData = $this->getCurrentData($data);
        $headData = $this->getHeadData($item, $data);
        $checkData = array_diff_assoc($currentData, $headData);

        return empty($checkData) ? false : $checkData;
    }

    abstract public function getCurrentData($data);
    abstract public function getHeadData($item, $data);

    public function formatDecimalNumber($number): string
    {
        return number_format($number, 6, '.', '');
    }

    public function setCurrentTable($table)
    {
        $this->currentTable = $table;

    }
}
