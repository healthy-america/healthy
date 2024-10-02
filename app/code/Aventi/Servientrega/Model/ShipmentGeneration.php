<?php
/**
 * Copyright © Aventi SAS All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Aventi\Servientrega\Model;

use Aventi\Servientrega\Helper\Configuration;
use Aventi\Servientrega\Helper\WebService;
use Exception;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\UrlInterface;
use Magento\InventoryApi\Api\GetSourcesAssignedToStockOrderedByPriorityInterface;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;
use Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Model\Convert\Order as OrderConverter;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Shipment;
use Magento\Sales\Model\Order\Shipment\TrackFactory;
use Magento\Sales\Model\OrderRepository;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Shipping\Model\ShipmentNotifier;
use Psr\Log\LoggerInterface;

/**
 * @class ShipmentGeneration
 */
class ShipmentGeneration
{

    /**
     * Constructor
     *
     * @param LoggerInterface $_logger
     * @param WebService $_webService
     * @param ShipmentNotifier $_shipmentNotifier
     * @param Order $_order
     * @param CollectionFactory $_orderCollectionFactory
     * @param OrderConverter $_shipment
     * @param TrackFactory $_trackFactory
     * @param Configuration $_configuration
     * @param DirectoryList $_fileSystemDir
     * @param File $_fileSystem
     * @param UrlInterface $_url
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param OrderRepository $orderRepository
     * @param StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver
     * @param GetSourcesAssignedToStockOrderedByPriorityInterface $getSourcesAssignedToStockOrderedByPriority
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     * @param ManagerInterface $_eventManager
     */
    public function __construct(//NOSONAR
        private readonly LoggerInterface                                    $_logger,
        private readonly WebService                                         $_webService,
        private readonly ShipmentNotifier                                   $_shipmentNotifier,
        private readonly Order                                              $_order,
        private readonly CollectionFactory                                  $_orderCollectionFactory,
        private readonly OrderConverter                                      $_shipment,
        private readonly TrackFactory                                        $_trackFactory,
        private readonly Configuration                                       $_configuration,
        protected DirectoryList                                              $_fileSystemDir,
        private readonly File                                                $_fileSystem,
        protected UrlInterface                                               $_url,
        private readonly ShipmentRepositoryInterface                         $shipmentRepository,
        private readonly SearchCriteriaBuilder                               $searchCriteriaBuilder,
        private readonly OrderRepository                                     $orderRepository,
        private readonly StockByWebsiteIdResolverInterface                   $stockByWebsiteIdResolver,
        private readonly GetSourcesAssignedToStockOrderedByPriorityInterface $getSourcesAssignedToStockOrderedByPriority,
        private readonly DefaultSourceProviderInterface                      $defaultSourceProvider,
        protected ManagerInterface                                           $_eventManager
    ) {
    }

    /**
     * GenerateShipmentGuide
     *
     * @param Order $order
     * @return void
     * @throws InputException
     * @throws NoSuchEntityException
     * @throws AlreadyExistsException
     */
    public function generateShipmentGuide(Order $order): void
    {
        $orderItems = $order->getAllVisibleItems();
        $qty = 0;
        foreach ($orderItems as $item) {
            $qty += $item->getQtyOrdered();
        }
        $shippingInfo = $order->getShippingAddress()->getData();
        $isCashOnDelivery = $this->checkPaymentMethod($order);

        $params = [
            'Num_Guia' => 0,
            'Num_Sobreporte' => 0,
            'Num_Piezas' => $qty, // Número de artículos en el envío.
            'Des_TipoTrayecto' => 1, // 1->Nacional; 2->Internacional.
            'Ide_Producto' => 2, // 2->Mercancía premier; 6->Mercancía Industrial.
            'Ide_Destinatarios' => '00000000-0000-0000-0000-000000000000',
            'Ide_Manifiesto' => '00000000-0000-0000-0000-000000000000',
            'Des_FormaPago' => 2, //2->Crédito; 4->Contra-entrega.
            'Des_MedioTransporte' => 1, // 1->Terrestre.
            'Num_PesoTotal' => 3, // Peso en Kg.
            'Num_ValorDeclaradoTotal' => $order->getGrandTotal(),
            'Num_VolumenTotal' => 0, // Calculado por el sistema; 0 por defecto.
            'Num_BolsaSeguridad' => 0, // Defecto 0.
            'Num_Precinto' => 0, // Defecto 0.
            'Des_TipoDuracionTrayecto' => 1, // 1->Normal;2->Hoy mismo;3->Cero Horas;4->48h;5->72h.
            'Des_Telefono' => $shippingInfo['telephone'], // Obligatorio
            'Des_Ciudad' => $this->getFormattedCity($shippingInfo['postcode']), // Postal Code o DANE Code.
            'Des_Direccion' =>  $order->getShippingAddress()->getStreet()[0],
            'Nom_Contacto' => $shippingInfo['firstname'] . ' ' . $shippingInfo['lastname'],
            'Des_VlrCampoPersonalizado1' => '', // Campo personalizado.
            'Num_ValorLiquidado' => 0, // Calculado por el sistema; Predeterminado 0.
            'Des_DiceContener' => 'PEDIDO ECOMMERCE #' . $order->getIncrementId(),
            'Des_TipoGuia' => 1, // Defecto 1.
            'Num_VlrSobreflete' => 0, // Calculado por el sistema; 0 por defecto.
            'Num_VlrFlete' => 0, // Calculado por el sistema; 0 por defecto.
            'Num_Descuento' => 0, // Calculado por el sistema; 0 por defecto.
            'Num_PesoFacturado' => 0, // Calculado por el sistema; 0 por defecto.
            'idePaisOrigen' => 1, // 1->Colombia.
            'idePaisDestino' => 1, // 1->Colombia.
            'Des_IdArchivoOrigen' => 0, // Calculado por el sistema; 0 por defecto.
            'Des_DireccionRemitente' => '', // Opcional.
            'Est_CanalMayorista' => false,
            'Num_IdentiRemitente' => '',
            'Num_TelefonoRemitente' => '',
            'Num_Alto' => 1, // Obligatorio; Medida en cm.
            'Num_Ancho' => 1, // Obligatorio; Medida en cm.
            'Num_Largo' => 1, // Obligatorio; Medida en cm.
            'Des_DepartamentoDestino' => $this->getFormattedRegion($shippingInfo['region']),
            'Des_DepartamentoOrigen' => '', // Opcional.
            'Gen_Cajaporte' => 0, // Opcional; 0 por defecto.
            'Gen_Sobreporte' => 0, // Opcional; 0 por defecto.
            'Nom_UnidadEmpaque' => $isCashOnDelivery ? 'GENERICA' : 'GENERICO',
            'Des_UnidadLongitud' => 'cm', // Por defecto cm.
            'Des_UnidadPeso' => 'kg', // Por defecto kg.
            'Num_ValorDeclaradoSobreTotal' => 0, // Opcional; depende del valor de Sobre porte.
            'Num_Factura' => 'ORDEN-' . $order->getIncrementId(),
            'Des_CorreoElectronico' => $shippingInfo['email'],
            'Num_Recaudo' => $isCashOnDelivery ? $order->getGrandTotal() : 0, // Depende si el cliente tiene logística de recaudo.
            'Est_EnviarCorreo' => false, // Booleano para notificar envío al cliente.
            'Tipo_Doc_Destinatario' => 'CC', // CC o NIT.
            'Ide_Num_Identific_Dest' => $shippingInfo['vat_id']
        ];

        $response = $this->_webService->CargueMasivoExterno($params);
        if ($response->CargueMasivoExternoResult) {
            $guideNumber = $response->arrayGuias->string;
            try {
                $this->savePDFsGuide($guideNumber);
                $this->createShipment($order->getId(), $guideNumber);
            } catch (Exception $e) {
                $this->_logger->error($e->getMessage());
                $message = __('There was an error in Servientrega for the order: ') .
                    $order->getIncrementId() . $e->getMessage();
                $order->addCommentToStatusHistory($message);
                $this->orderRepository->save($order);
            }
        } else {
            $this->_logger->error("An error has occurred generating guide.");
            $this->_logger->error("Error response: " . json_encode($response));
        }
    }

    /**
     * CreateShipment
     *
     * @param $orderId
     * @param $trackingNumber
     * @return void
     * @throws AlreadyExistsException
     * @throws InputException
     * @throws NoSuchEntityException
     */
    private function createShipment($orderId, $trackingNumber): void
    {
        $order = null;
        try {
            $order = $this->orderRepository->get($orderId);
            try {
                $shipment = $this->prepareShipment($order, $trackingNumber);
                $this->shipmentRepository->save($shipment);
            } catch (Exception $e) {
                throw new LocalizedException(__($e->getMessage()));
            }
            $this->_shipmentNotifier->notify($shipment);
        } catch (Exception $e) {
            $message = __('Cannot create shipment for order #') . $order->getIncrementId() . ' ' . $e->getMessage();
            $order->addCommentToStatusHistory($message);
        }
        $this->orderRepository->save($order);
    }

    /**
     * PrepareShipment
     *
     * @param Order $order
     * @param $trackingNumber
     * @return Shipment
     * @throws FileSystemException
     * @throws InputException
     * @throws LocalizedException
     */
    private function prepareShipment(Order $order, $trackingNumber): Shipment
    {
        $shipment = $this->_shipment->toShipment($order);
        try {
            foreach ($order->getAllItems() as $orderItem) {
                // Check if order item has qty to ship or is virtual
                if ($orderItem->getIsVirtual()) {
                    continue;
                }
                $qtyShipped = $orderItem->getQtyOrdered();
                // Create shipment item with qty
                $shipmentItem = $this->_shipment->itemToShipmentItem($orderItem)->setQty($qtyShipped);
                // Add shipment item to shipment
                $shipment->addItem($shipmentItem);
            }
        } catch (Exception $e) {
            throw new LocalizedException(__("An error has occurred: " . $e->getMessage()));
        }
        $shipment->addComment($this->getGeneratedShippingComments($trackingNumber), false, false);
        $shipment->register();
        $shipment->getOrder()->setIsInProcess(true);
        $websiteId = $order->getStore()->getWebsiteId();
        $stockId = $this->stockByWebsiteIdResolver->execute((int) $websiteId)
            ->getStockId();
        $sources = $this->getSourcesAssignedToStockOrderedByPriority->execute((int) $stockId);
        if (!empty($sources) && count($sources) === 1) {
            $sourceCode = $sources[0]->getSourceCode();
        } else {
            $sourceCode = $this->defaultSourceProvider->getCode();
        }

        $dataCarrier = [
            'weight' => '10',
            'qty' => $shipment->getOrder()->getTotalQtyOrdered(),
            'carrier_code' => 'servientrega',
            'title' => 'Servientrega',
            'number' => $trackingNumber,
            'description' => 'Servientrega shipment and tracking'
        ];

        $track = $this->_trackFactory->create()->addData($dataCarrier);
        $shipment->addTrack($track);
        $shipment->getExtensionAttributes()->setSourceCode($sourceCode);

        return $shipment;
    }

    /**
     * OrdersToShip
     * Retrieves orders to be shipped and generates the shipment.
     *
     * @return void
     * @throws AlreadyExistsException
     * @throws InputException
     * @throws NoSuchEntityException
     */
    public function ordersToShip(): void
    {
        $orderCollection = $this->_orderCollectionFactory->create()
            ->addFieldToSelect(['increment_id'])
            ->addFieldToFilter('status', ['in' => ['processing']])
            ->addFieldToFilter('shipping_method', ['eq' => 'servientrega_servientrega'])
            ->addAttributeToFilter('sap_id', ['notnull' => true])
            ->getItems();
        /**
         * @var $orderInfo Order
         */
        foreach ($orderCollection as $orderInfo) {
            $shipment = $this->getShipment($orderInfo->getId());
            if (!$shipment) {
                $order = $this->_order->loadByIncrementId($orderInfo->getIncrementId());
                $this->generateShipmentGuide($order);
            }
        }
    }

    /**
     * SaveShipment
     *
     * @param Shipment $shipment
     * @param string $trackingNumber The number guide.
     * @return void
     */
    public function saveShipment(Shipment $shipment, string $trackingNumber): void
    {
        $dataCarrier = [
            'weight' => '10',
            'qty' => $shipment->getOrder()->getTotalQtyOrdered(),
            'carrier_code' => 'servientrega',
            'title' => 'Servientrega',
            'number' => $trackingNumber,
            'description' => 'Servientrega shipment and tracking'
        ];
        $shipment->getOrder()->setIsInProcess(true);
        try {
            $track = $this->_trackFactory->create()->addData($dataCarrier);
            $shipment->addTrack($track);
            $shipment->getExtensionAttributes()->setSourceCode('default');
            $shipment->save();
            $this->_shipmentNotifier->notify($shipment);
            $this->savePDFsGuide($trackingNumber);
        } catch (Exception $e) {
            $this->_logger->error($e->getMessage());
        }
    }

    /**
     * GetGeneratedShippingComments
     *
     * @param $guide
     * @return string
     */
    public function getGeneratedShippingComments($guide): string
    {
        if ($this->_configuration->allowSavePDF()) {
            $urls = $this->getUrls($guide);

            return __("Guide number # ") . "<a href=\"" . $urls['guideUrl'] . "\" download=\"true\" >" . $guide . "</a>"
                . "<br>"
                . __("Tracking number # ") . "<a target=\"_blank\" href=\"" . $urls['trackingUrl'] . "\">" . $guide . "</a>";
        } else {
            return __("Guide number # {$guide}")
                . "\n"
                . __("Tracking number # {$guide}");
        }
    }

    /**
     * SavePDFsGuide
     *
     * @param $guide
     * @return void
     * @throws FileSystemException
     */
    public function savePDFsGuide($guide): void
    {
        $param = [
            'numberGuide' => $guide,
            'formatGuide' => 2
        ];
        if ($this->_configuration->allowSavePDF()) {
            $response = $this->_webService->GenerarGuiaSticker($param);
            $folder = $this->getOrCreateFolder();
            $fileName = $guide . '.pdf';
            $filePath = $folder . '/' . $fileName;
            # Write the PDF contents to a local file
            file_put_contents($filePath, $response->bytesReport);
        }
    }

    /**
     * GetOrCreateFolder
     *
     * @return string
     * @throws FileSystemException
     */
    public function getOrCreateFolder(): string
    {
        try {
            $name = 'servientrega';
            $folder = $this->_fileSystemDir->getPath('pub') . '/' . $name;
            if (!is_dir($folder)) {
                $this->_fileSystem->mkdir($folder);
            }
        } catch (FileSystemException $e) {
            throw new FileSystemException(__("Directory does not exists or cannot be read"));
        }

        return $folder;
    }

    /**
     * GetUrls
     * Retrieve urls where files are saved.
     *
     * @param $guide
     * @return string[]
     */
    public function getUrls($guide): array
    {
        $guideUrl = $this->_configuration->getPDFPath();
        $uriTracking = $this->_configuration->getURLMTrack();

        return [
            'guideUrl' => $guideUrl . $guide . '.pdf',
            'trackingUrl' => $uriTracking . $guide
        ];
    }

    /**
     * GetShipment
     * Checks if order has any shipment.
     *
     * @param $orderId
     * @return array|null
     */
    private function getShipment($orderId): ?array
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('order_id', $orderId)->create();
        try {
            $shipments = $this->shipmentRepository->getList($searchCriteria);
            $shipmentsRecords = $shipments->getItems();
        } catch (Exception $e) {
            $this->_logger->debug($e->getMessage());
            $shipmentsRecords = null;
        }

        return $shipmentsRecords;
    }

    /**
     * GetFormattedCity
     * Get postcode formatted.
     *
     * @param $postcode
     * @return string|null
     */
    private function getFormattedCity($postcode): ?string
    {
        if ($postcode) {
            return $postcode . '000';
        }

        return null;
    }

    /**
     * GetFormattedRegion
     *
     * @param $region
     * @return mixed|string
     */
    private function getFormattedRegion($region): mixed
    {
        if ($region == 'VALLE DEL CAUCA') {
            $fRegion = 'VALLE';
        } else {
            $fRegion = $region;
        }

        return $fRegion;
    }

    /**
     * ReviewShipments
     * This method is only for debug; deletes all items shipped and returns to processing status order.
     *
     * @return void
     * @throws Exception
     */
    public function reviewShipments(): void
    {
        //Only for debug. No delete.
        $myOrder = $this->_order->loadByIncrementId('000001426');

        $_shipments = $myOrder->getShipmentsCollection();

        if ($_shipments) {
            foreach ($_shipments as $_shipment) {
                $_shipment->delete();
            }
        }
        /*Only for debug. If necessary, uncomment it.
        $_invoices = $myOrder->getInvoiceCollection();

        if($_invoices){
            foreach($_invoices as $invoice){
                $invoice->delete();
            }
        }*/

        foreach ($myOrder->getAllItems() as $item) {
            $item->setQtyShipped(0);
            $item->save();
        }

        $myOrder
            ->setState(Order::STATE_PROCESSING)
            ->setStatus(Order::STATE_PROCESSING);
        try {
            $this->orderRepository->save($myOrder);
        } catch (Exception $e) {
            $this->_logger->error($e->getMessage());
        }
    }

    /**
     * CheckPaymentMethod
     * Check if payment method is 'Cash On Delivery' if, it's send another billing code
     *
     * @param Order $order
     * @return bool
     */
    public function checkPaymentMethod(Order $order): bool
    {
        if ($order->getPayment()->getMethod() === 'cashondelivery') {
            $this->_webService->isCashOnDelivery();
            return true;
        }

        return false;
    }
}
