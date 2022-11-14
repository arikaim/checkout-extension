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
        $driverName = $data->get('driver_name',$defaultDriver);       
        $dataId = $data->get('id');
        $extensionName = $data->get('extension',null);
        $options       = $data->get('options');
        $userId        = $data->get('user');
      
        $driver = $this->get('driver')->create($driverName);
        if (\is_object($driver) == false) {
            // not valid checkout dirver name 
            $error = 'Not valid checkout driver name.';      
            return $this->pageLoad($request,$response,$error,'checkout>checkout.error',$language);      
        }

        // Create checkout data form event subscriber
        list($checkoutData) = $this->get('event')->dispatch('checkout.create',[
            'order_id'          => \trim($dataId),
            'extension'         => $extensionName,
            'options'           => $options,
            'user_id'           => $userId,
            'checkout_driver'   => \trim($driverName)              
        ],false,($extensionName == 'all' || empty($extensionName) == true) ? null : $extensionName);
    
        if (($checkoutData instanceof ContentItemInterface) == false) {
            // not valid checkout data
            $data['error_message'] = 'Not valid checkout data.';             
            return $this->pageLoad($request,$response,$data,'current>checkout.error',$language);      
        }

        // process
        $checkoutResponse = $driver->checkout($checkoutData);  

        if ($checkoutResponse->hasError() == true) {
            $data['error_message'] = $checkoutResponse->getError();             
            return $this->pageLoad($request,$response,$data,'current>checkout.error',$language); 
        }
      
        // token update  
        $checkoutData->setValue('token',$checkoutResponse->getToken());
        $checkoutData->setValue('checkout_driver',\trim($driverName));
      
        $this->get('event')->dispatch('checkout.token.update',$checkoutData->toArray(),false,$extensionName);
             
        if ($checkoutResponse->isRedirect() == true) {
            return $this->withRedirect($response,$checkoutResponse->getRedirectUrl());
        }
     
        // show error page   
        if ($checkoutResponse->hasError() == true) {
            $data['error_message'] = $checkoutResponse->getError();
            return $this->pageLoad($request,$response,$data,'current>checkout.error',$language);
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
        $extensionName = $data->get('extension',null);  
        $params = $request->getQueryParams();    
        $token = $params['token'] ?? null;
        $defaultDriver = $this->get('options')->get('checkout.default.driver');   
        $driverName = $data->get('driver_name',$defaultDriver);  
        $driver = $this->get('driver')->create($driverName);
       
        // Create checkout data form event subscriber
        list($checkoutData) = $this->get('event')->dispatch('checkout.create',[
            'token'             => \trim($token),
            'checkout_driver'   => \trim($driverName)           
        ],false,$extensionName);

        if (($checkoutData instanceof ContentItemInterface) == false) {
            // not valid checkout data
            $data['error_message'] = 'Not valid checkout data.';             
            return $this->pageLoad($request,$response,$data,'checkout>checkout.error',$language);      
        }

        $transaction = $driver->completeCheckout($checkoutData);

        if ($transaction == null) {
            // show error page      
            $data['error_message'] = 'Error. Order checkout transaction not valid.';
            return $this->pageLoad($request,$response,$data,'checkout>checkout.error',$language);
        }
      
        $model = Model::Transactions('checkout');
        $model->saveTransaction($transaction,$this->getUserId());

        $checkoutData->setValue('transaction_id',$transaction->getTransactionId());

        $this->get('event')->dispatch('checkout.success',$checkoutData->toArray(),false,$extensionName);

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
        $extensionName = $data->get('extension',null);  
        $token = $params['token'] ?? null;
    
        $this->get('event')->dispatch('checkout.cancel',[
            'token' => $token
        ],false,$extensionName);

        return $this->pageLoad($request,$response,$data,'checkout>checkout.cancel',$language);
    }
}
