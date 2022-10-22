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

use Arikaim\Core\Db\Model;
use Arikaim\Core\Controllers\ControlPanelApiController;

/**
 * Checkout control panel api controler
*/
class CheckoutControlPanel extends ControlPanelApiController
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
        $data->validate(true);        

        $uuid = $data->get('uuid');
        $driverName = $data->get('driver_name',null);

        $model = Model::Transactions('checkout')->findById($uuid);
        if ($model == null) {
            $this->error('errors.id','Not valid uuid.');
            return;
        }

        $driver = $this->get('driver')->create($driverName);
        if (is_object($driver) == false) {
            $this->error('errors.driver','Not valid checkout driver name.');
            return;
        }

        $result = $driver->getTransactionDetails($model->transaction_id);
        
        if ($result === false) {
            $this->error('errors.details','Error get transaction details');
            return false;
        }

        $this
            ->message('delete')
            ->field('details',$result);   
    }
}
