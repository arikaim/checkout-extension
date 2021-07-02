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

use Arikaim\Core\Controllers\Controller;
use Arikaim\Core\Db\Model;

/**
 * Checkout pages controler
*/
class Checkout extends Controller
{
    /**
     * Checkout page
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function checkout($request, $response, $data) 
    { 
        $language = $this->getPageLanguage($data);

        $driverName = $data->get('name','paypal');
        $dataId = $data->get('id','current');
        $driver = $this->get('driver')->create($driverName);

        $cart = [
            'items' => [
                'name'  => 'Product 1',
                'price' => 10,
                'desc'  => 'Description for product 1',
                'qty'   => 1
            ],
            'total' => 10
        ];

        $cartData = $this->get('event')->dispatch('checkout.init',['id' => $dataId]);  
        

     

        if ($driver->isSuccess($result) == true) {
            $checkoutUrl = $driver->getCheckoutUrl($result,$dataId);
            
            return $this->withRedirect($response,$checkoutUrl);
        } 

        // show error page      
        return $this->pageLoad($request,$response,$data,'checkout>checkout.error',$language);
    }

    /**
     * Checkout success
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function checkoutSuccess($request, $response, $data) 
    {               
        $language = $this->getPageLanguage($data);
        
        $driverName = $data->get('name','paypal');
        $dataId = $data->get('id','current');
        $driver = $this->get('driver')->create($driverName);

     
        $cartData = $this->get('event')->dispatch('checkout.init',['id' => $dataId]);  

        $cart = [
            'items' => [
                'name'  => 'Product 1',
                'price' => 10,
                'desc'  => 'Description for product 1',
                'qty'   => 1
            ],
            'total' => 10
        ];

        $transaction = $driver->processCheckout($checkoutData,$cart);
        if ($transaction === false) {     
            return $this->pageLoad($request,$response,$data,'checkout>checkout.error');                
        }
        $model = Model::Transactions('checkout');
        $model->saveTransaction($transaction);

        return $this->pageLoad($request,$response,$data,'checkout>checkout.success');
    }

    /**
     * Checkout cancel
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function checkoutCancelPage($request, $response, $data) 
    {               
        $driverName = $data->get('name','paypal');
        $driver = $this->get('driver')->create($driverName);

        $checkoutData = $data->get('data',null);
        if (empty($checkoutData) == true) {
            $checkoutData = $request->getQueryParams();
        }

        $result = $driver->processCancelCheckout($checkoutData);
        $this->get('event')->dispatch('checkout.cancel',$result);  
    }
}
