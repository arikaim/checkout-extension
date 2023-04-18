'use strict';

arikaim.component.onLoaded(function() {
    $('#drivers_dropdown').dropdown({
        onChange: function(value) {                    
            options.save('checkout.default.driver',value);
        }
    });
    
    arikaim.events.on('driver.config',function(element,name,category) {
        arikaim.ui.setActiveTab('#driver_tab');
        return drivers.loadConfig(name,'driver_config_content',null,'sixteen wide');           
    },'driverConfig'); 
});