<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Model\Integration\Check\Customer;

use Aventi\SAP\Model\Integration\Check\Check as AbstractCheck;
use Magento\Customer\Api\Data\CustomerInterface;

class CheckFields extends AbstractCheck
{
    /**
     * @inheritdoc
     */
    public function getCurrentData(object $data): array
    {
        return [
            'taxvat' => $data->taxvat,
            'firstname' => $data->firstname,
            'lastname' => $data->lastname,
            'store_id' => $data->store_id,
            'group_id' => $data->group_id,
            'sap_customer_id' => $data->custom_attributes['sap_customer_id']
        ];
    }

    /**
     * Handle incoming object data.
     *
     * @param mixed $item
     * @return array
     */
    public function getHeadData(mixed $item): array
    {
        $sapCustomerId = $item->getCustomAttribute('sap_customer_id')
            ? $item->getCustomAttribute('sap_customer_id')->getValue() : '';

        return [
            'taxvat' => $item->getTaxvat(),
            'firstname' => $item->getFirstname(),
            'lastname' => $item->getLastname(),
            'store_id' => (int)$item->getStoreId(),
            'group_id' => (int)$item->getGroupId(),
            'sap_customer_id' => $sapCustomerId
        ];
    }
}
