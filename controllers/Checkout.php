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
use Arikaim\Core\Interfaces\Content\ContentItemInterface;

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
        $dataId = $data->get('id');
        $extensionName = $data->get('extension',null);

        $driver = $this->get('driver')->create($driverName);

        if (\is_object($driver) == false) {
            // not valid checkout dirver name 
            $error = 'Not valid checkout driver name.';      
            return $this->pageLoad($request,$response,$error,'checkout>checkout.error',$language);      
        }

        // Create checkout data form event subscriber
        list($checkoutData) = $this->get('event')->dispatch('checkout.create',[
            'order_id'          => \trim($dataId),
            'checkout_driver'   => \trim($driverName)              
        ],false,'orders');
    
        if (($checkoutData instanceof ContentItemInterface) == false) {
            // not valid checkout data
            $data['error_message'] = 'Not valid checkout data.';             
            return $this->pageLoad($request,$response,$data,'checkout>checkout.error',$language);      
        }

        // process
        $checkoutResponse = $driver->checkout($checkoutData);  

        if ($checkoutResponse->hasError() == true) {
            $data['error_message'] = $checkoutResponse->getError();             
            return $this->pageLoad($request,$response,$data,'checkout>checkout.error',$language); 
        }
      
        // token update  
        $checkoutData->setValue('token',$checkoutResponse->getToken());
        $checkoutData->setValue('checkout_driver',\trim($driverName));
        $this->get('event')->dispatch('checkout.token.update',$checkoutData->toArray());
             
        if ($checkoutResponse->isRedirect() == true) {
            return $this->withRedirect($response,$checkoutResponse->getRedirectUrl());
        }
     
        // show error page   
        if ($checkoutResponse->hasError() == true) {
            $data['error_message'] = $checkoutResponse->getError();
            return $this->pageLoad($request,$response,$data,'checkout>checkout.error',$language);
        }
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
        $token = $params['token'] ?? null;
        $defaultDriver = $this->get('options')->get('checkout.default.driver');   
        $driverName = $data->get('name',$defaultDriver);  
        $driver = $this->get('driver')->create($driverName);
       
        // Create checkout data form event subscriber
        list($checkoutData) = $this->get('event')->dispatch('checkout.create',[
            'token'             => \trim($token),
            'checkout_driver'   => \trim($driverName)           
        ],false,'orders');

        if (($checkoutData instanceof ContentItemInterface) == false) {
            // not valid checkout data
            $data['error_message'] = 'Not valid checkout data.';             
            return $this->pageLoad($request,$response,$data,'checkout>checkout.error',$language);      
        }

        $transaction = $driver->completeCheckout($checkoutData);

        if ($transaction == null) {
            // show error page      
            $data['message'] = 'Error complete checkout';
            return $this->pageLoad($request,$response,$data,'checkout>checkout.error',$language);
        }
      
        $model = Model::Transactions('checkout');
        $model->saveTransaction($transaction);

        $checkoutData->setValue('transaction_id',$transaction->getTransactionId());

        $this->get('event')->dispatch('checkout.success',$checkoutData->toArray());

        return $this->pageLoad($request,$response,$data,'checkout>checkout.success',$language);
    }

    /**
     * Checkout cancel page
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function checkoutCancel($request, $response, $data) 
    {               
        $language = $this->getPageLanguage($data);
        $token = $params['token'] ?? null;
        $checkoutData = (empty($token) == false) ? $this->get('content')->type('checkout')->get($token) : null;  
        
        $data['checkout'] = $checkoutData;
        $data['token'] = $token;

        $this->get('event')->dispatch('checkout.cancel',$checkoutData ?? []);

        return $this->pageLoad($request,$response,$data,'checkout>checkout.cancel',$language);
    }
}
