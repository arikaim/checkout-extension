'use strict';

arikaim.component.onLoaded(function() { 
    var uuid = $('#transaction_details').attr('uuid');
    var driverName = $('#transaction_details').attr('driver-name');
    if (isEmpty(uuid) == false) {
        arikaim.page.showLoader('#transaction_details');
        transactionsControlPanel.getDetails(uuid,driverName,function(result) {
            return arikaim.page.loadContent({
                id: 'transaction_details',
                component: 'checkout::admin.transactions.details.items',
                params: { 
                    details: result.details 
                }
            });
        });
    }
}); 