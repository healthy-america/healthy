<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\ScopeInterface;
use Aventi\SAP\Logger\Logger;
class DataEmail extends AbstractHelper
{
    const PATH_STORE = 'general/store_information/name';
    const PATH_URL = 'web/secure/base_url';
    const PATH_EMAIL = 'trans_email/ident_general/email';
    const PATH_CUSTOM_EMAIL_1 = 'trans_email/ident_custom1/email';

    /**
     * @var Logger
     */
    private Logger $logger;

    /**
     * @var TransportBuilder
     */
    private TransportBuilder $transportBuilder;
    /**
     * @var StateInterface
     */
    private StateInterface $inlineTranslation;

    /**
     * @param Context $context
     * @param Logger $logger
     * @param TransportBuilder $transportBuilder
     * @param StateInterface $inlineTranslation
     */
    public function __construct(
        Context                               $context,
        Logger                                $logger,
        TransportBuilder                      $transportBuilder,
        StateInterface                        $inlineTranslation
    )
    {
        parent::__construct($context);
        $this->logger = $logger;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
    }

    /**
     * @param null $store
     * @return string
     */
    public function getNameStore($store = null): string
    {
        return (string)$this->scopeConfig->getValue(self::PATH_STORE, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * @param null $store
     * @return string
     */
    public function getUrlStore($store = null): string
    {
        return (string)$this->scopeConfig->getValue(self::PATH_URL, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * @param null $store
     * @return string
     */
    public function getEmail($store = null): string
    {
        return (string)$this->scopeConfig->getValue(self::PATH_EMAIL, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * @param null $store
     * @return string
     */
    public function getCustomEmail($store = null): string
    {
        return (string)$this->scopeConfig->getValue(self::PATH_CUSTOM_EMAIL_1, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * @param $email
     * @param $name
     * @param $password
     * @throws LocalizedException
     * @throws MailException
     */
    public function sendEmail($email, $name, $password){

        $sender = [
            'name' => $this->getNameStore(),
            'email' =>  $this->getEmail()
        ];

        try {
            $transport = $this->transportBuilder
                ->setTemplateIdentifier('customer_create_account_email_template') // this code we have mentioned in the email_templates.xml
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND, // this is using frontend area to get the template file
                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                    ]
                )
                ->setTemplateVars(
                    [
                        'email' => $email,
                        'name' => $name,
                        'password' => $password
                    ]
                )
                ->setFrom($sender)
                >addTo($email)
                ->getTransport();

            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
