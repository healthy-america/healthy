<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Model\Integration\Check;

use Magento\Customer\Api\Data\CustomerInterface;

class CustomerData extends Check
{
    /**
     * @inheritdoc
     */
    public function getCurrentData(object $data): array
    {
        return [
            'firstname' => $data->firstname,
            'lastname' => $data->lastname,
            'sap_customer_id' => $data->custom_attributes['sap_customer_id'],
            'price_list' => $data->custom_attributes['price_list'],
            'group_num' => $data->custom_attributes['group_num'],
            'taxvat' => $data->address_data['document_id'],
            'street' => $data->address_data['street'],
            'city' => $data->address_data['city'],
            'country_id' => $data->address_data['country_id'],
            'region_id' => $data->address_data['region_id'],
            'region_code' => $data->address_data['region_code'],
            'region' => $data->address_data['region'],
            'postcode' => $data->address_data['postcode'],
            'telephone' => $data->address_data['telephone'],
            'group_id' =>  $data->group_id,
            'doc_type' => $data->address_data['doc_type'],
            'person_type' => $data->address_data['person_type'],
            'tax_information' => $data->address_data['tax_information']
        ];
    }

    /**
     * @inheritdoc
     */
    public function getHeadData(mixed $item): array
    {
        $sapCustomerId = $item->getCustomAttribute('sap_customer_id')
            ? $item->getCustomAttribute('sap_customer_id')->getValue() : '';
        $priceList = $item->getCustomAttribute('price_list')
            ? $item->getCustomAttribute('price_list')->getValue() : '';
        $groupNum = $item->getCustomAttribute('group_num')
            ? $item->getCustomAttribute('group_num')->getValue() : '';

        return [
            'firstname' => $item->getFirstname(),
            'lastname' => $item->getLastname(),
            'sap_customer_id' => $sapCustomerId,
            'price_list' => $priceList,
            'group_num' => $groupNum,
            'taxvat' => $item->getTaxvat(),
            'street' => array_key_exists(0, $item->getAddresses()) ? $item->getAddresses()[0]->getStreet()[0] : '',
            'city' => array_key_exists(0, $item->getAddresses()) ? $item->getAddresses()[0]->getCity() : '',
            'country_id' => array_key_exists(0, $item->getAddresses()) ? $item->getAddresses()[0]->getCountryId() : '',
            'region_id' => array_key_exists(0, $item->getAddresses()) ? $item->getAddresses()[0]->getRegionId() : '',
            'region_code' => array_key_exists(0, $item->getAddresses()) ? $item->getAddresses()[0]->getRegion()->getRegionCode() : '',
            'region' => array_key_exists(0, $item->getAddresses()) ? $item->getAddresses()[0]->getRegion()->getRegion() : '',
            'postcode' => array_key_exists(0, $item->getAddresses()) ? $item->getAddresses()[0]->getPostcode() : '',
            'telephone' => array_key_exists(0, $item->getAddresses()) ? $item->getAddresses()[0]->getTelephone() : '',
            'group_id' =>  $item->getGroupId(),
            'doc_type' => array_key_exists(0, $item->getAddresses()) ? $item->getAddresses()[0]->getFax() : '',
            'person_type' => array_key_exists(0, $item->getAddresses()) ? $item->getAddresses()[0]->getPrefix() : '',
            'tax_information' => array_key_exists(0, $item->getAddresses()) ? $item->getAddresses()[0]->getCompany() : '',
        ];
    }
}
