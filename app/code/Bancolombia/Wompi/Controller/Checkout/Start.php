<?php

namespace Bancolombia\Wompi\Controller\Checkout;

use Magento\Checkout\Model\Session;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\Order\Payment;
use Magento\Payment\Gateway\ConfigInterface;
class Start extends Action
{
    private $checkoutSession;
    private $config;

    public function __construct(
        Context $context,
        Session $checkoutSession,
        ConfigInterface $config
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->config = $config;

        parent::__construct($context);
    }

    public function execute()
    {
        $order = $this->checkoutSession->getLastRealOrder();
        $shippingAddress = $order->getShippingAddress();
        $order->setData('state', Order::STATE_PENDING_PAYMENT)->save();
        $order->setData('status', Order::STATE_PENDING_PAYMENT)->save();

        if($this->config->getValue('test_mode')==='1') {
            $public_key = $this->config->getValue('wompi_public_key_test');
            $integrity_key = $this->config->getValue('wompi_integrity_key_test');
        } else {
            $public_key = $this->config->getValue('wompi_public_key');
            $integrity_key = $this->config->getValue('wompi_integrity_key');
        }

        $order_id = $order->getIncrementId();
        $amount_in_cents = $order->getBaseGrandTotal() * 100;
        $currency = $order->getBaseCurrencyCode();
        $integrity_signature = hash('sha256', "{$order_id}{$amount_in_cents}{$currency}{$integrity_key}");
        $tax_in_cents = $order->getTaxAmount() * 100;

        echo "
              <head>
              <title>Pagando con Wompi</title>
              <link rel='shortcut icon' href='https://comercios.wompi.co/favicon.png'>
                  <meta charset='utf-8'>

                  <script>
                  window.onload=function(){
                      document.forms['wompi'].submit();
                  }
                  </script>
              </head>
              <body>
             ";

        echo "
                <form name='wompi' action='https://checkout.wompi.co/p/' method='GET'>
             ";

        // OBLIGATORIOS
        echo "
                  <input type='hidden' name='public-key' value='$public_key' />
                  <input type='hidden' name='currency' value='$currency' />
                  <input type='hidden' name='amount-in-cents' value='$amount_in_cents' />
                  <input type='hidden' name='reference' value='$order_id' />
                  <input type='hidden' name='tax-in-cents:vat'  value='$tax_in_cents' />
                  <input type='hidden' name='signature:integrity'  value='$integrity_signature' />
             ";

        // SHIPPING
        echo "
                  <input type='hidden' name='shipping-address:address-line-1' value='{$shippingAddress->getStreet()[0]}' />
                  <input type='hidden' name='shipping-address:country' value='{$shippingAddress->getCountryId()}' />
                  <input type='hidden' name='shipping-address:city' value='{$shippingAddress->getCity()}' />
                  <input type='hidden' name='shipping-address:region' value='{$shippingAddress->getRegion()}' />
                  <input type='hidden' name='shipping-address:phone-number' value='{$shippingAddress->getTelephone()}' />
                  <input type='hidden' name='shipping-address:name' value='{$shippingAddress->getFirstname()} {$shippingAddress->getLastname()}' />
             ";

        // CUSTOMER
        echo "
                  <input type='hidden' name='customer-data:email' value='{$shippingAddress->getEmail()}'/>
                  <input type='hidden' name='customer-data:full-name' value='{$shippingAddress->getFirstname()} {$shippingAddress->getLastname()}' />
                  <input type='hidden' name='customer-data:phone-number-prefix' value='+57' />
                  <input type='hidden' name='customer-data:phone-number' value='{$shippingAddress->getTelephone()}' />
             ";

        $redirect_url = $this->config->getValue('wompiredirect');
        if (!empty($redirect_url)) {
            echo "
                  <input type='hidden' name='redirect-url' value='{$redirect_url}/response/response/result' />
             ";

        }

        echo "
                </form>
              </body>
             ";

    }
}
    ?>
