<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace MagentoSistecredito\SistecreditoPaymentGateway\Test\Unit\Observer;

use Magento\Framework\DataObject;
use Magento\Framework\Event;
use Magento\Payment\Model\InfoInterface;
use Magento\Payment\Model\MethodInterface;
use Magento\Payment\Observer\AbstractDataAssignObserver;
use MagentoSistecredito\SistecreditoPaymentGateway\Gateway\Http\Client\ClientMock;
use MagentoSistecredito\SistecreditoPaymentGateway\Observer\DataAssignObserver;

class DataAssignObserverTest extends \PHPUnit\Framework\TestCase
{
    public function testExectute()
    {
        $observerContainer = $this->getMockBuilder(Event\Observer::class)
            ->disableOriginalConstructor()
            ->getMock();
        $event = $this->getMockBuilder(Event::class)
            ->disableOriginalConstructor()
            ->getMock();
        $paymentMethodFacade = $this->getMockBuilder(MethodInterface::class)->getMock();
        $paymentInfoModel = $this->getMockBuilder(InfoInterface::class)->getMock();
        $dataObject = new DataObject(
            [
                'transaction_result' => "success"
            ]
        );

        $observerContainer->expects(static::atLeastOnce())
            ->method('getEvent')
            ->willReturn($event);
        $event->expects(static::exactly(2))
            ->method('getDataByKey')
            ->willReturnMap(
                [
                    [AbstractDataAssignObserver::METHOD_CODE, $paymentMethodFacade],
                    [AbstractDataAssignObserver::DATA_CODE, $dataObject]
                ]
            );

        $paymentMethodFacade->expects(static::once())
            ->method('getInfoInstance')
            ->willReturn($paymentInfoModel);

        $paymentInfoModel->expects(static::never())
            ->method('setAdditionalInformation')
            ->with(
                'transaction_result',
                "success"
            );

        $observer = new DataAssignObserver();
        $observer->execute($observerContainer);
    }
}
