'use strict';

arikaim.component.onLoaded(function() {
    $('#drivers_dropdown').on('change', function() {
        var value = $(this).val();
        options.save('checkout.default.driver',value);       
    });
    
    arikaim.events.on('driver.config',function(element,name,category) {
        return drivers.loadConfig(name,'driver_config_panel',null,'');           
    },'driverConfig'); 
});