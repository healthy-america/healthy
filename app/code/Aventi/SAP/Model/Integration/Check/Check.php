<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Model\Integration\Check;

abstract class Check
{
    /**
     * Check object current data against head data
     *
     * @param object $data
     * @param object $item
     * @return array|false
     */
    public function checkData(object $data, object $item)
    {
        $currentData = $this->getCurrentData($data);
        $headData = $this->getHeadData($item);
        $checkData = array_diff_assoc($currentData, $headData);

        return empty($checkData) ? false : $checkData;
    }

    /**
     * Get object formatted current data
     *
     * @param object $data
     * @return mixed
     */
    abstract public function getCurrentData(object $data);

    /**
     * Get object formatted head data
     *
     * @param mixed $item
     * @return mixed
     */
    abstract public function getHeadData(mixed $item);

    /**
     * Format decimal price
     *
     * @param $number
     * @return string
     */
    public function formatDecimalNumber($number): string
    {
        return number_format($number, 6, '.', '');
    }
}
