<?php

namespace MagentoSistecredito\SistecreditoPaymentGateway\Test\Unit\Controller;

use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Url;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\OrderFactory;
use MagentoSistecredito\SistecreditoPaymentGateway\Controller\Gateway\GatewayUrl;
use MagentoSistecredito\SistecreditoPaymentGateway\Gateway\Config\Config;
use MagentoSistecredito\SistecreditoPaymentGateway\Helper\DbHelper;
use MagentoSistecredito\SistecreditoPaymentGateway\Helper\GatewayEndpoint;
use PHPUnit\Framework\TestCase;

class GatewayUrlTest extends TestCase
{
    public $_checkoutSession;

    public $_orderFactory;

    public $_gatewayEndpoint;

    public $_dbHelper;

    public $_messageManager;

    public $_gatewayConfig;

    public $_urlInterface;

    protected function setUp(): void
    {
        $this->_checkoutSession = $this->getMockBuilder(Session::class)
            ->disableOriginalConstructor()
            ->addMethods(['getLastRealOrderId'])
            ->onlyMethods(['restoreQuote'])
            ->getMock();

        $this->_orderFactory = $this->getMockBuilder(OrderFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_gatewayEndpoint = $this->getMockBuilder(GatewayEndpoint::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_dbHelper = $this->getMockBuilder(DbHelper::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_messageManager = $this->getMockBuilder(Context::class)
            ->disableOriginalConstructor()
            ->addMethods(['addErrorMessage'])
            ->onlyMethods(['getMessageManager','getResultFactory','getRequest'])
            ->getMock();

        $this->_messageManager->method('getMessageManager')
            ->willReturn(
                $this->getMockBuilder(ManagerInterface::class)
                    ->disableOriginalConstructor()
                    ->getMock()
            );

        $this->_gatewayConfig = $this->getMockBuilder(Config::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_urlInterface = $this->getMockBuilder(Url::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->resultJsonFactory = $this->getMockBuilder(JsonFactory::class)
            ->disableOriginalConstructor()
            ->getMock();

        parent::setUp();
    }

    public function returnOnsameSite(){
        return [
            [true],
            [false],
        ];
    }

    /**
     * @dataProvider returnOnsameSite
     */
    public function testExecuteTypeDocumentOrDocumentEmpty($onSameSite)
    {

        $resultJson = $this->getMockBuilder(JsonFactory::class)
            ->disableOriginalConstructor()
            ->addMethods(['setHttpResponseCode'])
            ->getMock();


        $resultJson->method('setHttpResponseCode')
            ->willReturn((object) array('result' => 'failed'));

        $this->resultJsonFactory->method('create')->willReturn(
            $resultJson
        );

        $resultFactory = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $resultRedirect = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resultRedirect
            ->method('setPath')
            ->willReturn((object) array('result' => 'failed','checkout/cart'=>['_secure' => false]));

        $resultFactory->expects($this->atLeastOnce())
            ->method('create')
            ->with(ResultFactory::TYPE_REDIRECT)
            ->willReturn($resultRedirect);

        $this->_messageManager
            ->expects($this->once())
            ->method('getResultFactory')
            ->willReturn($resultFactory);

        $mockOrder = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_checkoutSession
            ->expects($this->once())
            ->method('getLastRealOrderId')
            ->willReturn($mockOrder);

        // Define what the loadByIncrementId method should return
        $mockOrder
            ->method('loadByIncrementId')
            ->willReturnSelf();

        $mockOrder
            ->method('getId')
            ->willReturn(1);

        $mockOrder
            ->method('getRealOrderId')
            ->willReturn('000000001');

        $mockOrder
            ->expects($this->exactly(2))
            ->method('getGrandTotal')
            ->willReturn(100);

        $payment = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $payment->expects($this->exactly(1))
            ->method('setAmountCanceled')
            ->willReturnSelf();

        $mockOrder->method('getPayment')
            ->willReturn($payment);

        $mockOrder
            ->method('setState')
            ->willReturnSelf();

        $mockOrder->expects($this->exactly(1))
            ->method('save')
            ->willReturnSelf();

        $mockOrder->expects($this->exactly(1))
            ->method('registerCancellation')
            ->willReturnSelf();

        $this->_orderFactory->method('create')
            ->willReturn($mockOrder);

        $mockRequest = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Configurar el objeto ficticio de Request para que getParam("typeDocument") devuelva el valor que necesitas
        $mockRequest->expects($this->exactly(3))
            ->method('getParam')
            ->willReturnOnConsecutiveCalls('', '',$onSameSite);

        $this->_messageManager->method('getRequest')
            ->willReturn($mockRequest);

        $this->_dbHelper->expects($this->exactly(2))
            ->method('createSistecreditoOrderLog')
            ->willReturn((object) ['result' => 'success']);

        $this->_checkoutSession->expects($this->once())
            ->method('restoreQuote')
            ->willReturnSelf();

        $this->_messageManager
            ->method('addErrorMessage')
            ->willReturnSelf();

        $gatewayUrl = new GatewayUrl(
            $this->_checkoutSession,
            $this->_orderFactory,
            $this->_gatewayEndpoint,
            $this->_dbHelper,
            $this->_messageManager,
            $this->_gatewayConfig,
            $this->_urlInterface,
            $this->resultJsonFactory
        );

        $return = $gatewayUrl->execute();
        $this->assertEquals('failed', $return->result);
    }

    public function testExecuteTypeDocumentAndDocumentNotEmptyAndOnSameSite(){
        $resultJson = $this->getMockBuilder(JsonFactory::class)
            ->disableOriginalConstructor()
            ->addMethods(['setHttpResponseCode','setData'])
            ->getMock();


        $resultJson->method('setData')
            ->willReturn((object) array('result' => 'success'));

        $this->resultJsonFactory->method('create')->willReturn(
            $resultJson
        );

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

        $this->_messageManager
            ->expects($this->once())
            ->method('getResultFactory')
            ->willReturn($resultFactory);

        $mockOrder = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_checkoutSession
            ->expects($this->once())
            ->method('getLastRealOrderId')
            ->willReturn($mockOrder);

        // Define what the loadByIncrementId method should return
        $mockOrder
            ->method('loadByIncrementId')
            ->willReturnSelf();

        $mockOrder
            ->method('getId')
            ->willReturn(1);

        $mockOrder
            ->method('getRealOrderId')
            ->willReturn('000000001');

        $mockOrder
            ->expects($this->exactly(1))
            ->method('getGrandTotal')
            ->willReturn(100);

        $payment = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $payment->expects($this->exactly(0))
            ->method('setAmountCanceled')
            ->willReturnSelf();

        $mockOrder->method('getPayment')
            ->willReturn($payment);

        $mockOrder
            ->method('setState')
            ->willReturnSelf();

        $mockOrder->expects($this->exactly(0))
            ->method('save')
            ->willReturnSelf();

        $mockOrder->expects($this->exactly(0))
            ->method('registerCancellation')
            ->willReturnSelf();

        $this->_orderFactory->method('create')
            ->willReturn($mockOrder);

        $mockRequest = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Configurar el objeto ficticio de Request para que getParam("typeDocument") devuelva el valor que necesitas
        $mockRequest->expects($this->exactly(3))
            ->method('getParam')
            ->willReturnOnConsecutiveCalls('CC', '1066523848',true);

        $this->_messageManager->method('getRequest')
            ->willReturn($mockRequest);

        $this->_dbHelper->expects($this->exactly(2))
            ->method('createSistecreditoOrderLog')
            ->willReturn((object) ['result' => 'success']);

        $this->_gatewayConfig->expects($this->once())
            ->method('getVisorUrl')
            ->willReturn('http://localhost:8080');

        $this->_urlInterface->expects($this->exactly(2))
            ->method('getBaseUrl')
            ->willReturn('http://localhost:8080');

        $gatewayUrl = new GatewayUrl(
            $this->_checkoutSession,
            $this->_orderFactory,
            $this->_gatewayEndpoint,
            $this->_dbHelper,
            $this->_messageManager,
            $this->_gatewayConfig,
            $this->_urlInterface,
            $this->resultJsonFactory
        );

        $return = $gatewayUrl->execute();
        $this->assertEquals('success', $return->result);
    }

    public function testExecuteTypeDocumentAndDocumentNotEmptyAndDontOnSameSite(){
        $resultJson = $this->getMockBuilder(JsonFactory::class)
            ->disableOriginalConstructor()
            ->getMock();


        $this->resultJsonFactory->method('create')->willReturn(
            $resultJson
        );

        $resultFactory = $this->getMockBuilder(ResultFactory::class)
            ->disableOriginalConstructor()
            ->onlyMethods(['create'])
            ->getMock();

        $resultRedirect = $this->getMockBuilder(Redirect::class)
            ->disableOriginalConstructor()
            ->getMock();

        $resultRedirect->expects($this->once())
            ->method('setUrl')
            ->willReturn((object) array('result' => 'success'));

        $resultFactory->expects($this->atLeastOnce())
            ->method('create')
            ->with(ResultFactory::TYPE_REDIRECT)
            ->willReturn($resultRedirect);

        $this->_messageManager
            ->expects($this->once())
            ->method('getResultFactory')
            ->willReturn($resultFactory);

        $mockOrder = $this->getMockBuilder(Order::class)
            ->disableOriginalConstructor()
            ->getMock();

        $this->_checkoutSession
            ->expects($this->once())
            ->method('getLastRealOrderId')
            ->willReturn($mockOrder);

        // Define what the loadByIncrementId method should return
        $mockOrder
            ->method('loadByIncrementId')
            ->willReturnSelf();

        $mockOrder
            ->method('getId')
            ->willReturn(1);

        $mockOrder
            ->method('getRealOrderId')
            ->willReturn('000000001');

        $mockOrder
            ->expects($this->exactly(1))
            ->method('getGrandTotal')
            ->willReturn(100);

        $payment = $this->getMockBuilder(\Magento\Sales\Model\Order\Payment::class)
            ->disableOriginalConstructor()
            ->getMock();

        $payment->expects($this->exactly(0))
            ->method('setAmountCanceled')
            ->willReturnSelf();

        $mockOrder->method('getPayment')
            ->willReturn($payment);

        $mockOrder
            ->method('setState')
            ->willReturnSelf();

        $mockOrder->expects($this->exactly(0))
            ->method('save')
            ->willReturnSelf();

        $mockOrder->expects($this->exactly(0))
            ->method('registerCancellation')
            ->willReturnSelf();

        $this->_orderFactory->method('create')
            ->willReturn($mockOrder);

        $mockRequest = $this->getMockBuilder(\Magento\Framework\App\Request\Http::class)
            ->disableOriginalConstructor()
            ->getMock();

        // Configurar el objeto ficticio de Request para que getParam("typeDocument") devuelva el valor que necesitas
        $mockRequest->expects($this->exactly(3))
            ->method('getParam')
            ->willReturnOnConsecutiveCalls('CC', '1066523848',false);

        $this->_messageManager->method('getRequest')
            ->willReturn($mockRequest);

        $this->_dbHelper->expects($this->exactly(1))
            ->method('createSistecreditoOrderLog')
            ->willReturn((object) ['result' => 'success']);

        $this->_gatewayEndpoint->expects($this->once())
            ->method('getPaymentProcessUrl')
            ->willReturn('http://localhost:8080');

        $gatewayUrl = new GatewayUrl(
            $this->_checkoutSession,
            $this->_orderFactory,
            $this->_gatewayEndpoint,
            $this->_dbHelper,
            $this->_messageManager,
            $this->_gatewayConfig,
            $this->_urlInterface,
            $this->resultJsonFactory
        );

        $return = $gatewayUrl->execute();
        $this->assertEquals('success', $return->result);
    }
}
