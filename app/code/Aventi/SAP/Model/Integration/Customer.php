<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Model\Integration;

use Amasty\CompanyAccount\Api\Data\CompanyInterface;
use Amasty\CompanyAccount\Model\Source\Company\Status;
use Aventi\SAP\Helper\Attribute;
use Aventi\SAP\Helper\Configuration;
use Aventi\SAP\Helper\Customer as CustomerHelper;
use Aventi\SAP\Helper\Data;
use Aventi\SAP\Logger\Logger;
use Aventi\SAP\Model\Integration;
use Bcn\Component\Json\Exception\ReadingError;
use Bcn\Component\Json\Reader;
use Exception;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\DriverInterface;

class Customer extends Integration
{
    public const TYPE_URI = 'customer';
    public const TYPE_URI_FAST = 'customer_fast';

    public const DEFAULT_STORE_ID = 1;

    /**
     * Synchronization table results
     *
     * @var array|int[]
     */
    private array $resTable = [
        'check' => 0,
        'fail' => 0,
        'new' => 0,
        'updated' => 0,
        'exist' => 0
    ];

    /**
     * Constructor
     *
     * @param CustomerRepositoryInterface $customerRepository
     * @param CollectionFactory $customerCollectionFactory
     * @param CustomerHelper $customerHelper
     * @param Configuration $configuration
     * @param DriverInterface $driver
     * @param Filesystem $filesystem
     * @param Attribute $attribute
     * @param Data $data
     * @param Logger $logger
     */
    public function __construct(
        protected CustomerRepositoryInterface $customerRepository,
        protected CollectionFactory $customerCollectionFactory,
        protected CustomerHelper $customerHelper,
        protected Configuration $configuration,
        DriverInterface $driver,
        Filesystem $filesystem,
        Attribute $attribute,
        protected Data $data,
        Logger $logger
    ) {
        parent::__construct($attribute, $logger, $driver, $filesystem);
    }

    /**
     * Synchronize customers from SAP
     *
     * @throws NoSuchEntityException
     * @throws LocalizedException|ReadingError
     */
    public function process(array $data = null): void
    {
        $initialRegister = 0;
        $amountOfRegisters = 1000;
        $rule = str_split("-+~/\\<>\'\":*$#@()!,?`=%&^", 1);
        $ruleEmail = str_split("-+~/\\<>\'\":*$#()!,?`=%&^", 1);

        while (true) {
            $jsonData = $this->data->getResource(self::TYPE_URI, $initialRegister, $amountOfRegisters, $data['fast']);
            $jsonPath = $this->getJsonPath($jsonData, self::TYPE_URI);
            if ($jsonPath) {
                $reader = $this->getJsonReader($jsonPath);
                $reader->enter(null, Reader::TYPE_OBJECT);
                $total = (int)$reader->read('total');
                $customers = $reader->read('data');
                if ($total <= 0) {
                    break;
                }
                $progressBar = $this->startProgressBar($total);

                foreach ($customers as $customer) {
                    $cardName = str_replace($rule, " ", $customer['CardName']);
                    $email = str_replace($ruleEmail, "", $customer['E_Mail']);
                    $customerObject = (object) [
                        // General.
                        'name' => $cardName,
                        'status' => Status::STATUS_ACTIVE,
                        'email' => $this->getEmailFormatted($email),
                        'group_id' => 1,
                        // Account Information.
                        'legal_name' => $cardName,
                        //'status' => $this->getStatus($customer['frozenFor']),
                        // Address.
                        'address_data' => $this->customerHelper->getAddressData($customer),
                        // Company Admin Information.
                        'firstname' => $cardName,
                        'lastname' => '.',//$customer['LastName'] ?: '.',
                        'custom_attributes' => [
                            'sap_customer_id' => $customer['CardCode'] ?: '',
                            'price_list' => $customer['ListNum'],
                            'group_num' => $customer['GroupNum'],
                            //'sap_slp_code' => $customer['SlpCode']
                        ],
                        'credit_data' => [
                            'credit_limit' => (float) $customer['CreditLine'],
                            'credit_balance' => -(float) $customer['Balance']//Balance
                        ]
                    ];
                    if ($this->validateRequiredFieldsToCreateCustomer($customerObject)) {
                        $this->managerCompany($customerObject);
                    }
                    $this->advanceProgressBar($progressBar);
                    // Debug only
                    //$total--;
                    //sleep(5);
                }
                $initialRegister += $amountOfRegisters;
                $this->finishProgressBar($progressBar, $initialRegister, $amountOfRegisters);
                $this->closeFile($jsonPath);
                $progressBar = null;
            } else {
                break;
            }
        }
        $this->printTable($this->resTable);
    }

    /**
     * Method that check if the required fields to create a customer come from SAP
     *
     * @param $customerObjectFromWs
     * @return bool
     */
    private function validateRequiredFieldsToCreateCustomer($customerObjectFromWs): bool
    {
        if ($customerObjectFromWs->address_data) {
            return true;
        } else {
            $this->resTable['fail']++;

            return false;
        }
    }
//
    /**
     * Retrieves email formatted
     *
     * @param mixed $email
     * @return string
     */
    private function getEmailFormatted(mixed $email): string
    {
        $formattedEmail = strtolower($email);
        if (str_contains($email, ';')) {
            $formattedEmail = substr($email, 0, strpos($email, ";"));
        } elseif (str_contains($email, ',')) {
            $formattedEmail = substr($email, 0, strpos($email, ","));
        }

        return trim($formattedEmail);
    }

    /**
     * Handle company data post-synchronization
     *
     * @param object $customerObject
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    private function managerCompany(object $customerObject): void
    {
        if ($customerObject->email === null || $customerObject->email === "") {
            $this->resTable['fail']++;
            return;
        }

        $company = $this->customerHelper->prepareCompany($customerObject);
        $customer = $this->customerHelper->prepareCustomer($customerObject);

        try {
            if ($company['action'] == 'check' && $customer['action'] == 'check') {
                $this->updateCreditLimit($company['object'], $customerObject);
                $this->resTable['check']++;
            } elseif ($company['action'] == 'create') {
                $this->customerHelper->saveCompany($company['object'], $customer['object']);
                $this->updateCreditLimit($company['object'], $customerObject);
                $this->resTable['new']++;
            } elseif ($company['action'] == 'update' || $customer['action'] == 'update') {
                $this->customerHelper->saveCompany($company['object'], $customer['object']);
                $this->updateCreditLimit($company['object'], $customerObject);
                $this->resTable['updated']++;
            } elseif ($company['action'] === 'exist') {
                $this->resTable['exist']++;
            }
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            $this->resTable['fail']++;
        }
    }

    public function updateCreditLimit(CompanyInterface $company, object $customerObject): void
    {
        $this->customerHelper->updateCreditLimit(
            $company,
            $customerObject->credit_data['credit_limit'],
            $customerObject->credit_data['credit_balance']
        );
    }

//    /**
//     * Return customer fast ws URI type
//     *
//     * @param bool $fast
//     * @return string
//     */
//    public function getUri(bool $fast): string
//    {
//        if ($fast) {
//            return self::TYPE_URI_FAST;
//        } else {
//            return self::TYPE_URI;
//        }
//    }
}
