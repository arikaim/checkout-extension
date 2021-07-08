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
        $driver = $this->get('driver')->create($driverName);
        $dataId = $data->get('id');
        $dataType = $data->get('type','checkout');

        if (empty($driver) == true) {
            // not valid checkout dirver name 
            $data['message'] = 'Not valid checkout driver name.';              
            return $this->pageLoad($request,$response,$data,'checkout>checkout.error',$language);      
        }

        // try from event 
        $checkoutData = $this->get('event')->dispatch('checkout.create',[
            'id'   => $dataId,
            'type' => $dataType
        ]);
        
        if (\is_object($checkoutData) == false) {
            // try from content system
            if ($this->get('content')->hasContentType($dataType) == false) {
                // not valid data type
                $data['message'] = 'Not valid daat type name.';              
                return $this->pageLoad($request,$response,$data,'checkout>checkout.error',$language);  
            }
          
            $checkoutData = $this->get('content')->type($dataType)->get($dataId);            
        }
        

        if (($checkoutData instanceof ContentItemInterface) == false) {
            // not valie checkout data
            $data['message'] = 'Not valid checkout data.';             
            return $this->pageLoad($request,$response,$data,'checkout>checkout.error',$language);      
        }

        if ($dataType != 'checkout') {
            $checkoutData = $checkoutData->runAction('convert.checkout');
        }

        if ($checkoutData == null) {
            // not valie checkout data
            $data['message'] = 'Not valid checkout data.';             
            return $this->pageLoad($request,$response,$data,'checkout>checkout.error',$language); 
        }

        // process
        $resp = $driver->checkout($checkoutData);    

        $errorMessage = $resp->getMessage();
        if (empty($errorMessage) == false) {
            $data['message'] = $errorMessage;             
            return $this->pageLoad($request,$response,$data,'checkout>checkout.error',$language); 
        }
      
        // save checkout item
        $token = $resp->getTransactionReference();
        $checkoutData->setValue('token',$token);
        $this->get('content')->provider('checkout')->saveItem($token,$checkoutData->toArray());
      
        if ($resp->isRedirect() == true) {
            $this->get('event')->dispatch('checkout.payment',$checkoutData->toArray());
            return $this->withRedirect($response,$resp->getRedirectUrl());
        }
     
        // show error page      
        $data['message'] = $resp->getMessage();
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
        $token = $params['token'] ?? null;
        $defaultDriver = $this->get('options')->get('checkout.default.driver');   
        $driverName = $data->get('name',$defaultDriver);  
        $driver = $this->get('driver')->create($driverName);
       
        $checkoutData = $this->get('content')->type('checkout')->get($token);   

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
