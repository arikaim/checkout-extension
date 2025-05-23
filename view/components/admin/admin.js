/**
 *  Arikaim
 *  @copyright  Copyright (c)  <info@arikaim.com>
 *  @license    http://www.arikaim.com/license
 *  http://www.arikaim.com
 */
'use strict';

function CheckoutControlPanel() {
   
    this.getDetails = function(uuid, driverName, onSuccess, onError) { 
        var data = { 
            driver_name: driverName,
            uuid: uuid 
        };
        
        return arikaim.post('/api/admin/checkout/transaction/details',data,onSuccess,onError);           
    };
}

var checkoutAdmin = new CheckoutControlPanel();

arikaim.component.onLoaded(function() { 
    arikaim.ui.tab();        
});