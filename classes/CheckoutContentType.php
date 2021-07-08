<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Extensions\Checkout\Classes;

use Arikaim\Core\Content\Type\ContentType;

/**
 * Checkout content type class
*/
class CheckoutContentType extends ContentType 
{
    /**
     * Define address type
     *
     * @return void
     */
    protected function define(): void
    {
        $this->setName('checkout');
        $this->setTitle('Checkout Data');
        // fields
        $this->addField('id','text','Id');
        $this->addField('token','text','Token');
        $this->addField('amount','number','Amount');
        $this->addField('currency','text','Currency Code');       
        $this->addField('order_id','text','Order Id');
        $this->addField('order_type','text','Order Type');
        $this->addField('description','text','Description');  
        $this->addField('transaction_id','text','Checkout Transaction Id');          
    }
}
