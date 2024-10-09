<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\SAP\Helper;

use Exception;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DB\Transaction;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filter\RemoveAccents;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Sales\Api\Data\OrderPaymentInterface;
use Magento\Sales\Api\Data\TransactionSearchResultInterfaceFactory;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order\Status\HistoryRepository;
use Magento\Sales\Model\ResourceModel\Order\Status\History\CollectionFactory;
use Magento\Store\Api\WebsiteRepositoryInterface;

/**
 * @class Order
 */
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
     * @param ProductRepositoryInterface $_productRepository
     * @param WebsiteRepositoryInterface $_websiteRepository
     * @param RemoveAccents $removeAccents
     */
    public function __construct(//NOSONAR
        Context                                                  $context,
        private readonly OrderRepositoryInterface                $_orderRepository,
        private readonly CollectionFactory                       $_historyCollectionFactory,
        private readonly Configuration                           $_configuration,
        private readonly HistoryRepository                       $_historyRepository,
        private readonly Transaction                             $_transaction,
        private readonly TransactionSearchResultInterfaceFactory $paymentTransaction,
        private readonly ProductRepositoryInterface              $_productRepository,
        private readonly WebsiteRepositoryInterface              $_websiteRepository,
        private readonly RemoveAccents                           $removeAccents
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
                'Quantity' => (int)$item->getQtyOrdered(),
                'Price' => (int)$item->getOriginalPrice(),
                'DiscountPercent' => $this->getPercentOfSaleIfApply($item),
                'WhsCode' => $this->_configuration->getWhsCode(),
                'OcrCode' => $this->getOcrCode($item),
                'OcrCode2' => $this->_configuration->getOcrCode2(),
                'OcrCode3' => $this->_configuration->getOcrCode3()
            ];
        }

        return $products;
    }

    /**
     * Get OcrCode
     *
     * @param $item
     * @return int|void|null
     */
    private function getOcrCode($item)
    {
        $productId = $item->getProductId();
        try {
            $product = $this->_productRepository->getById($productId);
            $websites = $product->getWebsiteIds();

            if (!empty($websites)) {
                $websiteId = $websites[0];
                $website = $this->_websiteRepository->getById($websiteId);
                $websiteCode = $website->getCode();

                return $this->getOcrCodePerWebsite($websiteCode);
            }
        } catch (NoSuchEntityException $e) {
            return $this->_logger->error($e->getMessage());
        }
    }

    /**
     * Get OcrCode per website
     *
     * @param $websiteCode
     * @return int
     */
    public function getOcrCodePerWebsite($websiteCode): int
    {
        return match ($websiteCode) {
            'healthy_sports' => 2101,
            'base' => 2102,
            'nutrivita' => 2104,
            default => 2103
        };
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
        $discountAmount = (int)$item->getDiscountAmount(); #discount_amount

        if ($discountAmount !== 0) {
            $discountPercent = $item->getDiscountPercent();
            if ($discountPercent == 0) {
                $priceWithDiscount = $originalPrice - $discountAmount;
                $discountPercent = (1 - ($priceWithDiscount / $originalPrice)) * 100;
            }
        }

        return (int)$discountPercent;
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
            $iteration = (int)(preg_replace('/\D+/i', '', $history->getData('comment')));
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
     * @param OrderInterface $order
     * @return array
     */
    public function processDataSAP(OrderInterface $order): array
    {
        $products = $this->getStringProductForSAP($order);
        $customerInfo = $this->getStringCustomerInfoForSAP($order);
        $paymentTitle = $this->getPaymentTitle($order);
        $paymentTransId = $this->getTransactionId($order);

        $userFields = [
            "U_medipagoe~" . $paymentTitle,
            "U_idtransaccion~" . $paymentTransId
        ];
        $userFields = trim(implode("|", $userFields));
        $slpCode = $this->_configuration->getSlpCode();

        $addressComplement = isset($order->getShippingAddress()->getStreet()[1]) ?
            strtoupper($order->getShippingAddress()->getStreet()[1]) : '';
        $comment = "Complemento Dir: $addressComplement | Metodo de pago: $paymentTitle";

        return [
            'TipoDocumento' => 17,
            'CardCode' => "CN",
            'DocDueDate' => date_format(date_create($order->getCreatedAt()), 'm/d/Y'),
            "SlpCode" => $slpCode,
            'Serie' => $this->_configuration->getSerie(),
            'CamposUsuario' => $userFields,
            'CiudadS' => $order->getBillingAddress()->getCity(),
            'RegionS' => $this->getState($order->getShippingAddress()->getRegionId()),
            'DireccionS' => $order->getShippingAddress()->getStreet()[0],
            'Detalles' => $products,
            'Comments' => $comment,
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
     * GetState
     *
     * @param $mState
     * @return string
     */
    public function getState($mState): string
    {
        return match ($mState) {
            "731" => '001',
            "747" => '002',
            "721" => '003',
            default => $mState,
        };
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
        $identification = $orderEntity->getShippingAddress()->getVatId();
        $firstName = $this->removeAccents->filter(strtoupper($orderEntity->getShippingAddress()->getFirstName()));
        $lastName = $this->removeAccents->filter(strtoupper($orderEntity->getShippingAddress()->getLastName()));
        $telephone = $orderEntity->getShippingAddress()->getTelephone();
        $email = $orderEntity->getShippingAddress()->getEmail();
        $city = $orderEntity->getShippingAddress()->getCity();
        $address = strtoupper($orderEntity->getShippingAddress()->getStreet()[0]);
        $postalCode = $orderEntity->getShippingAddress()->getPostcode();
        $userFields = $this->getCustomerUserFields($orderEntity->getShippingAddress());

        return [
            "LicTradNum" => $identification,
            "CardName" => strtoupper($lastName . ' ' . $firstName),
            "CardFName" => "",
            "ListNum" => $this->_configuration->getListNum(),
            "GroupCode" => $this->_configuration->getGroupCode(),
            "SlpCode" => $this->_configuration->getSlpCode(),
            "Phone1" => $telephone,
            "Email" => $email,
            "Address2S" => "",
            "CountryS" => "CO",
            "StreetS" => $address,
            "CityS" => $city,
            "ZipCodeS" => $postalCode,
            "StateS" => "",
            "Address2B" => "",
            "CountryB" => "CO",
            "StreetB" => $address,
            "CityB" => $city,
            "ZipCodeB" => $postalCode,
            "StateB" => "",
            "Territory" => $this->_configuration->getTerritory(),
            "CamposUsuario" => $userFields,
            "CamposUsuarioDireccionShip" => "U_HBT_MunMed~$postalCode|U_HBT_DirMM~N",
            "CamposUsuarioDireccionBill" => "U_HBT_MunMed~$postalCode|U_HBT_DirMM~Y"
        ];
    }

    /**
     * GetTransactionId
     *
     * @param OrderInterface $order
     * @return ?string
     */
    public function getTransactionId(OrderInterface $order): ?string
    {
        $transactionId = '';
        if ($order->getPayment()->getMethod() === 'sample_gateway') {
            $transactionId = $order->getPayment()->getAdditionalInformation("reference");
        } else {
            $transaction = $this->paymentTransaction->create()->addOrderIdFilter($order->getId());
            foreach ($transaction->getItems() as $invoice) {
                $transactionId = $invoice->getTxnId();
            }
        }

        return $transactionId;
    }

    /**
     * GetPaymentTitle
     *
     * @param OrderInterface $order
     * @return string
     */
    public function getPaymentTitle(OrderInterface $order): string
    {
        try {
            return $order->getPayment()->getMethodInstance()->getTitle();
        } catch (LocalizedException $e) {
            $this->_logger->error($e->getMessage());
            return '';
        }
    }

    /**
     * GetDocumentType
     *
     * @param $doc
     * @return int
     */
    public function getDocumentType($doc): int
    {
        return match ($doc) {
            "CC" => 13,
            "CE" => 22,
            "RUT" => 31,
            default => $doc
        };
    }

    /**
     * GetCustomerUserFields
     *
     * @param $address
     * @return string
     */
    public function getCustomerUserFields($address): string
    {
        $lastname = explode(
            " ",
            strtoupper(str_replace('Ñ', 'N', $address->getLastname()))
        );
        $formatUserFields = "U_HBT_ConsumFinal~Y|U_regional1~R810|U_centroc1~20263|U_HBT_RegTrib~RS";
        $userFileds = [
            "U_HBT_TipDoc" => $this->getDocumentType($address->getFax()),
            "U_HBT_ActEco" => $address->getFax() === "RUT" ? "0010" : null,
            "U_HBT_MunMed" => $address->getPostcode(),
            "U_HBT_TipEnt" => $address->getSuffix() === "Natural" ? "1" : "2",
            "U_HBT_Nombres" => strtoupper(str_replace('Ñ', 'N', $address->getFirstName())),
            "U_HBT_Apellido1" => $lastname[0],
            "U_HBT_Apellido2" => isset($lastname[1]) ? $lastname[1] : '',
            "U_HBT_Nacional" => $address->getFax() === "CE" ? 2 : 1,
            "U_HBT_TipExt" => $address->getFax() === "CE" ? 1 : 0,
            "U_HBT_RegFis" => 49,
            "U_HBT_MedPag" => "ZZZ",
            "U_HBT_MailRecep_FE" => $address->getEmail(),
            "U_HBT_InfoTrib" => $address->getSuffix() === "Natural" ? "ZZ" : $address->getCompany()
        ];

        foreach ($userFileds as $field => $value) {
            $formatUserFields .= "|" . $field . "~" . $value;
        }

        return $formatUserFields;
    }
}
