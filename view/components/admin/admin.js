/**
 *  Arikaim
 *  @copyright  Copyright (c)  <info@arikaim.com>
 *  @license    http://www.arikaim.com/license
 *  http://www.arikaim.com
 */
'use strict';

function CheckoutControlPanel() {
   
    this.getDetails = function(uuid, driverName, onSuccess, onError) {  
        return arikaim.post('/api/admin/checkout/transaction/details',{ 
            driver_name: driverName,
            uuid: uuid 
        },onSuccess,onError);           
    };
}

var checkoutAdmin = new CheckoutControlPanel();
