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
use Arikaim\Core\Http\Url;
use Arikaim\Modules\Checkout\CheckoutData;

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
        $defaultDriver = $this->get('options')->get('checkout.default.driver');   
        $driverName = $data->get('name',$defaultDriver);
        $driver = $this->get('driver')->create($driverName);
        $dataId = $data->get('id');
        $dataType = $data->get('type','checkout');

        // try from event 
        $checkoutData = $this->get('event')->dispatch('checkout.success',[
            'id'   => $dataId,
            'type' => $dataType
        ]);

        if (\is_object($checkoutData) == false) {
            // try from content system // TODO
            $checkoutData = CheckoutData::create(12.00,'USD','order id: ' . time());
        }
     
        $resp = $driver->checkout($checkoutData);          

        if ($resp->isRedirect() == true) {
            $this->get('event')->dispatch('checkout.payment',$checkoutData->toArray());
            $url = $resp->getRedirectUrl();

            return $this->withRedirect($response,$url);
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
        $params = $request->getQueryParams();    
        $defaultDriver = $this->get('options')->get('checkout.default.driver');   
        $driverName = $data->get('name',$defaultDriver);  

        $driver = $this->get('driver')->create($driverName);
       
        $transaction = $driver->completeCheckout($params);

        if ($transaction == null) {
            // show error page      
            return $this->pageLoad($request,$response,$data,'checkout>checkout.error',$language);
        }
      
        $model = Model::Transactions('checkout');
        $model->saveTransaction($transaction);

        $this->get('event')->dispatch('checkout.success',$transaction->toArray());

        return $this->pageLoad($request,$response,$data,'checkout>checkout.success',$language);
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
        $language = $this->getPageLanguage($data);
        $checkoutData = $data->get('data',$request->getQueryParams());
    
        $this->get('event')->dispatch('checkout.cancel',$checkoutData);

        return $this->pageLoad($request,$response,$data,'checkout>checkout.cancel',$language);
    }
}
