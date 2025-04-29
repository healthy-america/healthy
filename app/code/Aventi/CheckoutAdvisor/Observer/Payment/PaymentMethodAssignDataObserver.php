<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\CheckoutAdvisor\Observer\Payment;

use Magento\Framework\App\State;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Model\QuoteRepository;
use Magento\Webapi\Controller\Rest\InputParamsResolver;

class PaymentMethodAssignDataObserver implements ObserverInterface
{
    /**
     * @var InputParamsResolver
     */
    protected InputParamsResolver $_inputParamsResolver;

    /**
     * @var QuoteRepository
     */
    protected QuoteRepository $_quoteRepository;

    /**
     * @var State
     */
    protected State $_state;

    public function __construct(
        InputParamsResolver $inputParamsResolver,
        QuoteRepository $quoteRepository,
        State $state
    ) {
        $this->_inputParamsResolver = $inputParamsResolver;
        $this->_quoteRepository = $quoteRepository;
        $this->_state = $state;
    }

    /**
     * Assign payment method data
     *
     * @param Observer $observer
     * @throws LocalizedException
     */
    public function execute(Observer $observer)
    {
        $inputParams = $this->_inputParamsResolver->resolve();
        if ($this->_state->getAreaCode() != \Magento\Framework\App\Area::AREA_ADMINHTML) {
            foreach ($inputParams as $inputParam) {
                if ($inputParam instanceof \Magento\Quote\Model\Quote\Payment) {
                    $paymentData = $inputParam->getData('additional_data');
                    $paymentOrder = $observer->getEvent()->getPayment();
                    $order = $paymentOrder->getOrder();
                    $quote = $this->_quoteRepository->get($order->getQuoteId());
                    $paymentQuote = $quote->getPayment();
                    if (isset($paymentData['payment_advisor'])) {
                        $paymentQuote->setData('payment_advisor', $paymentData['payment_advisor']);
                        $paymentQuote->setData('advisor', $paymentData['payment_advisor']);
                        $paymentOrder->setData('payment_advisor', $paymentData['payment_advisor']);
                        $paymentOrder->setData('advisor', $paymentData['payment_advisor']);
                    }
                }
            }
        }
    }
}
