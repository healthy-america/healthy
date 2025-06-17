<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Helper;

use Amasty\CompanyAccount\Api\CompanyRepositoryInterface;
use Amasty\CompanyAccount\Api\CreditRepositoryInterface;
use Amasty\CompanyAccount\Api\Data\CompanyExtensionFactory;
use Amasty\CompanyAccount\Api\Data\CompanyInterface;
use Amasty\CompanyAccount\Api\Data\CompanyInterfaceFactory;
use Amasty\CompanyAccount\Api\Data\CreditInterfaceFactory;
use Amasty\CompanyAccount\Model\CustomerDataProvider;
use Aventi\SAP\Model\Integration\Check\CompanyData;
use Aventi\SAP\Model\Integration\Check\CustomerData;
use Exception;
use Magento\Customer\Api\AddressRepositoryInterface;
use Magento\Customer\Api\Data\AddressInterfaceFactory;
use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Customer\Api\Data\CustomerInterfaceFactory;
use Magento\Customer\Api\Data\GroupInterfaceFactory;
use Magento\Customer\Api\Data\RegionInterfaceFactory;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Customer\Model\ResourceModel\CustomerRepository;
use Magento\Directory\Model\Region;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\State\InputMismatchException;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Amasty\CompanyAccount\Model\ResourceModel\Customer as CustomerCompanyResource;

class Customer extends AbstractHelper
{
    const REGION_TABLE = 'directory_country_region';

    /**
     * Get country path
     */
    public const COUNTRY_CODE_PATH = 'general/country/default';

    /**
     * Constructor
     *
     * @param Context $context
     * @param CompanyInterfaceFactory $companyFactory
     * @param CustomerInterfaceFactory $customerInterfaceFactory
     * @param RegionInterfaceFactory $regionInterfaceFactory
     * @param CompanyExtensionFactory $companyExtensionFactory
     * @param AddressInterfaceFactory $addressInterfaceFactory
     * @param Region $regionModel
     * @param CreditRepositoryInterface $creditRepository
     * @param CreditInterfaceFactory $creditInterfaceFactory
     * @param StoreManagerInterface $storeManager
     * @param CustomerRepository $customerRepository
     * @param CollectionFactory $collectionFactory
     * @param AddressRepositoryInterface $addressRepository
     * @param CompanyData $companyData
     * @param CustomerData $customerData
     * @param ResourceConnection $resourceConnection
     * @param GroupInterfaceFactory $groupFactory
     * @param GroupRepositoryInterface $groupRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CustomerDataProvider $customerDataProvider
     * @param CompanyRepositoryInterface $companyRepository
     */
    public function __construct(
        Context $context,
        protected CompanyInterfaceFactory $companyFactory,
        protected CustomerInterfaceFactory $customerInterfaceFactory,
        protected RegionInterfaceFactory $regionInterfaceFactory,
        protected CompanyExtensionFactory $companyExtensionFactory,
        protected AddressInterfaceFactory $addressInterfaceFactory,
        protected Region $regionModel,
        protected CreditRepositoryInterface $creditRepository,
        protected CreditInterfaceFactory $creditInterfaceFactory,
        protected StoreManagerInterface $storeManager,
        protected CustomerRepository $customerRepository,
        protected CollectionFactory $collectionFactory,
        protected AddressRepositoryInterface $addressRepository,
        protected CompanyData $companyData,
        protected CustomerData $customerData,
        protected ResourceConnection $resourceConnection,
        protected GroupInterfaceFactory $groupFactory,
        protected GroupRepositoryInterface $groupRepository,
        protected SearchCriteriaBuilder $searchCriteriaBuilder,
        protected CustomerDataProvider $customerDataProvider,
        protected CompanyRepositoryInterface $companyRepository,
        protected CustomerCompanyResource $customerCompanyResource
    ) {
        parent::__construct($context);
    }

    /**
     * Get customer address data.
     *
     * @param array $customerData
     * @return array
     */
    public function getAddressData(array $customerData): array
    {
        $documentId = array_key_exists('LicTradNum', $customerData) ? $customerData['LicTradNum'] : '';
        $docType = array_key_exists('U_HBT_TipDoc', $customerData) ? $customerData['U_HBT_TipDoc'] : '';
        $personType = array_key_exists('U_HBT_TipEnt', $customerData) ? $customerData['U_HBT_TipEnt'] : '';
        $taxInfo = array_key_exists('U_HBT_InfoTrib', $customerData) ? $customerData['U_HBT_InfoTrib'] : '';
        foreach ($customerData['Direcciones'] as $addressItem) {
            if ($addressItem['Address'] === 'PRINCIPAL' || $addressItem['Address'] === 'principal') {
                $regionCode = $this->getRegionCode($addressItem['State']);
                $region = $this->regionModel->loadByCode($regionCode, 'CO');

                return [
                    'document_id' => $documentId,
                    'doc_type' => $this->getDocumentType($docType),
                    'person_type' => $this->getPersonType($personType),
                    'tax_information' => $taxInfo,
                    'street' => $addressItem['Street'],
                    'city' => $addressItem['City'],
                    'country_id' => 'CO',//$this->getCountryByWebsite(),
                    'region_id' => $region->getId(),
                    'region_code' => $region->getCode(),
                    'region' => $region->getName(),
                    'postcode' => array_key_exists(
                        'U_HBT_MunMed',
                        $customerData
                    ) ? $customerData['U_HBT_MunMed'] : $addressItem['ZipCode'],
                    'telephone' => $addressItem['Phone1']
                ];
            }
        }

        return [];
    }

    /**
     * Get the id of a Co region with your code
     *
     * @param $code
     * @return string
     */
    private function getRegionCode($code): string
    {
        return match ($code) {
            "001" => '731',
            "002" => '747',
            "003" => '721',
            default => $code,
        };
    }

    /**
     * Creates company with SAP customer data.
     *
     * @param CompanyInterface $company
     * @param CustomerInterface $customer
     * @throws Exception
     */
    public function saveCompany(CompanyInterface $company, CustomerInterface $customer): void
    {
        try {
            $customer = $this->customerRepository->save($customer);
            $company->setSuperUserId($customer->getId());
            $company = $this->companyRepository->save($company);
            $this->customerCompanyResource->assignCompany($company->getCompanyId(), [$customer->getId()], false);
        } catch (InputException|InputMismatchException|LocalizedException|CouldNotSaveException $e) {
            $this->_logger->error($e);
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Get Country code by website scope
     *
     * @return string
     */
    public function getCountryByWebsite(): string
    {
        return $this->scopeConfig->getValue(
            self::COUNTRY_CODE_PATH,
            ScopeInterface::SCOPE_WEBSITES
        );
    }

    /**
     * Prepare company object.
     *
     * @param object $data
     * @return array
     */
    public function prepareCompany(object $data): array
    {
        try {
            $collection = $this->collectionFactory->create();
            $customer = $collection->addAttributeToFilter(
                'sap_customer_id',
                ['eq' => $data->custom_attributes['sap_customer_id']]
            )->getFirstItem();

            if (!empty($customer->getData())) {
                $companyObject = $this->customerDataProvider->getCompanyByCustomerId((int) $customer->getData('entity_id'));
                if ($companyObject) {
                    $result = $this->companyData->checkData($data, $companyObject);
                    if (!$result) {
                        return ['action' => 'check', 'object' => $companyObject];
                    }
                    $company = $this->prepareDataCompany($companyObject, $data, 'update');

                    return ['action' => 'update', 'object' => $company];
                }
            }
            throw new NoSuchEntityException();
        } catch (NoSuchEntityException|LocalizedException $e) {
            $result = $this->checkCustomerEmail($data);
            if ($result) {
                return ['action' => 'exist'];
            }
            $company = $this->companyFactory->create();
            $companyObject = $this->prepareDataCompany($company, $data, 'create');

            return ['action' => 'create', 'object' => $companyObject];
        }
    }

    /**
     * Prepare customer data.
     *
     * @param object $data
     * @return array|null
     * @throws LocalizedException
     */
    public function prepareCustomer(object $data): ?array
    {
        try {
            $collection = $this->collectionFactory->create();
            $customerCollection = $collection->addAttributeToFilter(
                'sap_customer_id',
                ['eq' => $data->custom_attributes['sap_customer_id']]
            )->getFirstItem();

            $customerObject = $this->customerRepository->getById($customerCollection->getData('entity_id'));
            if ($customerObject) {
                $result = $this->customerData->checkData($data, $customerObject);
                if (!$result) {
                    return ['action' => 'check', 'object' => $customerObject];
                }
                $customer = $this->prepareDataCustomer($customerObject, $data, 'update');

                return ['action' => 'update', 'object' => $customer];
            }
        } catch (NoSuchEntityException|LocalizedException $e) {
            $customer = $this->customerInterfaceFactory->create();
            $customer = $this->prepareDataCustomer($customer, $data, 'create');

            return ['action' => 'create', 'object' => $customer];
        }

        return null;
    }

    /**
     * Update customer credit.
     *
     * @param CompanyInterface $company
     * @param float $creditLimit
     * @param float $creditBalance
     */
    public function updateCreditLimit(
        CompanyInterface $company,
        float $creditLimit,
        float $creditBalance
    ): void {

        try {
            $creditInterface = $this->creditRepository->getByCompanyId($company->getCompanyId());
            $creditInterface->setBalance($creditLimit);
            $creditInterface->setBePaid($creditBalance);

            $this->creditRepository->save($creditInterface);
        } catch (NoSuchEntityException $e) {
            $this->_logger->error($e->getMessage());

            $extensionAttributes = $company->getExtensionAttributes();
            $creditInterface = $this->creditInterfaceFactory->create();
            $creditInterface->setBalance($creditLimit);
            $creditInterface->setBePaid($creditBalance);
            $creditInterface->setCurrencyCode('COP');
            $company->setExtensionAttributes($extensionAttributes->setCredit($creditInterface));

            try {
                $this->companyRepository->save($company);
            } catch (CouldNotSaveException|LocalizedException $e) {
                $this->_logger->error($e->getMessage());
            }
        }
    }

    /**
     * Prepare customer data.
     *
     * @param CustomerInterface $customer
     * @param object $data
     * @param string $action
     * @return CustomerInterface
     * @throws LocalizedException
     */
    private function prepareDataCustomer(CustomerInterface $customer, object $data, string $action): CustomerInterface
    {
        $customer->setFirstname($data->firstname);
        $customer->setLastname($data->lastname);
        $customer->setEmail($data->email);
        $customer->setGroupId($data->group_id);
        $customer->setTaxvat($data->address_data['document_id']);
        $customer->setCustomAttribute('sap_customer_id', $data->custom_attributes['sap_customer_id']);
        $customer->setCustomAttribute('price_list', $data->custom_attributes['price_list']);
        $customer->setCustomAttribute('group_num', $data->custom_attributes['group_num']);

        if ($action == 'update') {
            $addressId = $customer->getAddresses()[0]->getId();
            $address = $this->addressRepository->getById($addressId);
        } else {
            $address = $this->addressInterfaceFactory->create();
        }
        $address->setCustomerId($customer->getId());
        $address->setFirstname($data->firstname);
        $address->setLastname($data->lastname);
        $address->setTelephone($data->address_data['telephone']);

        $street[] = $data->address_data['street'];//pass street as array
        $address->setStreet($street);
        $address->setCity($data->address_data['city']);
        $address->setCountryId($data->address_data['country_id']);

        // Create region interface for Customer Address.
        $region = $this->regionInterfaceFactory->create();
        $region->setRegion($data->address_data['region']);
        $region->setRegionId($data->address_data['region_id']);
        $region->setRegionCode($data->address_data['region_code']);

        $address->setRegion($region);
        $address->setRegionId($data->address_data['region_id']);
        $address->setPostcode($data->address_data['postcode']);
        $address->setFax($data->address_data['doc_type']);
        $address->setVatId($data->address_data['document_id']);
        $address->setPrefix($data->address_data['person_type']);
        $address->setCompany($data->address_data['tax_information']);
        $address->setIsDefaultBilling(true);
        $address->setIsDefaultShipping(true);
        $customer->setAddresses([$address]);

        return $customer;
    }

    /**
     * Prepare company data
     *
     * @param CompanyInterface $companyObject
     * @param object $data
     * @param string $action
     * @return CompanyInterface
     */
    private function prepareDataCompany(CompanyInterface $companyObject, object $data, string $action): CompanyInterface
    {
        $extensionAttributes = $companyObject->getExtensionAttributes();
        if ($extensionAttributes === null) {
            $extensionAttributes = $this->companyExtensionFactory->create();
        }
        $extensionAttributes->setSapCustomerId($data->custom_attributes['sap_customer_id']);

        if ($action === 'create') {
            $companyObject->setStatus($data->status);
        }
        $companyObject->setCompanyName($data->name);
        $companyObject->setCompanyEmail($data->email);
        $companyObject->setLegalName($data->legal_name);
        $companyObject->setVatTaxId($data->address_data['document_id']);
        $companyObject->setStreet($data->address_data['street']);
        $companyObject->setCity($data->address_data['city']);
        $companyObject->setCountryId($data->address_data['country_id']);
        $companyObject->setRegion($data->address_data['region']);
        $companyObject->setRegionId($data->address_data['region_id']);
        $companyObject->setPostcode($data->address_data['postcode']);
        $companyObject->setTelephone($data->address_data['telephone']);
        $companyObject->setCustomerGroupId(1);
        $companyObject->setExtensionAttributes($extensionAttributes);

        return $companyObject;
    }

    /**
     * Checks if customer exits by its email.
     *
     * @param object $data
     * @return bool
     */
    private function checkCustomerEmail(object $data): bool
    {
        try {
            $customer = $this->customerRepository->get($data->email);
            return true;
        } catch (NoSuchEntityException|LocalizedException $e) {
            return false;
        }
    }

    /**
     * Get document type from SAP to Magento
     *
     * @param $sapId
     * @return string
     */
    protected function getDocumentType($sapId): string
    {
        return match ((string)$sapId) {
            "13" => "CC",
            "22" => "CE",
            "31" => "RUT",
            default => ""
        };
    }

    /**
     * Get person type from SAP to Magento
     *
     * @param $sapId
     * @return string
     */
    protected function getPersonType($sapId): string
    {
        return match ((string)$sapId) {
            "1" => "Natural",
            "2" => "Legal",
            default => ""
        };
    }
}
