<?php

namespace Aventi\Servientrega\Model;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\DB\TransactionFactory;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryApi\Api\GetSourcesAssignedToStockOrderedByPriorityInterface;
use Magento\InventoryCatalogApi\Api\DefaultSourceProviderInterface;
use Magento\InventorySalesApi\Model\StockByWebsiteIdResolverInterface;
use Magento\Sales\Api\ShipmentRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderRepository;

class ShipmentGeneration
{
    const SHIPPING_METHOD = 'servientrega_servientrega';

    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $_logger;

    /**
     * @var \Aventi\Servientrega\Helper\WebService
     */
    private $_webService;

    /**
     * @var \Magento\Shipping\Model\ShipmentNotifier
     */
    private $_shipmentNotifier;

    /**
     * @var Order
     */
    private $_order;

    /**
     * @var \Magento\Sales\Model\Convert\Order
     */
    private $_shipment;

    /**
     * @var \Magento\Sales\Model\Order\Shipment\TrackFactory
     */
    private $_trackFactory;

    /**
     * @var \Aventi\Servientrega\Helper\Configuration
     */
    private $_configuration;

    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    protected $_fileSystemDir;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_url;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    private $_orderCollectionFactory;

    /**
     * @var TransactionFactory
     */
    private $_transactionFactory;

    /**
     * @var \Magento\Framework\Filesystem\Io\File
     */
    private $_fileSystem;

    /**
     * @var ShipmentRepositoryInterface
     */
    private $shipmentRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var OrderRepository
     */
    private $orderRepository;

    /**
     * @var StockByWebsiteIdResolverInterface
     */
    private $stockByWebsiteIdResolver;

    /**
     * @var GetSourcesAssignedToStockOrderedByPriorityInterface
     */
    private $getSourcesAssignedToStockOrderedByPriority;

    /**
     * @var DefaultSourceProviderInterface
     */
    private $defaultSourceProvider;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * ShipmentGeneration constructor.
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Aventi\Servientrega\Helper\WebService $webService
     * @param \Magento\Shipping\Model\ShipmentNotifier $shipmentNotifier
     * @param Order $order
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collectionFactory
     * @param \Magento\Sales\Model\Convert\Order $shipment
     * @param \Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory
     * @param \Aventi\Servientrega\Helper\Configuration $configuration
     * @param \Magento\Framework\Filesystem\DirectoryList $fileSystemDir
     * @param \Magento\Framework\Filesystem\Io\File $fileSystem
     * @param \Magento\Framework\UrlInterface $url
     * @param TransactionFactory $transactionFactory
     * @param ShipmentRepositoryInterface $shipmentRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param OrderRepository $orderRepository
     * @param StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver
     * @param GetSourcesAssignedToStockOrderedByPriorityInterface $getSourcesAssignedToStockOrderedByPriority
     * @param DefaultSourceProviderInterface $defaultSourceProvider
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     */
    public function __construct(
        \Psr\Log\LoggerInterface                                   $logger,
        \Aventi\Servientrega\Helper\WebService                     $webService,
        \Magento\Shipping\Model\ShipmentNotifier                   $shipmentNotifier,
        Order                                                      $order,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collectionFactory,
        \Magento\Sales\Model\Convert\Order                         $shipment,
        \Magento\Sales\Model\Order\Shipment\TrackFactory           $trackFactory,
        \Aventi\Servientrega\Helper\Configuration                  $configuration,
        \Magento\Framework\Filesystem\DirectoryList                $fileSystemDir,
        \Magento\Framework\Filesystem\Io\File                      $fileSystem,
        \Magento\Framework\UrlInterface                            $url,
        TransactionFactory                                         $transactionFactory,
        ShipmentRepositoryInterface                                $shipmentRepository,
        SearchCriteriaBuilder                                      $searchCriteriaBuilder,
        OrderRepository $orderRepository,
        StockByWebsiteIdResolverInterface $stockByWebsiteIdResolver,
        GetSourcesAssignedToStockOrderedByPriorityInterface $getSourcesAssignedToStockOrderedByPriority,
        DefaultSourceProviderInterface $defaultSourceProvider,
        \Magento\Framework\Event\ManagerInterface $eventManager
    ) {
        $this->_logger = $logger;
        $this->_webService = $webService;
        $this->_shipmentNotifier = $shipmentNotifier;
        $this->_order = $order;
        $this->_orderCollectionFactory = $collectionFactory;
        $this->_shipment = $shipment;
        $this->_trackFactory = $trackFactory;
        $this->_configuration = $configuration;
        $this->_fileSystemDir = $fileSystemDir;
        $this->_fileSystem = $fileSystem;
        $this->_url = $url;
        $this->_transactionFactory = $transactionFactory;
        $this->shipmentRepository = $shipmentRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->orderRepository = $orderRepository;
        $this->stockByWebsiteIdResolver = $stockByWebsiteIdResolver;
        $this->getSourcesAssignedToStockOrderedByPriority = $getSourcesAssignedToStockOrderedByPriority;
        $this->defaultSourceProvider = $defaultSourceProvider;
        $this->_eventManager = $eventManager;
    }

    /**
     * @param Order $order
     * @throws \Exception
     */
    public function generateShipmentGuide(Order $order)
    {
        $orderItems = $order->getAllVisibleItems();
        $qty = 0;
        foreach ($orderItems as $item) {
            $qty += $item->getQtyOrdered();
        }
        $shippingInfo = $order->getShippingAddress()->getData();
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
            'Nom_UnidadEmpaque' => 'GENERICA',
            'Des_UnidadLongitud' => 'cm', // Por defecto cm.
            'Des_UnidadPeso' => 'kg', // Por defecto kg.
            'Num_ValorDeclaradoSobreTotal' => 0, // Opcional; depende del valor de Sobre porte.
            'Num_Factura' => 'ORDEN-' . $order->getIncrementId(),
            'Des_CorreoElectronico' => $shippingInfo['email'],
            'Num_Recaudo' => 0, // Depende si el cliente tiene logística de recaudo.
            'Est_EnviarCorreo' => false, // Booleano para notificar envío al cliente.
            'Tipo_Doc_Destinatario' => 'CC', // CC o NIT.
            'Ide_Num_Identific_Dest' => $shippingInfo['vat_id']
        ];

        $this->_logger->debug(json_encode($params));

        $response = $this->_webService->CargueMasivoExterno($params);
        if ($response->CargueMasivoExternoResult) {
            $guideNumber = $response->arrayGuias->string;
            try {
                $this->savePDFsGuide($guideNumber);
                $this->createShipment($order->getId(), $guideNumber);
            } catch (\Exception $e) {
                $this->_logger->error($e->getMessage());
                $message = 'Hubo un error en el proceso de Servientrega para la orden #' .
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
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws InputException
     */
    private function createShipment($orderId, $trackingNumber)
    {
        $order = null;
        try {
            $order = $this->orderRepository->get($orderId);
            try {
                $shipment = $this->prepareShipment($order, $trackingNumber);
                $this->shipmentRepository->save($shipment);
            } catch (\Exception $e) {
                throw new LocalizedException(__($e->getMessage()));
            }
            $this->_shipmentNotifier->notify($shipment);
        } catch (\Exception $e) {
            $message = 'Cannot create shipment for order #' . $order->getIncrementId() . ' ' . $e->getMessage();
            $order->addCommentToStatusHistory($message);
        }
        $this->orderRepository->save($order);
    }

    /**
     * @param $order Order
     * @param $trackingNumber
     * @return Order\Shipment
     * @throws LocalizedException
     * @throws InputException
     */
    private function prepareShipment(Order $order, $trackingNumber): Order\Shipment
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
        } catch (\Exception $e) {
            throw new LocalizedException(__("An error has occurred" . " --- " . $e->getMessage()));
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
     * Retrieves orders to be shipped and generates the shipment.
     * @throws \Exception
     */
    public function ordersToShip()
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
     * @param \Magento\Sales\Model\Order\Shipment $shipment
     * @param string $trackingNumber The number guide.
     */
    public function saveShipment(\Magento\Sales\Model\Order\Shipment $shipment, string $trackingNumber)
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
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
        }
    }

    /**
     * @param $guide
     * @return \Magento\Framework\Phrase|string
     * @throws FileSystemException
     */
    public function getGeneratedShippingComments($guide)
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
     * @param $guide
     * @throws FileSystemException
     */
    public function savePDFsGuide($guide)
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
     * Retrieve urls where files are saved.
     * @param $guide
     * @return array
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
     * Checks if order has any shipment.
     * @param $orderId
     * @return array|null
     */
    private function getShipment($orderId): ?array
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter('order_id', $orderId)->create();
        try {
            $shipments = $this->shipmentRepository->getList($searchCriteria);
            $shipmentsRecords = $shipments->getItems();
        } catch (\Exception $e) {
            $this->_logger->debug($e->getMessage());
            $shipmentsRecords = null;
        }

        return $shipmentsRecords;
    }

    /**
     * Get postcode formatted.
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

    private function getFormattedRegion($region)
    {
        $fRegion = null;
        if ($region == 'VALLE DEL CAUCA') {
            $fRegion = 'VALLE';
        } else {
            $fRegion = $region;
        }
        return $fRegion;
    }

    /**
     * This method is only for debug; deletes all items shipped and returns to
     * processing status order.
     * @throws \Exception
     */
    public function reviewShipments()
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
        } catch (\Exception $e) {
            $this->_logger->error($e->getMessage());
        }
    }
}
