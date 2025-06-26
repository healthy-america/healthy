<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Model\Integration\Check;

class CompanyData extends Check
{

    /**
     * @inheritdoc
     */
    public function getCurrentData(object $data): array
    {
        return [
            'name' => $data->name,
            'email' => $data->email,
            'legal_name' => $data->legal_name,
            'tax_id' => $data->taxvat,
            'street' => $data->address_data['street'],
            'city' => $data->address_data['city'],
            'country_id' => $data->address_data['country_id'],
            'region' => $data->address_data['region'],
            'region_id' => $data->address_data['region_id'],
            'postcode' => $data->address_data['postcode'],
            'telephone' => $data->address_data['telephone'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function getHeadData(mixed $item): array
    {
        return [
            'name' => $item->getCompanyName(),
            'email' => $item->getCompanyEmail(),
            'legal_name' => $item->getLegalName(),
            'tax_id' => $item->getVatTaxId(),
            'street' => isset($item->getStreet()[0]) ? $item->getStreet()[0] : '',
            'city' => $item->getCity(),
            'country_id' => $item->getCountryId(),
            'region' => $item->getRegion(),
            'region_id' => $item->getRegionId(),
            'postcode' => $item->getPostcode(),
            'telephone' => $item->getTelephone(),
        ];
    }
}
