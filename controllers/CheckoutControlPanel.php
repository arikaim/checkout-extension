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
use Arikaim\Core\Db\Model;

/**
 * Checkout control panel api controler
*/
class CheckoutControlPanel extends ApiController
{
    /**
     * Init controller
     *
     * @return void
     */
    public function init()
    {
        $this->loadMessages('checkout::admin.messages');
    }

    /**
     * Checkout transaction details
     *
     * @param \Psr\Http\Message\ServerRequestInterface $request
     * @param \Psr\Http\Message\ResponseInterface $response
     * @param Validator $data
     * @return Psr\Http\Message\ResponseInterface
    */
    public function transctionDetailsController($request, $response, $data) 
    {        
        $this->requireControlPanelPermission();

        $this->onDataValid(function($data) { 
            $uuid = $data->get('uuid');
            $driverName = $data->get('driver_name',null);

            $model = Model::Transactions('checkout')->findById($uuid);

            if (is_object($model) == false) {
                $this->error('Not valid uuid.');
                return;
            }

            $driver = $this->get('driver')->create($driverName);
            if (is_object($driver) == false) {
                $this->error('Not valid checkout driver name.');
                return;
            }

            $result = $driver->getTransactionDetails($model->transaction_id);
           
            $this->setResponse($result,function() use($uuid,$result) {                  
                $this
                    ->message('delete')
                    ->field('details',$result);             
            },'errors.details');              
        });
        $data->validate();        
        
    }
}
