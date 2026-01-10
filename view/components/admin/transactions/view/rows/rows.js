'use strict';

arikaim.component.onLoaded(function() {
    safeCall('transactionsView',function(obj) {
        obj.initRows();
    },true);   
}); 