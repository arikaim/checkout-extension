<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Extensions\Checkout;

use Arikaim\Core\Extension\Extension;

/**
 * Checkout extension
*/
class Checkout extends Extension
{
    /**
     * Install extension routes, events, jobs
     *
     * @return void
    */
    public function install()
    {        
        // Control Panel Routes       
        $this->addApiRoute('POST','/api/checkout/admin/transaction/details','CheckoutControlPanel','transctionDetails','session'); 
        // Api               
        $this->addApiRoute('POST','/api/checkout/notify[/{name}]','CheckoutApi','notify'); 

        // Pages
        $this->addPageRoute('/checkout/{name}/{id}[/{type}]','Checkout','checkout','checkout>checkout');    
        $this->addPageRoute('/checkout/success/{name}/[{data}]','Checkout','checkoutSuccess','checkout>checkout.success');   
        $this->addPageRoute('/checkout/cancel/[{data}]','Checkout','checkoutCancel','checkout>checkout.cancel');   
      
        // Events
        $this->registerEvent('checkout.success','Success payment');  
        $this->registerEvent('checkout.cancel','Cancel payment');  
        $this->registerEvent('checkout.notify','IPN notify');  
        $this->registerEvent('checkout.payment','Checkout payment');  
    
        // Create db tables
        $this->createDbTable('TransactionsSchema');
    
        // current checkout driver
        $this->createOption('checkout.default.driver','paypal-express');      
    }   
}
