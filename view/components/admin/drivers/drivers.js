'use strict';

function CheckoutDriversControlPanel() {
    var self = this;

    this.setDefault = function(uuid, category, onSuccess, onError) {           
        var data = { 
            uuid: uuid,
            keyValue: (isEmpty(category) == true) ? null : category     
        };

        return arikaim.put('/api/admin/checkout/default',data,onSuccess,onError);      
    };

    this.setStatus = function(uuid, status, onSuccess, onError) {           
        var data = { 
            uuid: uuid, 
            status: status 
        };

        return arikaim.put('/api/admin/checkout/status',data,onSuccess,onError);      
    };

    this.initRows = function() {
        arikaim.ui.button('.change-driver-status',function(element) {  
            var uuid = $(element).attr('uuid'); 
            $(element).toggleClass('active');
            var status;
            
            if ($(element).hasClass('active') == true) {
                $(element).html('<i class="icon check"></i>');
                status = 1;
            } else {
                $(element).html('<i class="icon close red"></i>');
                status = 0;
            }
        
            checkoutDrivers.setStatus(uuid,status);
        });

        arikaim.ui.button('.set-default-driver',function(element) {  
            var uuid = $(element).attr('uuid');   
            var category = $(element).attr('category');   
    
            checkoutDrivers.setDefault(uuid,category,function(result) {
                return arikaim.page.loadContent({
                    id: 'drivers_list',
                    component: 'checkout::admin.drivers.list',
                    params: { category: category }
                },function(result) {
                    self.initRows();
                });
            });
        });
    
        arikaim.ui.button('.driver-config',function(element) {  
            var name = $(element).attr('driver-name');
    
            return drivers.loadConfig(name,'driver_config_content');        
        });
    };
}

var checkoutDrivers = new CheckoutDriversControlPanel();

arikaim.component.onLoaded(function() {
    checkoutDrivers.initRows();
});