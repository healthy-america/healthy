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
            $wompikey = $this->config->getValue('wompikey');
        } else {
            $wompikey = $this->config->getValue('wompikey_p');
        }
        ?>

            <?php ?>
                <head>
                <title>Pagando con Wompi</title> 
                <link rel="shortcut icon" href="https://comercios.wompi.co/favicon.png">
                    <meta charset="utf-8">
                
                    <script>
                    window.onload=function(){
                        document.forms["wompi"].submit();
                    }
                    </script>
                </head> 
                <body>

                    <form name="wompi" action="https://checkout.wompi.co/p/" method="GET">
                            
                    <!-- OBLIGATORIOS -->
                    <input type="hidden" name="public-key" value="<?php print_r ($wompikey) ?>" />
                    <input type="hidden" name="currency" value="<?php print_r ($order->getBaseCurrencyCode())?>" />
                    <input type="hidden" name="amount-in-cents" value="<?php print_r ($order->getBaseGrandTotal()*100)?>" />
                    <input type="hidden" name="reference" value="<?php print_r ($order->getIncrementId()+20211021 )?>" />
                    <input type="hidden" name="tax-in-cents:vat"  value="<?php print_r (($order->getTaxAmount())*100)?>" />
                    <!-- SHIPPING --> 
                    <input type="hidden" name="shipping-address:address-line-1" value="<?php print_r ($shippingAddress->getStreet()[0])?>" />
                    <input type="hidden" name="shipping-address:country" value="<?php print_r ($shippingAddress->getCountryId())?>" />
                    <input type="hidden" name="shipping-address:city" value="<?php print_r ($shippingAddress->getCity())?>" />
                    <input type="hidden" name="shipping-address:region" value="<?php print_r ($shippingAddress->getRegion())?>" /> 
                    <input type="hidden" name="shipping-address:phone-number" value="<?php print_r ($shippingAddress->getTelephone())?>" />
                    <input type="hidden" name="shipping-address:name" value="<?php print_r ($shippingAddress->getFirstname() . ' ' . $shippingAddress->getLastname())?>" />
                    <!-- CUSTOMER -->
                    <input type="hidden" name="customer-data:email" value="<?php print_r ($shippingAddress->getEmail())?>"/>
                    <input type="hidden" name="customer-data:full-name" value="<?php print_r ($shippingAddress->getFirstname() . ' ' . $shippingAddress->getLastname())?>" />
                    <input type="hidden" name="customer-data:phone-number-prefix" value="+57" />
                    <input type="hidden" name="customer-data:phone-number" value="<?php print_r ($shippingAddress->getTelephone())?>" />
                    <input type="hidden" name="redirect-url" value="<?php print_r ($this->config->getValue('wompiredirect') . "/response/response/result")?>" />
                    </form>
                    
                    </body>

            <?php  ?>
     <?php                        

                
    }
}
    ?>


    