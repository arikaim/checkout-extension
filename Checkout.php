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
        $this->addPageRoute('/checkout/{driver_name}/{id}[/{extension}[/{options}[/{user}]]]','Checkout','checkout','checkout>checkout');    
        $this->addPageRoute('/checkout/success/{driver_name}/{extension}/[{options}[/{user}]]','Checkout','checkoutSuccess','checkout>checkout.success');   
        $this->addPageRoute('/checkout/cancel/{extension}/[{options}[/{user}]]','Checkout','checkoutCancel','checkout>checkout.cancel');   
        // Events
        $this->registerEvent('checkout.create','Checkout payment create.');  
        $this->registerEvent('checkout.token.update','Checkout token notify.');  
        $this->registerEvent('checkout.success','Success payment');  
        $this->registerEvent('checkout.cancel','Cancel payment');  
        $this->registerEvent('checkout.notify','IPN notify');         
        // Create db tables
        $this->createDbTable('TransactionsSchema');
        
        // Content Types
        $this->registerContentType('Classes\\CheckoutContentType');
        // Content providers
      //  $this->registerContentProvider('Classes\\CheckoutContentProvier');

        // current checkout driver
        $this->createOption('checkout.default.driver','paypal-express');      
    }   
}
