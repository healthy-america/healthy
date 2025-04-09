<?php

namespace MagentoSistecredito\SistecreditoPaymentGateway\Test\Unit\Controller;


use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use MagentoSistecredito\SistecreditoPaymentGateway\Controller\Gateway\Confirm;
use MagentoSistecredito\SistecreditoPaymentGateway\Helper\DbHelper;
use MagentoSistecredito\SistecreditoPaymentGateway\Helper\GatewayEndpoint;

class ConfirmTest extends \PHPUnit\Framework\TestCase
{
    public $confirm;

    public $context;

    public $urlInterface;

    public $orderFactory;

    public $gatewayEndpoint;

    public $checkoutSession;

    public $_dbHelper;

    public $scopeConfig;

    protected function setUp(): void
    {
        $this->context = $this->getMockBuilder(\Magento\Framework\App\Action\Context::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->urlInterface = $this->getMockBuilder(\Magento\Framework\Url::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->orderFactory = $this->getMockBuilder(\Magento\Sales\Model\OrderFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->gatewayEndpoint = $this->getMockBuilder(GatewayEndpoint::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->checkoutSession = $this->getMockBuilder(\Magento\Checkout\Model\Session::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_dbHelper = $this->getMockBuilder(DbHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->scopeConfig = $this->getMockBuilder(\Magento\Framework\App\Config\ScopeConfigInterface::class)
            ->getMock();

        parent::setUp();
    }

    public function testExecuteWithRequestMethodTypeGetAndParametesNullThenReturnAndRedirectWithErrorParameter()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $manager = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->getMock();

        $manager->expects($this->any())
            ->method('addErrorMessage')
            ->willReturnSelf();

        $this->context->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($manager);

        $this->scopeConfig->expects($this->any())
            ->method('getValue')
            ->willReturn('https://www.google.com');

        $this->_dbHelper->expects($this->once())
            ->method('validUrlRedirect')
            ->willReturn(true);

        $resultFactory = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();
        $resultRedirect = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultFactory->expects($this->atLeastOnce())
            ->method('create')
            ->with(ResultFactory::TYPE_REDIRECT)
            ->willReturn($resultRedirect);

        $this->context->expects($this->once())->method('getResultFactory')->willReturn($resultFactory);

        $confirmMock = new Confirm(
            $this->context,
            $this->urlInterface,
            $this->orderFactory,
            $this->gatewayEndpoint,
            $this->checkoutSession,
            $this->_dbHelper,
            $this->scopeConfig
        );

        $this->confirm = $confirmMock;

        $this->confirm->execute();
    }

    public function testExecuteWithRequestMethodTypeGetAndParametesNotNullThenReturnAndUpdateTransactionPaymentMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $manager = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->getMock();

        $manager->expects($this->any())
            ->method('addErrorMessage')
            ->willReturnSelf();

        $this->context->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($manager);

        $this->scopeConfig->expects($this->any())
            ->method('getValue')
            ->willReturn('https://www.google.com');

        $this->_dbHelper->expects($this->once())
            ->method('validUrlRedirect')
            ->willReturn(true);

        $resultFactory = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $resultRedirect = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resultFactory->expects($this->atLeastOnce())
            ->method('create')
            ->with(ResultFactory::TYPE_REDIRECT)
            ->willReturn($resultRedirect);

        $this->context->expects($this->once())->method('getResultFactory')->willReturn($resultFactory);

        $this->_dbHelper->expects($this->once())
            ->method('filterInputArray')
            ->willReturn([
                'orderId' => 1,
                'transactionId' => 1
            ]);

        // Mock Order object
        $mockOrder = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Define what the loadByIncrementId method should return
        $mockOrder->method('loadByIncrementId')
            ->willReturn($mockOrder);

        $mockOrder->method('getState')
            ->willReturn("pending_payment");

        $mockPayment = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockPayment->method('setAmountCanceled')
            ->willReturnSelf();

        $mockOrder->method('getPayment')
            ->willReturn($mockPayment);

        $mockOrder->method('setState')
            ->willReturnSelf();

        $mockOrder->method('setStatus')
            ->willReturnSelf();

        $mockOrder->method('save')
            ->willReturnSelf();

        $mockOrder->method('registerCancellation')
            ->willReturnSelf();

        // Define what the create method should return
        $this->orderFactory->method('create')
            ->willReturn($mockOrder);

        $this->gatewayEndpoint->expects($this->once())
            ->method('getInfoCredit')
            ->willReturn((object)[
                "data" => (object)[
                    "transactionStatus" => "APPROVED",
                    "authentication" => "Authentication",
                    "credit" => (object)[
                        "creditNumber" => "1234567890",
                    ],
                    "valueToPaid" => "10000",
                ]
            ]);

        $confirmMock = new Confirm(
            $this->context,
            $this->urlInterface,
            $this->orderFactory,
            $this->gatewayEndpoint,
            $this->checkoutSession,
            $this->_dbHelper,
            $this->scopeConfig
        );

        $this->confirm = $confirmMock;

        $this->confirm->execute();
    }

    public function testExecuteWithRequestMethodTypeGetAndParametesNotNullThenReturnAndUpdateTransactionPaymentMethodWitStatus3()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $manager = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->getMock();

        $manager->expects($this->any())
            ->method('addErrorMessage')
            ->willReturnSelf();

        $this->context->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($manager);

        $this->scopeConfig->expects($this->any())
            ->method('getValue')
            ->willReturn('https://www.google.com');

        $this->_dbHelper->expects($this->once())
            ->method('validUrlRedirect')
            ->willReturn(false);

        $resultFactory = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $resultRedirect = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultFactory->expects($this->atLeastOnce())
            ->method('create')
            ->with(ResultFactory::TYPE_REDIRECT)
            ->willReturn($resultRedirect);

        $this->context->expects($this->once())->method('getResultFactory')->willReturn($resultFactory);

        $this->_dbHelper->expects($this->once())
            ->method('filterInputArray')
            ->willReturn([
                'orderId' => 1,
                'transactionId' => 1
            ]);

        // Mock Order object
        $mockOrder = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Define what the loadByIncrementId method should return
        $mockOrder->method('loadByIncrementId')
            ->willReturn($mockOrder);

        $mockOrder->method('getState')
            ->willReturn("pending_payment");

        $mockPayment = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockPayment->method('setAmountCanceled')
            ->willReturnSelf();

        $mockOrder->method('getPayment')
            ->willReturn($mockPayment);

        $mockOrder->method('setState')
            ->willReturnSelf();

        $mockOrder->method('setStatus')
            ->willReturnSelf();

        $mockOrder->method('save')
            ->willReturnSelf();

        $mockOrder->method('registerCancellation')
            ->willReturnSelf();

        // Define what the create method should return
        $this->orderFactory->method('create')
            ->willReturn($mockOrder);

        $this->gatewayEndpoint->expects($this->once())
            ->method('getInfoCredit')
            ->willReturn((object)[
                "data" => (object)[
                    "transactionStatus" => 3,
                    "authentication" => "Authentication",
                    "credit" => (object)[
                        "creditNumber" => "1234567890",
                    ],
                    "valueToPaid" => "10000",
                ]
            ]);

        $confirmMock = new Confirm(
            $this->context,
            $this->urlInterface,
            $this->orderFactory,
            $this->gatewayEndpoint,
            $this->checkoutSession,
            $this->_dbHelper,
            $this->scopeConfig
        );

        $this->confirm = $confirmMock;

        $this->confirm->execute();
    }

    public function testExecuteWithRequestMethodTypeGetAndParametesNotNullThenReturnAndUpdateTransactionPaymentMethodWitStatusCancel()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';

        $manager = $this->getMockBuilder(\Magento\Framework\Message\ManagerInterface::class)
            ->getMock();

        $manager->expects($this->any())
            ->method('addErrorMessage')
            ->willReturnSelf();

        $this->context->expects($this->any())
            ->method('getMessageManager')
            ->willReturn($manager);

        $this->scopeConfig->expects($this->any())
            ->method('getValue')
            ->willReturn('https://www.google.com');

        $this->_dbHelper->expects($this->once())
            ->method('validUrlRedirect')
            ->willThrowException(new \Exception("Error"));

        $resultFactory = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->setMethods(['create'])
            ->getMock();
        $resultRedirect = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();
        $resultFactory->expects($this->atLeastOnce())
            ->method('create')
            ->with(ResultFactory::TYPE_REDIRECT)
            ->willReturn($resultRedirect);

        $this->context->expects($this->once())->method('getResultFactory')->willReturn($resultFactory);

        $this->_dbHelper->expects($this->once())
            ->method('filterInputArray')
            ->willReturn([
                'orderId' => 1,
                'transactionId' => 1
            ]);

        // Mock Order object
        $mockOrder = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Define what the loadByIncrementId method should return
        $mockOrder->method('loadByIncrementId')
            ->willReturn($mockOrder);

        $mockOrder->method('getState')
            ->willReturn("canceled");

        $mockPayment = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockPayment->method('setAmountCanceled')
            ->willReturnSelf();

        $mockOrder->method('getPayment')
            ->willReturn($mockPayment);

        $mockOrder->method('setState')
            ->willReturnSelf();

        $mockOrder->method('setStatus')
            ->willReturnSelf();

        $mockOrder->method('save')
            ->willReturnSelf();

        $mockOrder->method('registerCancellation')
            ->willReturnSelf();

        // Define what the create method should return
        $this->orderFactory->method('create')
            ->willReturn($mockOrder);

        $this->gatewayEndpoint->expects($this->once())
            ->method('getInfoCredit')
            ->willReturn((object)[
                "data" => (object)[
                    "transactionStatus" => 2,
                    "authentication" => "Authentication",
                    "credit" => (object)[
                        "creditNumber" => "1234567890",
                    ],
                    "valueToPaid" => "10000",
                ]
            ]);

        $confirmMock = new Confirm(
            $this->context,
            $this->urlInterface,
            $this->orderFactory,
            $this->gatewayEndpoint,
            $this->checkoutSession,
            $this->_dbHelper,
            $this->scopeConfig
        );

        $this->confirm = $confirmMock;

        $this->confirm->execute();
    }

    /**
     * @dataProvider states
     */
    public function testExecuteWithRequestMethodTypePostAndParametesNotNull($state){
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $this->_dbHelper->expects($this->once())
            ->method('fileGetContents')
            ->willReturn(json_encode([
                "TypeDocument" => "CC",
                "IdDocument" => "1234567890",
                "ValueToPay" => "20000",
                "OrderId" => "1",
                "CreditNumber" => "1234567890",
                "Authentication" => "Authentication",
                "TransactionStatus" => $state,
                "TransactionId" => "1"
            ]));

        $resultfactory = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resultJson = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->disableOriginalConstructor()
            ->getMock();



        $resultfactory->expects($this->once())
            ->method('create')
            ->willReturn($resultJson);

        $this->context->expects($this->any())
            ->method('getResultFactory')
            ->willReturn($resultfactory);

        // Mock Order object
        $mockOrder = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockOrder->method('getState')
            ->willReturn($state);

        // Define what the loadByIncrementId method should return
        $mockOrder->method('loadByIncrementId')
            ->willReturn($mockOrder);

        $mockPayment = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockPayment->method('setAmountCanceled')
            ->willReturnSelf();

        $mockOrder->method('getPayment')
            ->willReturn($mockPayment);

        $mockOrder->method('setState')
            ->willReturnSelf();

        $mockOrder->method('setStatus')
            ->willReturnSelf();

        $mockOrder->method('save')
            ->willReturnSelf();

        $mockOrder->method('registerCancellation')
            ->willReturnSelf();

        // Define what the create method should return
        $this->orderFactory->method('create')
            ->willReturn($mockOrder);

        $confirmMock = new Confirm(
            $this->context,
            $this->urlInterface,
            $this->orderFactory,
            $this->gatewayEndpoint,
            $this->checkoutSession,
            $this->_dbHelper,
            $this->scopeConfig
        );

        $this->confirm = $confirmMock;

        $this->confirm->execute();
    }

    public function states(){
        return [
            ["complete"],
            ["Abandonada"],
            ["Approved"]
        ];
    }

    public function testExecuteWithRequestMethodTypePostAndParametesTransactionIdEmpty(){
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $this->_dbHelper->expects($this->once())
            ->method('fileGetContents')
            ->willReturn(json_encode([
                "TypeDocument" => "CC",
                "IdDocument" => "1234567890",
                "ValueToPay" => "20000",
                "OrderId" => "1",
                "CreditNumber" => "1234567890",
                "Authentication" => "Authentication",
                "TransactionStatus" => 3,
                "TransactionId" => ""
            ]));

        $resultfactory = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resultJson = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->disableOriginalConstructor()
            ->getMock();



        $resultfactory->expects($this->once())
            ->method('create')
            ->willReturn($resultJson);

        $this->context->expects($this->any())
            ->method('getResultFactory')
            ->willReturn($resultfactory);

        // Mock Order object
        $mockOrder = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Define what the loadByIncrementId method should return
        $mockOrder->method('loadByIncrementId')
            ->willReturn($mockOrder);

        $mockPayment = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockPayment->method('setAmountCanceled')
            ->willReturnSelf();

        $mockOrder->method('getPayment')
            ->willReturn($mockPayment);

        $mockOrder->method('setState')
            ->willReturnSelf();

        $mockOrder->method('setStatus')
            ->willReturnSelf();

        $mockOrder->method('save')
            ->willReturnSelf();

        $mockOrder->method('registerCancellation')
            ->willReturnSelf();

        // Define what the create method should return
        $this->orderFactory->method('create')
            ->willReturn($mockOrder);

        $confirmMock = new Confirm(
            $this->context,
            $this->urlInterface,
            $this->orderFactory,
            $this->gatewayEndpoint,
            $this->checkoutSession,
            $this->_dbHelper,
            $this->scopeConfig
        );

        $this->confirm = $confirmMock;

        $this->confirm->execute();
    }

    public function testExecuteWithRequestMethodTypePostAndParametesNotNullAndStateComplete(){
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $this->_dbHelper->expects($this->once())
            ->method('fileGetContents')
            ->willReturn(json_encode([
                "TypeDocument" => "CC",
                "IdDocument" => "1234567890",
                "ValueToPay" => "20000",
                "OrderId" => "1",
                "CreditNumber" => "1234567890",
                "Authentication" => "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.PLdsa_B70GdZtqgXctUARpWvlRqsv2HJITnCd1KHBII",
                "TransactionStatus" => "Terminado",
                "TransactionId" => "1"
            ]));

        $this->_dbHelper->expects($this->once())
            ->method('getSistecreditoOrderLog')
            ->willReturn([
                "request_token"=>  "token",
                "total_order" => "20000",

            ]);

        $this->_dbHelper->expects($this->once())
            ->method('decodeJwt')
            ->willReturn((object)["aud"=>"https://www.google.com"]);

        $this->urlInterface->expects($this->any())
            ->method('getBaseUrl')
            ->willReturn("https://www.google.com");

        $resultfactory = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resultJson = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->disableOriginalConstructor()
            ->getMock();



        $resultfactory->expects($this->once())
            ->method('create')
            ->willReturn($resultJson);

        $this->context->expects($this->any())
            ->method('getResultFactory')
            ->willReturn($resultfactory);

        // Mock Order object
        $mockOrder = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockOrder->method('getState')
            ->willReturn("Terminado");

        // Define what the loadByIncrementId method should return
        $mockOrder->method('loadByIncrementId')
            ->willReturn($mockOrder);

        $mockPayment = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockPayment->method('setAmountCanceled')
            ->willReturnSelf();

        $mockOrder->method('getPayment')
            ->willReturn($mockPayment);

        $mockOrder->method('setState')
            ->willReturnSelf();

        $mockOrder->method('setStatus')
            ->willReturnSelf();

        $mockOrder->method('save')
            ->willReturnSelf();

        $mockOrder->method('registerCancellation')
            ->willReturnSelf();

        $mockOrder->expects($this->once())
            ->method('canInvoice')
            ->willReturn(true);

        // Define what the create method should return
        $this->orderFactory->method('create')
            ->willReturn($mockOrder);

        $mockInvoice = $this->getMockBuilder(\Magento\Sales\Model\Order\Invoice::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockInvoice->method('getTotalQty')
            ->willReturn(1);

        $mockInvoice->method('getOrder')
            ->willReturn($mockOrder);

// Crear un objeto ficticio de la clase InvoiceService
        $mockInvoiceService = $this->getMockBuilder(\Magento\Sales\Model\Service\InvoiceService::class)
            ->disableOriginalConstructor()
            ->getMock();

// Configurar el objeto ficticio de InvoiceService para que prepareInvoice devuelva el objeto ficticio de Invoice
        $mockInvoiceService->method('prepareInvoice')->willReturn($mockInvoice);



// Crear un objeto ficticio de la clase ObjectManager
        $mockObjectManager = $this->getMockBuilder(\Magento\Framework\App\ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockInvoiceService2 = $this->getMockBuilder("Magento\Framework\DB\Transaction")
            ->disableOriginalConstructor()
            ->getMock();

        $mockInvoiceService2->method('addObject')->willReturnSelf();

// Configurar el objeto ficticio de ObjectManager para que create devuelva el objeto ficticio de InvoiceService
        $mockObjectManager->method('create')->willReturn($mockInvoiceService);

        $mockObjectManager2 = $this->getMockBuilder(\Magento\Framework\App\ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockObjectManager2->method('create')->willReturn($mockInvoiceService2);

        $this->_dbHelper->expects($this->any())
            ->method('getObjectManager')
            ->willReturnOnConsecutiveCalls($mockObjectManager, $mockObjectManager2);

        $confirmMock = new Confirm(
            $this->context,
            $this->urlInterface,
            $this->orderFactory,
            $this->gatewayEndpoint,
            $this->checkoutSession,
            $this->_dbHelper,
            $this->scopeConfig
        );

        $this->confirm = $confirmMock;

        $this->confirm->execute();
    }

    public function testvalidateForCsrf(){
        $this->confirm = new Confirm(
            $this->context,
            $this->urlInterface,
            $this->orderFactory,
            $this->gatewayEndpoint,
            $this->checkoutSession,
            $this->_dbHelper,
            $this->scopeConfig
        );

        $request = $this->getMockBuilder(\Magento\Framework\App\RequestInterface::class)
            ->getMock();



        $return = $this->confirm->validateForCsrf($request);

        $this->assertTrue($return);
    }

    /**
     * @dataProvider boolState
     */
    public function testExecuteWithRequestMethodTypePostAndParametesNotNullAndStateCompleteButCONFIRMATIONPAYMENTEXCEPTION($state1,$state2){
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $this->_dbHelper->expects($this->once())
            ->method('fileGetContents')
            ->willReturn(json_encode([
                "TypeDocument" => "CC",
                "IdDocument" => "1234567890",
                "ValueToPay" => "20000",
                "OrderId" => "1",
                "CreditNumber" => "1234567890",
                "Authentication" => "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.PLdsa_B70GdZtqgXctUARpWvlRqsv2HJITnCd1KHBII",
                "TransactionStatus" => "Terminado",
                "TransactionId" => "1"
            ]));

        $this->_dbHelper->expects($this->once())
            ->method('getSistecreditoOrderLog')
            ->willReturn(null);

        $this->_dbHelper->expects($this->once())
            ->method('decodeJwt')
            ->willReturn((object)["aud"=>"https://www.google.com"]);

        $this->urlInterface->expects($this->any())
            ->method('getBaseUrl')
            ->willReturn("https://www.google.com");

        $resultfactory = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resultJson = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->disableOriginalConstructor()
            ->getMock();



        $resultfactory->expects($this->once())
            ->method('create')
            ->willReturn($resultJson);

        $this->context->expects($this->any())
            ->method('getResultFactory')
            ->willReturn($resultfactory);

        // Mock Order object
        $mockOrder = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockOrder->method('getState')
            ->willReturn("Terminado");

        // Define what the loadByIncrementId method should return
        $mockOrder->method('loadByIncrementId')
            ->willReturn($mockOrder);

        $mockPayment = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockPayment->method('setAmountCanceled')
            ->willReturnSelf();

        $mockOrder->method('getPayment')
            ->willReturn($mockPayment);

        $mockOrder->method('setState')
            ->willReturnSelf();

        $mockOrder->method('setStatus')
            ->willReturnSelf();

        $mockOrder->method('save')
            ->willReturnSelf();

        $mockOrder->method('registerCancellation')
            ->willReturnSelf();

        $mockOrder->expects($this->once())
            ->method('canInvoice')
            ->willReturn($state1);

        // Define what the create method should return
        $this->orderFactory->method('create')
            ->willReturn($mockOrder);

        $mockInvoice = $this->getMockBuilder(\Magento\Sales\Model\Order\Invoice::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockInvoice->method('getTotalQty')
            ->willReturn($state2);

        $mockInvoice->method('getOrder')
            ->willReturn($mockOrder);

// Crear un objeto ficticio de la clase InvoiceService
        $mockInvoiceService = $this->getMockBuilder(\Magento\Sales\Model\Service\InvoiceService::class)
            ->disableOriginalConstructor()
            ->getMock();

// Configurar el objeto ficticio de InvoiceService para que prepareInvoice devuelva el objeto ficticio de Invoice
        $mockInvoiceService->method('prepareInvoice')->willReturn($mockInvoice);



// Crear un objeto ficticio de la clase ObjectManager
        $mockObjectManager = $this->getMockBuilder(\Magento\Framework\App\ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockInvoiceService2 = $this->getMockBuilder("Magento\Framework\DB\Transaction")
            ->disableOriginalConstructor()
            ->getMock();

        $mockInvoiceService2->method('addObject')->willReturnSelf();

// Configurar el objeto ficticio de ObjectManager para que create devuelva el objeto ficticio de InvoiceService
        $mockObjectManager->method('create')->willReturn($mockInvoiceService);

        $mockObjectManager2 = $this->getMockBuilder(\Magento\Framework\App\ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockObjectManager2->method('create')->willReturn($mockInvoiceService2);

        $this->_dbHelper->expects($this->any())
            ->method('getObjectManager')
            ->willReturnOnConsecutiveCalls($mockObjectManager, $mockObjectManager2);


        $confirmMock = new Confirm(
            $this->context,
            $this->urlInterface,
            $this->orderFactory,
            $this->gatewayEndpoint,
            $this->checkoutSession,
            $this->_dbHelper,
            $this->scopeConfig
        );

        $this->confirm = $confirmMock;

        $this->confirm->execute();
    }

    public function boolState(){
        return [
            [true, false],
            [false, false]
        ];
    }

    public function testExecuteWithRequestMethodTypePostAndParametesErrorDecode(){
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $this->_dbHelper->expects($this->once())
            ->method('fileGetContents')
            ->willReturn(json_encode([
                "TypeDocument" => "CC",
                "IdDocument" => "1234567890",
                "ValueToPay" => "20000",
                "OrderId" => "1",
                "CreditNumber" => "1234567890",
                "Authentication" => "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.PLdsa_B70GdZtqgXctUARpWvlRqsv2HJITnCd1KHBII",
                "TransactionStatus" => "Terminado",
                "TransactionId" => "1"
            ]));

        $this->_dbHelper->expects($this->once())
            ->method('getSistecreditoOrderLog')
            ->willReturn([
                "request_token"=>  "token",
                "total_order" => "20000",

            ]);

        $this->_dbHelper->expects($this->once())
            ->method('decodeJwt')
            ->willThrowException(new \Exception("Error"));

        $this->urlInterface->expects($this->any())
            ->method('getBaseUrl')
            ->willReturn("https://www.google.com");

        $resultfactory = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resultJson = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->disableOriginalConstructor()
            ->getMock();



        $resultfactory->expects($this->once())
            ->method('create')
            ->willReturn($resultJson);

        $this->context->expects($this->any())
            ->method('getResultFactory')
            ->willReturn($resultfactory);

        // Mock Order object
        $mockOrder = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockOrder->method('getState')
            ->willReturn("Terminado");

        // Define what the loadByIncrementId method should return
        $mockOrder->method('loadByIncrementId')
            ->willReturn($mockOrder);

        $mockPayment = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockPayment->method('setAmountCanceled')
            ->willReturnSelf();

        $mockOrder->method('getPayment')
            ->willReturn($mockPayment);

        $mockOrder->method('setState')
            ->willReturnSelf();

        $mockOrder->method('setStatus')
            ->willReturnSelf();

        $mockOrder->method('save')
            ->willReturnSelf();

        $mockOrder->method('registerCancellation')
            ->willReturnSelf();


        // Define what the create method should return
        $this->orderFactory->method('create')
            ->willReturn($mockOrder);



        $confirmMock = new Confirm(
            $this->context,
            $this->urlInterface,
            $this->orderFactory,
            $this->gatewayEndpoint,
            $this->checkoutSession,
            $this->_dbHelper,
            $this->scopeConfig
        );

        $this->confirm = $confirmMock;

        $this->confirm->execute();
    }

    public function testExecuteWithRequestMethodTypePostAndParametesDifferentJwt(){
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $this->_dbHelper->expects($this->once())
            ->method('fileGetContents')
            ->willReturn(json_encode([
                "TypeDocument" => "CC",
                "IdDocument" => "1234567890",
                "ValueToPay" => "20000",
                "OrderId" => "1",
                "CreditNumber" => "1234567890",
                "Authentication" => "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.PLdsa_B70GdZtqgXctUARpWvlRqsv2HJITnCd1KHBII",
                "TransactionStatus" => "Terminado",
                "TransactionId" => "1"
            ]));

        $this->_dbHelper->expects($this->once())
            ->method('getSistecreditoOrderLog')
            ->willReturn([
                "request_token"=>  "token",
                "total_order" => "20000",

            ]);

        $this->_dbHelper->expects($this->once())
            ->method('decodeJwt')
            ->willReturn((object)["aud"=>"https://www.google2.com"]);

        $this->urlInterface->expects($this->any())
            ->method('getBaseUrl')
            ->willReturn("https://www.google.com");

        $resultfactory = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resultJson = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->disableOriginalConstructor()
            ->getMock();



        $resultfactory->expects($this->once())
            ->method('create')
            ->willReturn($resultJson);

        $this->context->expects($this->any())
            ->method('getResultFactory')
            ->willReturn($resultfactory);

        // Mock Order object
        $mockOrder = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockOrder->method('getState')
            ->willReturn("Terminado");

        // Define what the loadByIncrementId method should return
        $mockOrder->method('loadByIncrementId')
            ->willReturn($mockOrder);

        $mockPayment = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockPayment->method('setAmountCanceled')
            ->willReturnSelf();

        $mockOrder->method('getPayment')
            ->willReturn($mockPayment);

        $mockOrder->method('setState')
            ->willReturnSelf();

        $mockOrder->method('setStatus')
            ->willReturnSelf();

        $mockOrder->method('save')
            ->willReturnSelf();

        $mockOrder->method('registerCancellation')
            ->willReturnSelf();


        // Define what the create method should return
        $this->orderFactory->method('create')
            ->willReturn($mockOrder);

        $this->_dbHelper->expects($this->once())
            ->method('getShopDomainSsl')
            ->willReturn("https://www.google.com");

        $confirmMock = new Confirm(
            $this->context,
            $this->urlInterface,
            $this->orderFactory,
            $this->gatewayEndpoint,
            $this->checkoutSession,
            $this->_dbHelper,
            $this->scopeConfig
        );

        $this->confirm = $confirmMock;

        $this->confirm->execute();
    }

    /**
     * @dataProvider boolState
     */
    public function testExecuteWithRequestMethodTypePostAndParametesNotNullAndStatePending(){
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $this->_dbHelper->expects($this->once())
            ->method('fileGetContents')
            ->willReturn(json_encode([
                "TypeDocument" => "CC",
                "IdDocument" => "1234567890",
                "ValueToPay" => "20000",
                "OrderId" => "1",
                "CreditNumber" => "1234567890",
                "Authentication" => "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.PLdsa_B70GdZtqgXctUARpWvlRqsv2HJITnCd1KHBII",
                "TransactionStatus" => "Terminado",
                "TransactionId" => "1"
            ]));

        $this->_dbHelper->expects($this->once())
            ->method('getSistecreditoOrderLog')
            ->willReturn(null);

        $this->_dbHelper->expects($this->once())
            ->method('decodeJwt')
            ->willReturn((object)["aud"=>"https://www.google.com"]);

        $this->urlInterface->expects($this->any())
            ->method('getBaseUrl')
            ->willReturn("https://www.google.com");

        $resultfactory = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resultJson = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->disableOriginalConstructor()
            ->getMock();



        $resultfactory->expects($this->once())
            ->method('create')
            ->willReturn($resultJson);

        $this->context->expects($this->any())
            ->method('getResultFactory')
            ->willReturn($resultfactory);

        // Mock Order object
        $mockOrder = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockOrder->method('getState')
            ->willReturn("pending");

        $mockOrder->method('getId')
            ->willReturn(1);

        // Define what the loadByIncrementId method should return
        $mockOrder->method('loadByIncrementId')
            ->willReturn($mockOrder);

        $mockPayment = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockPayment->method('setAmountCanceled')
            ->willReturnSelf();

        $mockOrder->method('getPayment')
            ->willReturn($mockPayment);

        $mockOrder->method('setState')
            ->willReturnSelf();

        $mockOrder->method('setStatus')
            ->willReturnSelf();

        $mockOrder->method('save')
            ->willReturnSelf();

        $mockOrder->method('registerCancellation')
            ->willReturnSelf();

        $mockOrder->expects($this->once())
            ->method('canInvoice')
            ->willReturn(true);

        // Define what the create method should return
        $this->orderFactory->method('create')
            ->willReturn($mockOrder);

        $mockInvoice = $this->getMockBuilder(\Magento\Sales\Model\Order\Invoice::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockInvoice->method('getTotalQty')
            ->willReturn(false);

        $mockInvoice->method('getOrder')
            ->willReturn($mockOrder);

// Crear un objeto ficticio de la clase InvoiceService
        $mockInvoiceService = $this->getMockBuilder(\Magento\Sales\Model\Service\InvoiceService::class)
            ->disableOriginalConstructor()
            ->getMock();

// Configurar el objeto ficticio de InvoiceService para que prepareInvoice devuelva el objeto ficticio de Invoice
        $mockInvoiceService->method('prepareInvoice')->willReturn($mockInvoice);



// Crear un objeto ficticio de la clase ObjectManager
        $mockObjectManager = $this->getMockBuilder(\Magento\Framework\App\ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();


// Configurar el objeto ficticio de ObjectManager para que create devuelva el objeto ficticio de InvoiceService
        $mockObjectManager->method('create')->willReturn($mockInvoiceService);



        $mockObjectManager3 = $this->getMockBuilder(\Magento\Framework\App\ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockInvoiceService3 = $this->getMockBuilder('Magento\Framework\App\ResourceConnection')
            ->disableOriginalConstructor()
            ->getMock();

        $resultConnection = $this->getMockBuilder('Magento\Framework\DB\Adapter\AdapterInterface')
            ->disableOriginalConstructor()
            ->getMock();

        $resultConnection->method('fetchAll')->willReturnOnConsecutiveCalls(
            [
                [
                    "sku" => "sku",
                ]
            ],
            [
                [
                    "MAX(reservation_id)" => "1",
                ]
            ]
        );

        $resultConnection->method('update')->willReturnSelf();

        $mockInvoiceService3->method('getConnection')
            ->willReturn($resultConnection);

        $mockObjectManager3->method('get')
            ->willReturn($mockInvoiceService3);

        $this->_dbHelper->expects($this->any())
            ->method('getObjectManager')
            ->willReturnOnConsecutiveCalls($mockObjectManager,$mockObjectManager3);


        $confirmMock = new Confirm(
            $this->context,
            $this->urlInterface,
            $this->orderFactory,
            $this->gatewayEndpoint,
            $this->checkoutSession,
            $this->_dbHelper,
            $this->scopeConfig
        );

        $this->confirm = $confirmMock;

        $this->confirm->execute();
    }

    public function testExecuteWithRequestMethodTypePostAndParametesNotNullAndInvalidValue(){
        $_SERVER['REQUEST_METHOD'] = 'POST';

        $this->_dbHelper->expects($this->once())
            ->method('fileGetContents')
            ->willReturn(json_encode([
                "TypeDocument" => "CC",
                "IdDocument" => "1234567890",
                "ValueToPay" => "10000",
                "OrderId" => "1",
                "CreditNumber" => "1234567890",
                "Authentication" => "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJzdWIiOiIxMjM0NTY3ODkwIiwibmFtZSI6IkpvaG4gRG9lIiwiaWF0IjoxNTE2MjM5MDIyfQ.PLdsa_B70GdZtqgXctUARpWvlRqsv2HJITnCd1KHBII",
                "TransactionStatus" => "Terminado",
                "TransactionId" => "1"
            ]));

        $this->_dbHelper->expects($this->once())
            ->method('getSistecreditoOrderLog')
            ->willReturn([
                "request_token"=>  "token",
                "total_order" => "20000",

            ]);

        $this->_dbHelper->expects($this->once())
            ->method('decodeJwt')
            ->willReturn((object)["aud"=>"https://www.google.com"]);

        $this->urlInterface->expects($this->any())
            ->method('getBaseUrl')
            ->willReturn("https://www.google.com");

        $resultfactory = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resultJson = $this->getMockBuilder(\Magento\Framework\Controller\Result\Json::class)
            ->disableOriginalConstructor()
            ->getMock();



        $resultfactory->expects($this->once())
            ->method('create')
            ->willReturn($resultJson);

        $this->context->expects($this->any())
            ->method('getResultFactory')
            ->willReturn($resultfactory);

        // Mock Order object
        $mockOrder = $this->getMockBuilder(\Magento\Sales\Model\Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockOrder->method('getState')
            ->willReturn("Terminado");

        // Define what the loadByIncrementId method should return
        $mockOrder->method('loadByIncrementId')
            ->willReturn($mockOrder);

        $mockPayment = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockPayment->method('setAmountCanceled')
            ->willReturnSelf();

        $mockOrder->method('getPayment')
            ->willReturn($mockPayment);

        $mockOrder->method('setState')
            ->willReturnSelf();

        $mockOrder->method('setStatus')
            ->willReturnSelf();

        $mockOrder->method('save')
            ->willReturnSelf();

        $mockOrder->method('registerCancellation')
            ->willReturnSelf();

        $mockOrder->expects($this->never())
            ->method('canInvoice')
            ->willReturn(true);

        // Define what the create method should return
        $this->orderFactory->method('create')
            ->willReturn($mockOrder);

        $mockInvoice = $this->getMockBuilder(\Magento\Sales\Model\Order\Invoice::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockInvoice->method('getTotalQty')
            ->willReturn(1);

        $mockInvoice->method('getOrder')
            ->willReturn($mockOrder);

// Crear un objeto ficticio de la clase InvoiceService
        $mockInvoiceService = $this->getMockBuilder(\Magento\Sales\Model\Service\InvoiceService::class)
            ->disableOriginalConstructor()
            ->getMock();

// Configurar el objeto ficticio de InvoiceService para que prepareInvoice devuelva el objeto ficticio de Invoice
        $mockInvoiceService->method('prepareInvoice')->willReturn($mockInvoice);



// Crear un objeto ficticio de la clase ObjectManager
        $mockObjectManager = $this->getMockBuilder(\Magento\Framework\App\ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockInvoiceService2 = $this->getMockBuilder("Magento\Framework\DB\Transaction")
            ->disableOriginalConstructor()
            ->getMock();

        $mockInvoiceService2->method('addObject')->willReturnSelf();

// Configurar el objeto ficticio de ObjectManager para que create devuelva el objeto ficticio de InvoiceService
        $mockObjectManager->method('create')->willReturn($mockInvoiceService);

        $mockObjectManager2 = $this->getMockBuilder(\Magento\Framework\App\ObjectManager::class)
            ->disableOriginalConstructor()
            ->getMock();

        $mockObjectManager2->method('create')->willReturn($mockInvoiceService2);

        $this->_dbHelper->expects($this->any())
            ->method('getObjectManager')
            ->willReturnOnConsecutiveCalls($mockObjectManager, $mockObjectManager2);

        $confirmMock = new Confirm(
            $this->context,
            $this->urlInterface,
            $this->orderFactory,
            $this->gatewayEndpoint,
            $this->checkoutSession,
            $this->_dbHelper,
            $this->scopeConfig
        );

        $this->confirm = $confirmMock;

        $this->confirm->execute();
    }
}
