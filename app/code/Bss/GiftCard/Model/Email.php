<?php
/**
 * BSS Commerce Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://bsscommerce.com/Bss-Commerce-License.txt
 *
 * @category   BSS
 * @package    Bss_GiftCard
 * @author     Extension Team
 * @copyright  Copyright (c) 2017-2018 BSS Commerce Co. ( http://bsscommerce.com )
 * @license    http://bsscommerce.com/Bss-Commerce-License.txt
 */

namespace Bss\GiftCard\Model;

use Bss\GiftCard\Api\TemplateRepositoryInterface;
use Bss\GiftCard\Helper\Data as GiftCardData;
use Bss\GiftCard\Model\Template\Image\Config;
use Bss\GiftCard\Model\Template\ImageFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Area;
use Magento\Framework\Mail\Template\FactoryInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class email
 * Bss\GiftCard\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Email
{
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var ImageFactory
     */
    private $imageModelFactory;

    /**
     * @var Config
     */
    private $imageConfig;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    private $inlineTranslation;

    /**
     * @var FactoryInterface
     */
    private $emailTemplateFactory;

    /**
     * @var TemplateFactory
     */
    private $templateFactory;

    /**
     * @var GiftCardData
     */
    private $giftCardData;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * @var TemplateRepositoryInterface
     */
    private $templateService;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Email constructor
     *
     * @param TransportBuilder $transportBuilder
     * @param LoggerInterface $logger
     * @param StateInterface $inlineTranslation
     * @param ImageFactory $imageModelFactory
     * @param Config $imageConfig
     * @param GiftCardData $giftCardData
     * @param ProductRepositoryInterface $productRepository
     * @param TimezoneInterface $localeDate
     * @param TemplateRepositoryInterface $templateService
     * @param TemplateFactory $templateFactory
     * @param FactoryInterface $emailTemplateFactory
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        TransportBuilder $transportBuilder,
        LoggerInterface $logger,
        StateInterface $inlineTranslation,
        ImageFactory $imageModelFactory,
        Config $imageConfig,
        GiftCardData $giftCardData,
        ProductRepositoryInterface $productRepository,
        TimezoneInterface $localeDate,
        TemplateRepositoryInterface $templateService,
        TemplateFactory $templateFactory,
        FactoryInterface $emailTemplateFactory,
        StoreManagerInterface $storeManager
    ) {
        $this->transportBuilder = $transportBuilder;
        $this->logger = $logger;
        $this->inlineTranslation = $inlineTranslation;
        $this->imageModelFactory = $imageModelFactory;
        $this->imageConfig = $imageConfig;
        $this->giftCardData = $giftCardData;
        $this->productRepository = $productRepository;
        $this->localeDate = $localeDate;
        $this->templateService = $templateService;
        $this->templateFactory = $templateFactory;
        $this->emailTemplateFactory = $emailTemplateFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Send mail to customer
     *
     * @param array $vars
     * @param int $storeId
     */
    public function sendEmail($vars, $storeId)
    {
        try {
            $this->inlineTranslation->suspend();

            $transport = $this->transportBuilder
                ->setTemplateIdentifier($this->giftCardData->getConfigEmail('to_recipient'))
                ->setTemplateOptions(
                    [
                        'area' => Area::AREA_FRONTEND,
                        'store' => $storeId
                    ]
                )->setTemplateVars(
                    $vars
                )
                ->setFrom($this->giftCardData->getConfigEmail('identity'))
                ->addTo($vars['bss_giftcard_recipient_email'], $vars['bss_giftcard_recipient_name'])
                ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
            if ($this->giftCardData->getConfigEmail('active_to_sender')) {
                $this->sendEmailToSender($vars, $storeId);
            }
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }

    /**
     * Send email
     *
     * @param array $vars
     * @param int $storeId
     */
    private function sendEmailToSender($vars, $storeId)
    {
        try {
            $this->inlineTranslation->suspend();

            $transport = $this->transportBuilder
                ->setTemplateIdentifier($this->giftCardData->getConfigEmail('to_sender'))
                ->setTemplateOptions(
                    [
                        'area' => Area::AREA_FRONTEND,
                        'store' => $storeId
                    ]
                )->setTemplateVars(
                    $vars
                )
                ->setFrom($this->giftCardData->getConfigEmail('identity'))
                ->addTo($vars['bss_giftcard_sender_email'], $vars['bss_giftcard_sender_name'])
                ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }

    /**
     * Preview email
     *
     * @param array $data
     * @param int $store
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function previewEmail($data, $store)
    {
        $emailBody = '';
        $productId = $data['product'];
        $product = $this->productRepository->getById($productId);
        $image = $this->imageModelFactory->create()->load($data['bss_giftcard_selected_image']);
        $amount = (float) $data['bss_giftcard_amount'];
        $template = $this->templateService->getTemplateById($data['bss_giftcard_template'])['template_data'];
        $vars = [
            'store' => $store,
            'senderName' => $data['bss_giftcard_sender_name'],
            'recipientName' => $data['bss_giftcard_recipient_name'],
            'value' => $this->giftCardData->convertPrice($amount),
            'code' => 'XXXXXXXXXXXX',
            'template' => $template,
            'img_url' => $this->imageConfig->getTmpMediaUrl($image->getValue()),
            'img_alt' => $image->getLabel()
        ];
        $expires = $product->getBssGiftCardExpires();
        if ($expires && $expires > 0) {
            $vars['expires'] = 'mm-dd-yyyy';
        }
        if ($product->getBssGiftCardMessage() && isset($data['bss_giftcard_message_email'])) {
            $vars['message'] = nl2br($data['bss_giftcard_message_email']);
        }
        try {
            $emailBody = $this->emailTemplateFactory
                ->get(
                    'bss_giftcard_to_recipient'
                )->setVars($vars)->setOptions(
                    ['area' => Area::AREA_FRONTEND, 'store' => $store->getId()]
                )->processTemplate();
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }

        return $emailBody;
    }

    /**
     * Send email
     *
     * @param mixed $code
     */
    public function autoSendMail($code)
    {
        $image = $this->imageModelFactory->create()->load($code->getImageId());

        $templateModel = $this->templateFactory->create();
        $template = $templateModel->loadProductTemplate($code->getProductId());
        $template = empty($template) ? [] : $template[0];
        $storeId = $code->getStoreId();

        $vars = [
            'senderName' => $code->getSenderName(),
            'recipientName' => $code->getRecipientName(),
            'senderEmail' => $code->getSenderEmail(),
            'recipientEmail' => $code->getRecipientEmail(),
            'value' => $this->giftCardData->convertPrice($code->getValue()),
            'code' => $code->getCode(),
            'message' => $code->getMessage(),
            'img_url' => $this->imageConfig->getTmpMediaUrl($image->getValue()),
            'expires' => $this->localeDate->formatDate(
                $code->getExpiryDay(),
                \IntlDateFormatter::MEDIUM
            ),
            'img_alt' => $image->getLabel(),
            'template' => $template,
            // these are duplicate data but need to add it to homogeneous data
            'bss_giftcard_recipient_name' => $code->getRecipientName(),
            'bss_giftcard_recipient_email' => $code->getRecipientEmail(),
            'bss_giftcard_sender_name' => $code->getSenderName(),
            'bss_giftcard_sender_email' => $code->getSenderEmail()
        ];
        $this->sendEmail($vars, $storeId);
    }

    /**
     * Send email
     *
     * @param mixed $collection
     */
    public function sendEmailNotify($collection)
    {
        foreach ($collection as $code) {
            if ($code->getRecipientEmail()) {
                $this->sendEmailNotifyToRecipient($code);
            }
        }
    }

    /**
     * Send email
     *
     * @param mixed $code
     */
    private function sendEmailNotifyToRecipient($code)
    {
        try {
            $vars = [
                'code' => $code->getCode(),
                'value' => $this->giftCardData->convertPrice($code->getValue()),
                'expires' => date('Y-m-d', strtotime($code->getExpiryDay()))
            ];
            $this->inlineTranslation->suspend();

            $transport = $this->transportBuilder
                ->setTemplateIdentifier($this->giftCardData->getConfigEmail('notify_to_recipient'))
                ->setTemplateOptions(
                    [
                        'area' => Area::AREA_FRONTEND,
                        'store' => $code->getStoreId()
                    ]
                )->setTemplateVars(
                    $vars
                )
                ->setFrom($this->giftCardData->getConfigEmail('identity'))
                ->addTo($code->getRecipientEmail(), $code->getRecipientName())
                ->getTransport();
            $transport->sendMessage();
            $this->inlineTranslation->resume();

            $code->setSentExpireNotify(true)->save();
        } catch (\Exception $e) {
            $this->logger->critical($e->getMessage());
        }
    }
}
