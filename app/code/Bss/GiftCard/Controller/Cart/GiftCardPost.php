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

namespace Bss\GiftCard\Controller\Cart;

use Bss\GiftCard\Model\GiftCardFactory;
use Bss\GiftCard\Model\Pattern\CodeFactory;
use Bss\GiftCard\Model\ResourceModel\GiftCard\QuoteFactory;
use Magento\Checkout\Controller\Cart as CheckoutCart;
use Magento\Checkout\Helper\Cart;
use Magento\Checkout\Model\Cart as CheckoutCartModel;
use Magento\Checkout\Model\Session;
use Magento\Customer\Model\SessionFactory as CustomerSession;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Escaper;
use Magento\Framework\Registry;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class gift card post
 *
 * Bss\GiftCard\Controller\Cart
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 */
class GiftCardPost extends CheckoutCart
{
    /**
     * @var CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var CodeFactory
     */
    private $codeFactory;

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var QuoteFactory
     */
    private $giftCardQuoteFactory;

    /**
     * @var GiftCardFactory
     */
    private $giftCardFactory;

    /**
     * @var CustomerSession
     */
    private $customerSession;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LoggerInterface
     */
    public $logger;

    /**
     * @param Context $context
     * @param ScopeConfigInterface $scopeConfig
     * @param Session $checkoutSession
     * @param Validator $formKeyValidator
     * @param CheckoutCartModel $cart
     * @param CartRepositoryInterface $quoteRepository
     * @param CodeFactory $codeFactory
     * @param Escaper $escaper
     * @param Registry $registry
     * @param QuoteFactory $giftCardQuoteFactory
     * @param GiftCardFactory $giftCardFactory
     * @param CustomerSession $customerSession
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $logger
     * @codeCoverageIgnore
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        Context $context,
        ScopeConfigInterface $scopeConfig,
        Session $checkoutSession,
        Validator $formKeyValidator,
        CheckoutCartModel $cart,
        CartRepositoryInterface $quoteRepository,
        CodeFactory $codeFactory,
        Escaper $escaper,
        Registry $registry,
        QuoteFactory $giftCardQuoteFactory,
        GiftCardFactory $giftCardFactory,
        CustomerSession $customerSession,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart
        );
        $this->quoteRepository = $quoteRepository;
        $this->codeFactory = $codeFactory;
        $this->escaper = $escaper;
        $this->registry = $registry;
        $this->giftCardQuoteFactory = $giftCardQuoteFactory;
        $this->giftCardFactory = $giftCardFactory;
        $this->customerSession = $customerSession;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * Execute
     *
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        if (!$this->_formKeyValidator->validate($this->getRequest())) {
            $this->messageManager->addErrorMessage(
                __('We cannot apply the gift card code.')
            );
            return $this->_goBack();
        }
        $quote = $this->cart->getQuote();
        $giftCardCode = trim($this->getRequest()->getParam('bss_giftcard_code'));
        $cartQuote = $this->cart->getQuote();
        $customerSession = $this->customerSession->create();
        $turn = (int) $customerSession->getBssGcTurn();
        $giftCardModel = $this->giftCardFactory->create();
        $websiteId = $this->storeManager->getStore($quote->getStoreId())->getWebsiteId();

        $codeLength = strlen($giftCardCode);
        if (!$codeLength) {
            $this->messageManager->addErrorMessage(
                __('Please enter gift card code.')
            );
            return $this->_goBack();
        }

        $giftCardQuote = $this->giftCardQuoteFactory->create();
        $giftCardCodes = $giftCardQuote->getGiftCardCode($cartQuote);
        $timeLeft = $giftCardModel->validateCustomer();
        if ($timeLeft > 0) {
            $mess = 'You have entered incorrect code too many times. Please try again after %1 seconds.';
            $seconds = $this->escaper->escapeHtml($timeLeft);
            $this->messageManager->addErrorMessage(
                __($mess, $seconds)
            );
            return $this->_goBack();
        }
        if (!empty($giftCardCodes)
            && !empty($giftCardQuote->validateQuote($cartQuote, $giftCardCode))) {
            $customerSession->setBssGcTurn($turn + 1);
            $this->messageManager->addErrorMessage(
                __('You have already used this gift card code.')
            );
            return $this->_goBack();
        }

        try {
            $isCodeLengthValid = $codeLength && $codeLength <= Cart::COUPON_CODE_MAX_LENGTH;
            $itemsCount = $cartQuote->getItemsCount();
            $code = $this->codeFactory->create();
            $code->loadByCode($giftCardCode);
            $codeValid = $isCodeLengthValid && $code->getId() && $code->validate();
            if (!$itemsCount) {
                if ($codeValid && ($code->getWebsiteId() == 0 || $websiteId == $code->getWebsiteId())) {
                    $quote = $this->_checkoutSession->getQuote();
                    $this->giftCardQuoteFactory->create()->setGiftCardCode($quote, $code);
                    $quote->save();
                    $this->messageManager->addSuccessMessage(
                        __('You used gift card code "%1".', $this->escaper->escapeHtml($giftCardCode))
                    );
                } else {
                    $customerSession->setBssGcTurn($turn + 1);
                    $this->messageManager->addErrorMessage(
                        __('The gift card code "%1" is not valid.', $this->escaper->escapeHtml($giftCardCode))
                    );
                }
            } else {
                if ($codeValid) {
                    $cartQuote->getShippingAddress()->setCollectShippingRates(true);
                    $this->giftCardQuoteFactory->create()->setGiftCardCode($cartQuote, $code);
                    $cartQuote->collectTotals();
                    $this->quoteRepository->save($cartQuote);

                    $this->messageManager->addSuccessMessage(
                        __('You used gift card code "%1".', $this->escaper->escapeHtml($giftCardCode))
                    );
                } else {
                    $customerSession->setBssGcTurn($turn + 1);
                    $this->messageManager->addErrorMessage(
                        __('The gift card code "%1" is not valid.', $this->escaper->escapeHtml($giftCardCode))
                    );
                }
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage(
                $e->getMessage()
            );
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(
                __('We cannot apply the gift card code.')
            );
            $this->logger->critical($e);
        }

        return $this->_goBack();
    }
}
