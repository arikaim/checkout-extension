<?php
/**
 * Arikaim
 *
 * @link        http://www.arikaim.com
 * @copyright   Copyright (c)  Konstantin Atanasov <info@arikaim.com>
 * @license     http://www.arikaim.com/license
 * 
*/
namespace Arikaim\Extensions\Checkout\Controllers;

use Arikaim\Core\Controllers\ApiController;

/**
 * Checkout api controler
*/
class CheckoutApi extends ApiController
{
    /**
     * Checkout notify
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function notifyController($request, $response, $data) 
    {        
        $driverName = $data->get('name','paypal');
        $driver = $this->get('driver')->create($driverName);

        $this->field('provider',$driverName);

        if ($driver->verifyIpnResponse($data->toArray()) == false) {     
            $this->get('logger')->error('IPN',$data->toArray());      
            $this->error('Checkout IPN error');
            return;
        } 

        $this->get('logger')->error('IPN',$data->toArray());
    }
}
