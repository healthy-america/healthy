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

namespace Bss\GiftCard\Model\Pattern;

use Bss\GiftCard\Api\TemplateRepositoryInterface;
use Bss\GiftCard\Model\Config\Source\Status;
use Bss\GiftCard\Model\Email;
use Bss\GiftCard\Model\PatternFactory;
use Bss\GiftCard\Model\ResourceModel\Pattern\Code as CodeResourceModel;
use Magento\Customer\Model\SessionFactory;
use Magento\Framework\Math\Random;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class code
 *
 * Bss\GiftCard\Model\Pattern
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class Code extends AbstractModel
{
    public const CHARS_LENGTH = 1;
    public const DIGIT_CODE = '{D}';
    public const LETTER_CODE = '{L}';

    /**
     * @var Random
     */
    private $random;

    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var DateTimeFactory
     */
    private $dateFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var SessionFactory
     */
    private $customerSession;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Email
     */
    private $emailModel;

    /**
     * @var HistoryFactory
     */
    private $historyFactory;

    /**
     * @var TemplateRepositoryInterface
     */
    private $templateService;

    /**
     * @var PatternFactory
     */
    protected $giftCardPattern;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param Random $random
     * @param DateTime $dateTime
     * @param DateTimeFactory $dateFactory
     * @param StoreManagerInterface $storeManager
     * @param SessionFactory $customerSession
     * @param Email $emailModel
     * @param HistoryFactory $historyFactory
     * @param TemplateRepositoryInterface $templateService
     * @param PatternFactory $giftCardPattern
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        Registry $registry,
        Random $random,
        DateTime $dateTime,
        DateTimeFactory $dateFactory,
        StoreManagerInterface $storeManager,
        SessionFactory $customerSession,
        Email $emailModel,
        HistoryFactory $historyFactory,
        TemplateRepositoryInterface $templateService,
        PatternFactory $giftCardPattern
    ) {
        $this->random = $random;
        $this->dateTime = $dateTime;
        $this->dateFactory = $dateFactory;
        $this->storeManager = $storeManager;
        $this->customerSession = $customerSession;
        $this->logger = $context->getLogger();
        $this->emailModel = $emailModel;
        $this->historyFactory = $historyFactory;
        $this->templateService = $templateService;
        $this->giftCardPattern = $giftCardPattern;
        parent::__construct(
            $context,
            $registry
        );
    }

    /**
     * Construct
     *
     * @return void
     */
    public function _construct()
    {
        $this->_init(CodeResourceModel::class);
    }

    /**
     * Import codes
     *
     * @param array $importCodesRawData
     * @param int $patternId
     * @return int[]|void
     */
    public function importCodes($importCodesRawData, $patternId)
    {
        if (!empty($importCodesRawData)) {
            $success = 0;
            $error = 0;
            $pattern = $this->getPattern($patternId);
            foreach ($importCodesRawData as $csvLine) {
                $codeText = $csvLine[0];
                $expiryDate = $this->dateTime->formatDate($csvLine[2]);
                $validate = $this->getResource()->validateCode($codeText);
                if (!empty($csvLine) && empty($validate) && preg_match($pattern, $codeText)) {
                    $data = [
                        'pattern_id' => $patternId,
                        'status' => Status::BSS_GC_STATUS_ACTIVE,
                        'value' => $csvLine[1],
                        'origin_value' => $csvLine[1],
                        'code' => $codeText,
                        'expiry_day' => $expiryDate
                    ];
                    $this->getResource()->insertCode($data);
                    $success++;
                } else {
                    $error++;
                }
            }
            return [
                'success' => $success,
                'error' => $error
            ];
        }
    }

    /**
     * Get pattern
     *
     * @param int $patternId
     * @return string regex format
     */
    private function getPattern($patternId)
    {
        $patternModel = $this->giftCardPattern->create()->load($patternId);
        $pattern = $patternModel->getPattern();
        $pattern = str_replace(
            [Code::DIGIT_CODE, Code::LETTER_CODE],
            ['\d', '[a-zA-Z]'],
            $pattern
        );
        return "/$pattern/";
    }

    /**
     * Get codes linked to patternId
     *
     * @param int $patternId
     */
    public function getByPattern($patternId)
    {
        return $this->getResource()->getByPattern($patternId);
    }

    /**
     * Generate codes
     *
     * @param   \Bss\GiftCard\Model\Pattern $pattern
     * @param   array $data
     * @param   array $vars
     * @return  bool
     */
    public function generateCodes($pattern, $data, $vars = [])
    {
        if ($pattern->getId()) {
            $qty = 1;
            try {
                while ($qty <= $data['qty']) {
                    if ($this->generateCode($pattern, $data, $vars)) {
                        $qty++;
                    }
                }
                return true;
            } catch (\Exception $e) {
                $this->logger->critical($e);
                return false;
            }
        }
        return false;
    }

    /**
     * Generate code
     *
     * @param \Bss\GiftCard\Model\Pattern $pattern
     * @param array $param
     * @param array $vars
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function generateCode($pattern, $param, $vars)
    {
        $codeName = $pattern->getPattern();
        $this->replacePatternCode($codeName, self::LETTER_CODE);
        $this->replacePatternCode($codeName, self::DIGIT_CODE);
        $codeResource = $this->getResource();
        $validate = $codeResource->validateCode($codeName);
        if (empty($validate)) {
            $data = [
                'code' => $codeName,
                'pattern_id' => $pattern->getId(),
                'sent' => true,
                'value' => $param['amount'],
                'origin_value' => $param['amount'],
                'status' => $this->handleCodeStatus($param['expiry'])
            ];

            $data = $this->setDataByParams($param, $data);
            $data = $this->setDataByVars($vars, $data);
            $this->setData($data)->save();
            if (!empty($vars)) {
                $vars['code'] = $codeName;
                $template = isset($vars['bss_giftcard_template']) ?
                    $this->templateService->getTemplateById($vars['bss_giftcard_template'])['template_data'] : null;
                if (!empty($template)) {
                    $vars['template']= $template;
                }
                if (!isset($param['delivery_date']) || !$param['delivery_date']) {
                    $this->emailModel->sendEmail($vars, $param['store_id']);
                }
            }
            return true;
        }
        return false;
    }

    /**
     * Set data by params
     *
     * @param array $param
     * @param array $data
     * @return mixed
     */
    private function setDataByParams($param, $data)
    {
        if (isset($param['store_id'])) {
            $data['store_id'] = $param['store_id'];
        }
        if (isset($param['order_id'])) {
            $data['order_id'] = $param['order_id'];
        }
        if (isset($param['product_id'])) {
            $data['product_id'] = $param['product_id'];
        }
        if (isset($param['website_id'])) {
            $data['website_id'] = $param['website_id'];
        }
        if (isset($param['delivery_date']) && $param['delivery_date']) {
            $data['send_at'] = $param['delivery_date'];
            $data['sent'] = false;
        }
        if ($param['expiry']) {
            $data['expiry_day'] = $this->dateTime->formatDate($param['expiry']);
        }
        return $data;
    }

    /**
     * Set data by vars
     *
     * @param array $vars
     * @param array $data
     * @return mixed
     */
    private function setDataByVars($vars, $data)
    {
        if (isset($vars['bss_giftcard_sender_name'])) {
            $data['sender_name'] = $vars['bss_giftcard_sender_name'];
        }
        if (isset($vars['bss_giftcard_sender_email'])) {
            $data['sender_email'] = $vars['bss_giftcard_sender_email'];
        }
        if (isset($vars['bss_giftcard_recipient_name'])) {
            $data['recipient_name'] = $vars['bss_giftcard_recipient_name'];
        }
        if (isset($vars['bss_giftcard_recipient_email'])) {
            $data['recipient_email'] = $vars['bss_giftcard_recipient_email'];
        }
        if (isset($vars['message'])) {
            $data['message'] = $vars['message'];
        }
        if (isset($vars['bss_giftcard_selected_image'])) {
            $data['image_id'] = $vars['bss_giftcard_selected_image'];
        }
        return $data;
    }

    /**
     * Handle code status
     *
     * @param string $expiry
     * @return int
     */
    private function handleCodeStatus($expiry)
    {
        if ($expiry) {
            $gmtDate = $this->dateFactory->create()->gmtTimestamp();
            $expiry = $this->dateTime->formatDate($expiry);
            if (strtotime($expiry) < $gmtDate) {
                return Status::BSS_GC_STATUS_EXPIRED;
            }
        }

        return Status::BSS_GC_STATUS_ACTIVE;
    }

    /**
     * Generate random string
     *
     * @return string
     */
    private function generateRandomString()
    {
        return $this->random->getRandomString(
            self::CHARS_LENGTH,
            Random::CHARS_UPPERS
        );
    }

    /**
     * Generate random digits
     *
     * @return  string
     */
    private function generateRandomDigits()
    {
        return $this->random->getRandomString(
            self::CHARS_LENGTH,
            Random::CHARS_DIGITS
        );
    }

    /**
     * Replace pattern code
     *
     * @param   string $codeName
     * @param   string $string
     * @return  string
     */
    private function replacePatternCode(&$codeName, $string)
    {
        $stringCodeCount = $this->dynamicChar($codeName, $string);
        $count = 0;
        if ($stringCodeCount > 0) {
            while ($count <= $stringCodeCount) {
                if ($string == self::LETTER_CODE) {
                    $random = $this->generateRandomString();
                } else {
                    $random = $this->generateRandomDigits();
                }
                $codeName = preg_replace(
                    '/' . $string . '/',
                    $random,
                    $codeName,
                    1
                );
                $count++;
            }
        }
        return $codeName;
    }

    /**
     * Dynamic char
     *
     * @param   string $codeName
     * @param   string $string
     * @return  string
     */
    public function dynamicChar($codeName, $string)
    {
        return substr_count($codeName, $string);
    }

    /**
     * Load by code
     *
     * @param   string $code
     * @return  $this
     */
    public function loadByCode($code)
    {
        return $this->load($code, 'code');
    }

    /**
     * Validate
     *
     * @return  bool
     */
    public function validate()
    {
        if ($this->getCodeId()) {
            $time = $this->dateFactory->create()->gmtDate();
            $expiry = $this->getExpiryDay();
            if ((!$expiry || strtotime($time) < strtotime($expiry))
                && $this->getStatus() == Status::BSS_GC_STATUS_ACTIVE) {
                return true;
            }
        }
        return false;
    }

    /**
     * Load by email
     *
     * @param   string|null $email
     * @param   int|null $websiteId
     * @return  $this
     */
    public function loadByEmail($email = null, $websiteId = null)
    {
        if ($websiteId === null) {
            $websiteId = (int) $this->storeManager->getStore()->getWebsiteId();
        }
        if ($email === null) {
            $email = $this->customerSession->create()->getCustomer()->getEmail();
        }
        $data = $this->getCollection()->addFieldToFilter(
            'customer_email',
            $email
        )->addFieldToFilter(
            'website_id',
            $websiteId
        )->setOrder(
            'code_id',
            'DESC'
        );
        return $data;
    }

    /**
     * Update amount
     *
     * @param   string $code
     * @param   float $amount
     * @param   int $quoteId
     * @return  void
     */
    public function updateAmount($code, $amount, $quoteId)
    {
        $giftCard = $this->loadByCode($code);
        $balance = $giftCard->getValue() - $amount;
        if ($balance >= 0) {
            $giftCard->setValue($balance);
            if ($balance == 0) {
                $giftCard->setStatus(Status::BSS_GC_STATUS_USED);
            }

            try {
                $giftCard->save();
            } catch (\Exception $e) {
                $this->logger->critical($e);
            }

            $history = $this->historyFactory->create();
            $data = [
                'code_id' => $giftCard->getId(),
                'quote_id' => $quoteId,
                'amount' => $amount
            ];

            try {
                $history->setData($data)->save();
            } catch (\Exception $e) {
                $this->logger->critical($e);
            }
        }
    }

    /**
     * Get status label
     *
     * @return string
     */
    public function getStatusLabel()
    {
        $status = $this->getStatus();
        switch ($status) {
            case Status::BSS_GC_STATUS_ACTIVE:
                $result = __('Active');
                break;
            case Status::BSS_GC_STATUS_EXPIRED:
                $result = __('Expired');
                break;
            case Status::BSS_GC_STATUS_USED:
                $result = __('Used');
                break;
            default:
                $result = __('Inactive');
        }
        return $result;
    }

    /**
     * Update status
     *
     * @param   array $ids
     * @param   string $state
     * @return  $this
     */
    public function updateStatus($ids, $state)
    {
        $this->getResource()->updateStatus($ids, $state);
        return $this;
    }
}
