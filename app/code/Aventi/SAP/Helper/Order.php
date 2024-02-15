<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Helper;

use Exception;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DB\Transaction;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\Data\TransactionSearchResultInterfaceFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Status\HistoryRepository;
use Magento\Sales\Model\ResourceModel\Order\Status\History\CollectionFactory;

class Order extends AbstractHelper
{
    /**
     * @constructor
     *
     * @param Context $context
     * @param OrderRepositoryInterface $_orderRepository
     * @param CollectionFactory $_historyCollectionFactory
     * @param Configuration $_configuration
     * @param HistoryRepository $_historyRepository
     * @param Transaction $_transaction
     * @param TransactionSearchResultInterfaceFactory $paymentTransaction
     */
    public function __construct(
        Context                                                  $context,
        private readonly OrderRepositoryInterface                $_orderRepository,
        private readonly CollectionFactory                       $_historyCollectionFactory,
        private readonly Configuration                           $_configuration,
        private readonly HistoryRepository                       $_historyRepository,
        private readonly Transaction                             $_transaction,
        private readonly TransactionSearchResultInterfaceFactory $paymentTransaction
    ) {
        parent::__construct($context);
    }

    /**
     * GetStringProductForSAP
     * Generate the structure of product for SAP
     *
     * @param OrderInterface $orderEntity
     * @return array
     */
    private function getStringProductForSAP(OrderInterface $orderEntity): array
    {
        $products = [];
        $items = $orderEntity->getAllVisibleItems();

        /** @var OrderItemInterface $item */
        foreach ($items as $item) {
            $products[] = [
                'ItemCode' => $item->getSku(),
                'Quantity' => (int) $item->getQtyOrdered(),
                'Price' => (int) $item->getOriginalPrice(),
                'DiscountPercent' => $this->getPercentOfSaleIfApply($item),
                'WhsCode' => $this->_configuration->getWhsCode(),
            ];
        }

        return $products;
    }

    /**
     * GetPercentOfSaleIfApply
     *
     * @param OrderItemInterface $item
     * @return int
     */
    public function getPercentOfSaleIfApply(OrderItemInterface $item): int
    {
        $discountPercent = 0;
        $originalPrice = $item->getOriginalPrice(); #original_price
        $priceWithTax =  $item->getPriceInclTax(); #prince_incl_tax
        $discountAmount =  (int) $item->getDiscountAmount(); #discount_amount

        if ($discountAmount !== 0) {
            $discountPercent = $item->getDiscountPercent();
            if ($discountPercent == 0) {
                $priceWithDiscount = $originalPrice - $discountAmount;
                $discountPercent = (1 - ($priceWithDiscount / $originalPrice)) * 100;
            }
        } elseif ($originalPrice != $priceWithTax) {
            $discountPercent = (1 - ($priceWithTax / $originalPrice)) * 100;
        }

        return (int) $discountPercent;
    }

    /**
     * ProcessIteration
     *
     * @param array $statuses
     * @param $orderId
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function processIteration(array $statuses, $orderId): bool
    {
        $order = $this->_orderRepository->get($orderId);
        $iteration = 0;
        $historiesModel = $this->_historyCollectionFactory->create();
        $historiesModel->addFieldToFilter('entity_name', 'order');
        $historiesModel->addFieldToFilter('status', $statuses);
        $historiesModel->addFieldToFilter('parent_id', $orderId);
        $historiesModel->addFieldToFilter('comment', ['like' => '%Sincronizando%']);
        $historiesModel->load();

        foreach ($historiesModel as $history) {
            $iteration = (int) (preg_replace('/\D+/i', '', $history->getData('comment')));
            if ($iteration != 10) {
                $history->delete();
            }
        }

        foreach ($order->getStatusHistories() as $history) {
            $status = $history->getStatus();
            if ($status === 'error') {
                $this->_historyRepository->delete($history);
            }
        }

        $iteration++;

        if ($iteration == 10) {
            $order->addStatusToHistory(
                'syncing',
                sprintf('Sincronizando pedido con SAP Server (%s intento)', $iteration)
            );
            $order->addStatusToHistory('syncing', 'Número de intentos máximos superados');
            $this->_orderRepository->save($order);
            return true;
        } elseif ($iteration > 10) {
            $this->resetAttempts($order);
            return false;
        }

        $order->addStatusToHistory(
            'syncing',
            sprintf('Sincronizando pedido con SAP Server (%s intento)', $iteration)
        );
        $this->_orderRepository->save($order);

        return true;
    }

    /**
     * ProcessDataSAP
     *
     * @throws NoSuchEntityException
     * @throws LocalizedException
     */
    public function processDataSAP(OrderInterface $order): array
    {
        $products = $this->getStringProductForSAP($order);
        $customerInfo = $this->getStringCustomerInfoForSAP($order);
        $paymentTitle = $order->getPayment()->getMethodInstance()->getTitle();
        $paymentTransId = $this->getTransactionId($order);

        $userFields = [
            "U_medipagoe~" . $paymentTitle,
            "U_idtransaccion~" . $paymentTransId
        ];
        $userFields = trim(implode("|", $userFields));

        return [
            'TipoDocumento' => 17,
            'DocDueDate' => date_format(date_create($order->getCreatedAt()), 'm/d/Y'),
            'Serie' => $this->_configuration->getSerie(),
            'CamposUsuario' => $userFields,
            'CiudadS' => $order->getBillingAddress()->getCity(),
            'RegionS' => $this->getState($order->getShippingAddress()->getRegionId()),
            'DireccionS' => $order->getShippingAddress()->getStreet()[0],
            'Detalles' => $products,
            'Comments' => "",//$comments,
            'BusinessPartner' => $customerInfo,
            'Referencia' => $order->getIncrementId()
        ];
    }

    /**
     * GetFormattedRegion
     *
     * @throws LocalizedException
     */
    private function getFormattedRegion(?string $regionCode): string
    {
        $sapRegion = '';
        switch ($regionCode) {
            case 'CO-AMA':
                $sapRegion = '091';
                break;
            case 'CO-ANT':
                $sapRegion = '005';
                break;
            case 'CO-ARA':
                $sapRegion = '081';
                break;
            case 'CO-ATL':
                $sapRegion = '008';
                break;
            case 'CO-BOG':
                $sapRegion = '011';
                break;
            case 'CO-BOL':
                $sapRegion = '013';
                break;
            case 'CO-BOY':
                $sapRegion = '015';
                break;
            case 'CO-CAL':
                $sapRegion = '017';
                break;
            case 'CO-CAQ':
                $sapRegion = '018';
                break;
            case 'CO-CAS':
                $sapRegion = '085';
                break;
            case 'CO-CAU':
                $sapRegion = '019';
                break;
            case 'CO-CES':
                $sapRegion = '020';
                break;
            case 'CO-CHO':
                $sapRegion = '027';
                break;
            case 'CO-COR':
                $sapRegion = '023';
                break;
            case 'CO-CUN':
                $sapRegion = '025';
                break;
            case 'CO-GUA':
                $sapRegion = '094';
                break;
            case 'CO-GUV':
                $sapRegion = '095';
                break;
            case 'CO-HUL':
                $sapRegion = '041';
                break;
            case 'CO-LAG':
                $sapRegion = '044';
                break;
            case 'CO-MAG':
                $sapRegion = '047';
                break;
            case 'CO-MET':
                $sapRegion = '050';
                break;
            case 'CO-NAR':
                $sapRegion = '052';
                break;
            case 'CO-NSA':
                $sapRegion = '054';
                break;
            case 'CO-PUT':
                $sapRegion = '086';
                break;
            case 'CO-QUI':
                $sapRegion = '063';
                break;
            case 'CO-RIS':
                $sapRegion = '066';
                break;
            case 'CO-SAP':
                $sapRegion = '088';
                break;
            case 'CO-SAN':
                $sapRegion = '068';
                break;
            case 'CO-SUC':
                $sapRegion = '070';
                break;
            case 'CO-TOL':
                $sapRegion = '073';
                break;
            case 'CO-VAC':
                $sapRegion = '076';
                break;
            case 'CO-VAU':
                $sapRegion = '097';
                break;
            case 'CO-VID':
                $sapRegion = '099';
                break;
            default:
                throw new LocalizedException(__('Region code not defined.'));
        }

        return $sapRegion;
    }

    /**
     * ResetAttempts
     *
     * @throws CouldNotDeleteException
     */
    private function resetAttempts(OrderInterface $order): void
    {
        foreach ($order->getStatusHistories() as $history) {
            $status = $history->getStatus();
            if ($status === 'syncing') {
                $this->_historyRepository->delete($history);
            }
        }
    }

    /**
     * CreateInvoice
     *
     * @throws LocalizedException
     * @throws Exception
     */
    public function createInvoice($order): void
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $paymentMethod = $order->getPayment()->getMethod();

        if (($paymentMethod === "banktransfer" || $paymentMethod === "cashondelivery") && !$order->hasInvoices()) {
            if (!$order->canInvoice()) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __('The order does not allow an invoice to be created.')
                );
            }

            $invoiceService = $objectManager->get(\Magento\Sales\Model\Service\InvoiceService::class);
            $invoice = $invoiceService->prepareInvoice($order);
            $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE);
            $invoice->register();
            $invoice->getOrder()->setCustomerNoteNotify(false);
            $invoice->getOrder()->setIsInProcess(true);
            $invoice->pay();
            $invoice->save();
            $transactionSave = $this->_transaction->addObject(
                $invoice
            )->addObject(
                $invoice->getOrder()
            );
            $transactionSave->save();
        }
    }

    /**
     * GetPaymentInfo
     *
     * @param OrderPaymentInterface|null $orderPayment
     * @return array
     * @throws LocalizedException
     */
    private function getPaymentInfo(?OrderPaymentInterface $orderPayment): array
    {
        $paymentInfo = [
            "paymentMethod" => 'N/A',
            "paymentInfo" => 'N/A'
        ];

        if ($orderPayment->getMethodInstance()->getCode() == 'mercadopago_basic') {
            $paymentResponse = $orderPayment->getAdditionalInformation("paymentResponse");
            $paymentInfo = [
                "paymentMethod" => $paymentResponse['payment_method_id'],
                "paymentInfo" => "Nro Transaccion: " . $paymentResponse['id']
            ];
        } elseif ($orderPayment->getMethodInstance()->getCode() == 'cashondelivery') {
            $paymentInfo = [
                "paymentMethod" => 'Pago contra-entrega',
                "paymentInfo" => 'Pago contra-entrega'
            ];
        }

        return $paymentInfo;
    }

    /**
     * GetStringCustomerInfoForSAP
     *
     * @param $orderEntity
     * @return array
     */
    public function getStringCustomerInfoForSAP($orderEntity): array
    {
        $this->_logger->debug(json_encode($orderEntity->getShippingAddress()->toArray()));
        $identification = $orderEntity->getShippingAddress()->getVatId();
        $firstName = str_replace('Ñ', 'N', strtoupper($orderEntity->getShippingAddress()->getFirstName()));
        $lastName = str_replace('Ñ', 'N', strtoupper($orderEntity->getShippingAddress()->getLastName()));
        $fLastName = explode(' ', $lastName);
        $telephone = $orderEntity->getShippingAddress()->getTelephone();
        $email = $orderEntity->getShippingAddress()->getEmail();
        $city  = $orderEntity->getShippingAddress()->getCity();
//        $state = $this->getState($orderEntity->getShippingAddress()->getStateId());
        $address = strtoupper($orderEntity->getShippingAddress()->getStreet()[0]);
        $postalCode = $orderEntity->getShippingAddress()->getPostcode();

//        $userFieldsAddress = trim(implode("|", $userFieldsAddress));
        $userFieldsAddress =  "";
        $userFields = "";

        return [
            "LicTradNum" => $identification,
            "CardName" => $lastName . ' ' . $firstName,
            "CardFName" => "",
            "ListNum" => $this->_configuration->getListNum(),
            "GroupCode" => $this->_configuration->getGroupCode(),
            "Phone1" => $telephone,
            "Email" => $email,
            "Address2S" => $address,
            "CountryS" => "CO",
            "StreetS" => $address,
            "CityS" => $city,
            "ZipCode" => $postalCode,
            "StateS" => "",
//            "Address2B":"Bogotá",
//            "CountryB":"",
//            "StreetB":"",
//            "CityB":"Bogotá",
//            "StateB":"",
            "CamposUsuario" => $userFields,
            "CamposUsuarioDireccionShip" => $userFieldsAddress,
            "CamposUsuarioDireccionBill" => ""
        ];
    }

    /**
     * GetState
     *
     * @param $mState
     * @return string
     */
    public function getState($mState): string
    {
        $sapState = $mState;
        switch ($sapState) {
            case "731":
                $sapState = '001';
                break;
            case "747":
                $sapState = '002';
                break;
            case "721":
                $sapState = '003';
                break;
        }

        return $sapState;
    }

    /**
     * GetTransactionId
     *
     * @param OrderInterface $order
     * @return string
     */
    public function getTransactionId(OrderInterface $order): string
    {
        $transactionId = '';
        $transaction = $this->paymentTransaction->create()->addOrderIdFilter($order->getId());
        foreach ($transaction->getItems() as $invoice) {
            $transactionId = $invoice->getTxnId();
        }

        return $transactionId;
    }
}
