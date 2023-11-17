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

use Bss\GiftCard\Model\Pattern\CodeFactory;
use Magento\Checkout\Model\SessionFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\Escaper;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Bss\GiftCard\Model\ResourceModel\GiftCard\QuoteFactory;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Customer\Model\SessionFactory as CustomerSession;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Store\Model\StoreManagerInterface;
use Bss\GiftCard\Helper\Data as GiftCardData;
use Magento\Checkout\Model\SessionFactory as CheckoutSession;

/**
 * Class gift card
 * Bss\GiftCard\Model
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GiftCard implements \Bss\GiftCard\Api\GiftCardInterface
{
    public const CODE_INPUT_LOCK_TIME = 300; //5 minutes: Time lock to enter code (after attempt exceed times)

    /**
     * @var CodeFactory
     */
    private $codeFactory;

    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var QuoteFactory
     */
    private $giftCardQuoteFactory;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var DateTimeFactory
     */
    private $dateFactory;

    /**
     * @var GiftCardData
     */
    private $giftCardHelper;

    /**
     * @var CheckoutSession
     */
    private $checkoutSession;

    /**
     * @var MaskedQuoteIdToQuoteIdInterface
     */
    private $maskedQuoteIdToQuoteId;

    /**
     * GiftCard constructor.
     *
     * @param CodeFactory $codeFactory
     * @param PriceCurrencyInterface $priceCurrency
     * @param Escaper $escaper
     * @param TimezoneInterface $localeDate
     * @param CartRepositoryInterface $quoteRepository
     * @param QuoteFactory $giftCardQuoteFactory
     * @param CustomerSession $customerSession
     * @param DateTimeFactory $dateFactory
     * @param StoreManagerInterface $storeManager
     * @param GiftCardData $giftCardHelper
     * @param CheckoutSession $checkoutSession
     * @param MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        CodeFactory $codeFactory,
        PriceCurrencyInterface $priceCurrency,
        Escaper $escaper,
        TimezoneInterface $localeDate,
        CartRepositoryInterface $quoteRepository,
        QuoteFactory $giftCardQuoteFactory,
        CustomerSession $customerSession,
        DateTimeFactory $dateFactory,
        StoreManagerInterface $storeManager,
        GiftCardData $giftCardHelper,
        CheckoutSession $checkoutSession,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId
    ) {
        $this->codeFactory = $codeFactory;
        $this->priceCurrency = $priceCurrency;
        $this->escaper = $escaper;
        $this->localeDate = $localeDate;
        $this->giftCardQuoteFactory = $giftCardQuoteFactory;
        $this->quoteRepository = $quoteRepository;
        $this->customerSession = $customerSession;
        $this->dateFactory = $dateFactory;
        $this->storeManager = $storeManager;
        $this->giftCardHelper = $giftCardHelper;
        $this->checkoutSession = $checkoutSession;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
    }

    /**
     * @inheritDoc
     */
    public function checkCode($code)
    {
        $result = [];
        $result['status'] = false;
        try {
            $codeModel = $this->codeFactory->create()->loadByCode($code);
            if ($codeModel->getCodeId()) {
                $result['data'] = [
                    [
                        'label' => __('Code'),
                        'value' => $codeModel->getCode()
                    ], [
                        'label' => __('Origin Value'),
                        'value' => $this->priceCurrency->convertAndFormat($codeModel->getOriginValue(), false)
                    ], [
                        'label' => __('Current Value'),
                        'value' => $this->priceCurrency->convertAndFormat($codeModel->getValue(), false)
                    ], [
                        'label' => __('Status'),
                        'value' => $codeModel->getStatusLabel()
                    ], [
                        'label' => __('Expire At'),
                        'value' => $codeModel->getExpiryDay() ? $this->localeDate->formatDate(
                            $codeModel->getExpiryDay(),
                            \IntlDateFormatter::MEDIUM
                        ) : __("Unlimited")
                    ]
                ];
                $result['status'] = true;
            } else {
                $result['data'] = $this->escaper->escapeHtml(__(
                    'The gift card code "%1" is not valid.',
                    $code
                ));
            }
        } catch (\Exception $e) {
            throw new NoSuchEntityException(__('The gift card code "%1" is not valid.', $code));
        }

        return ['result' => $result];
    }

    /**
     * Quest remove
     *
     * @param string $cartId
     * @param int $giftCardQuoteId
     * @return array|mixed
     * @throws CouldNotDeleteException
     * @throws NoSuchEntityException
     */
    public function guestRemove($cartId, $giftCardQuoteId)
    {
        $quote = $this->getQuoteFromQuoteIdParam($cartId);
        if (!$quote->getItemsCount()) {
            throw new NoSuchEntityException(__('Cart %1 doesn\'t contain products', $cartId));
        }
        try {
            return $this->removeGiftCodeFromQuote($quote, $giftCardQuoteId);
        } catch (CouldNotDeleteException $e) {
            throw $e;
        }
    }

    /**
     * @inheritDoc
     */
    public function removeGiftCodeByCustomer($customerId, $giftCardQuoteId)
    {
        try {
            $quote = $this->quoteRepository->getActiveForCustomer($customerId);
            if (!$quote->getItemsCount()) {
                throw new NoSuchEntityException(__('Customer cart doesn\'t contain products'));
            }
            return $this->removeGiftCodeFromQuote($quote, $giftCardQuoteId);
        } catch (CouldNotDeleteException $e) {
            throw $e;
        }
    }

    /**
     * Remove applied gift code in cart
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @param int $giftCodeId
     * @return array
     * @throws CouldNotDeleteException
     */
    protected function removeGiftCodeFromQuote($quote, $giftCodeId)
    {
        try {
            $quote->getShippingAddress()->setCollectShippingRates(true);
            $giftCardQuote = $this->giftCardQuoteFactory->create();
            $giftCardQuote->removeGiftCardQuote($giftCodeId, $quote->getId());
            $this->quoteRepository->save($quote->collectTotals());
            $this->checkoutSession->create()->unsBssgiftcardaccountTotalAmount();
            return $giftCardQuote->getGiftCardCode($quote);
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            throw new CouldNotDeleteException(new \Magento\Framework\Phrase($e->getMessage()));
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Could not delete gift card code'));
        }
    }

    /**
     * Apply
     *
     * @param string $cartId
     * @param string $giftCardCode
     * @return array|mixed
     * @throws CouldNotSaveException
     * @throws NoSuchEntityException
     */
    public function apply($cartId, $giftCardCode)
    {
        $quote = $this->getQuoteFromQuoteIdParam($cartId);
        if (!$quote->getItemsCount()) {
            throw new NoSuchEntityException(__('Cart %1 doesn\'t contain products', $cartId));
        }
        return $this->applyGiftCode($quote, $giftCardCode);
    }

    /**
     * @inheritDoc
     */
    public function applyGiftCodeByCustomer($customerId, $giftCardCode)
    {
        $quote = $this->quoteRepository->getActiveForCustomer($customerId);
        if (!$quote->getItemsCount()) {
            throw new NoSuchEntityException(__('Customer cart doesn\'t contain products'));
        }
        return $this->applyGiftCode($quote, $giftCardCode);
    }

    /**
     * Apply giftcode to cart
     *
     * @param \Magento\Quote\Api\Data\CartInterface $quote
     * @param string $giftCardCode
     *
     * @return array
     *
     * @throws CouldNotSaveException
     */
    protected function applyGiftCode($quote, $giftCardCode)
    {
        try {
            $giftCardCode = trim($giftCardCode);
            /** @var  \Magento\Quote\Model\Quote $quote */
            $customerSession = $this->customerSession->create();
            $turn = (int)$customerSession->getBssGcTurn();

            $timeLeft = $this->validateCustomer();
            if ($timeLeft > 0) {
                $mess = 'You have entered incorrect code too many times. Please try again after %1 seconds.';
                $seconds = $this->escaper->escapeHtml($timeLeft);
                throw new CouldNotSaveException(
                    __($mess, $seconds)
                );
            }
            $quote->getShippingAddress()->setCollectShippingRates(true);
            $giftCardQuote = $this->giftCardQuoteFactory->create();
            $giftCardCodes = $giftCardQuote->getGiftCardCode($quote);
            if (!empty($giftCardCodes)
                && !empty($giftCardQuote->validateQuote($quote, $giftCardCode))) {
                $customerSession->setBssGcTurn($turn + 1);
                throw new CouldNotSaveException(
                    __('You have already used this gift card code.')
                );
            }
            $code = $this->codeFactory->create();
            $code->loadByCode($giftCardCode);
            $websiteId = $this->storeManager->getStore($quote->getStoreId())->getWebsiteId();

            if ($code->getId() &&
                $code->validate() &&
                ($code->getWebsiteId() == 0 || $websiteId == $code->getWebsiteId())
            ) {
                $giftCardQuote->setGiftCardCode($quote, $code);
            } else {
                $customerSession->setBssGcTurn($turn + 1);
                throw new CouldNotSaveException(__('Gift card code is not valid'));
            }

            $this->quoteRepository->save($quote->collectTotals());
            return $giftCardQuote->getGiftCardCode($quote);
        } catch (CouldNotSaveException $e) {
            throw $e;
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Could not apply gift card code'));
        }
    }

    /**
     * Check Code entered attempts exceeded
     *
     * @return int time (seconds) left to enter code again
     */
    public function validateCustomer()
    {
        $customerSession = $this->customerSession->create();
        $time = $this->dateFactory->create()->gmtDate();
        $lastTime = $customerSession->getBssGcUpdate();
        $lastTime = $lastTime ? $lastTime : $time;
        $expiryTime = strtotime($time) - strtotime($lastTime);
        if ($expiryTime > self::CODE_INPUT_LOCK_TIME) {
            $customerSession->setBssGcTurn(0);
            $customerSession->setBssGcUpdate($time);
        }
        $maxTime = (int)$this->giftCardHelper->getConfigSetting('max_time_limit');
        if ($maxTime && $customerSession->getBssGcTurn() > $maxTime) {
            return self::CODE_INPUT_LOCK_TIME - $expiryTime;
        }
        return 0;
    }

    /**
     * Get active quote
     *
     * @param int|string $quoteId
     * @return \Magento\Quote\Api\Data\CartInterface
     * @throws NoSuchEntityException
     */
    protected function getQuoteFromQuoteIdParam($quoteId)
    {
        $qId = (int) $quoteId;
        if ($qId == 0) {
            $quoteId = $this->maskedQuoteIdToQuoteId->execute($quoteId);
        }
        return $this->quoteRepository->getActive($quoteId);
    }
}
