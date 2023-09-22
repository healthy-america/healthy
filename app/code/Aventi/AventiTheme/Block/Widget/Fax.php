<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Aventi\AventiTheme\Block\Widget;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Block\Widget\Fax as MagentoFax;
use Aventi\AventiTheme\Model\Config\Source\CustomerIdentificationTypeOptions;
use Magento\Customer\Helper\Address as AddressHelper;
use Magento\Customer\Model\Options;
use Magento\Framework\View\Element\Template\Context;

/**
 * Widget for showing customer company.
*/
class Fax extends MagentoFax
{
    /**
     * @var CustomerIdentificationTypeOptions
     */
    private CustomerIdentificationTypeOptions $customerIdentificationTypeOptions;

    /**
     * @param Context $context
     * @param AddressHelper $addressHelper
     * @param CustomerMetadataInterface $customerMetadata
     * @param Options $options
     * @param AddressMetadataInterface $addressMetadata
     * @param CustomerIdentificationTypeOptions $customerIdentificationTypeOptions
     * @param array $data
     */
    public function __construct(
        Context $context,
        AddressHelper $addressHelper,
        CustomerMetadataInterface $customerMetadata,
        Options $options,
        AddressMetadataInterface $addressMetadata,
        CustomerIdentificationTypeOptions $customerIdentificationTypeOptions,
        array $data = []
    ) {
        parent::__construct($context, $addressHelper, $customerMetadata, $options, $addressMetadata, $data);
        $this->customerIdentificationTypeOptions = $customerIdentificationTypeOptions;
    }

    /**
     * @return array[]
     */
    public function getCustomerIdentificationTypeOptions()
    {
        return $this->customerIdentificationTypeOptions->toOptionArray();
    }
}
