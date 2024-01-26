<?php
namespace MagentoSistecredito\SistecreditoPaymentGateway\Observer;

use Magento\Framework\Event\Observer;
use Magento\Payment\Observer\AbstractDataAssignObserver;

class DataAssignObserver extends AbstractDataAssignObserver
{
    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $method = $this->readMethodArgument($observer);
        $data = $this->readDataArgument($observer);
        $paymentInfo = $method->getInfoInstance();

        if ($data->getDataByPath('additional_data/id_type') !== null) {
            $paymentInfo->setAdditionalInformation(
                'id_type',
                $data->getDataByPath('additional_data/id_type')
            );
        }
        if ($data->getDataByPath('additional_data/id_number') !== null) {
            $paymentInfo->setAdditionalInformation(
                'id_number',
                $data->getDataByPath('additional_data/id_number')
            );
        }
    }
}
