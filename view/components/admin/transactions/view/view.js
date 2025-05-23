/**
 *  Arikaim
 *  @copyright  Copyright (c)  <info@arikaim.com>
 *  @license    http://www.arikaim.com/license
 *  http://www.arikaim.com
 */
'use strict';

function TransactionsView() {
    var self = this;

    this.init = function() {
        arikaim.ui.tab('.transaction-tab-item','transactions_content');
        paginator.init('transactions_rows','checkout::admin.transactions.view.rows','transactions');   

        search.init({
            id: 'transactions_rows',
            component: 'checkout::admin.transactions.view.rows',
            event: 'transactions.search.load'
        },'transactions');  
        
        arikaim.events.on('transactions.search.load',function(result) {      
            paginator.reload();
            self.initRows();    
        },'transactionsSearch');
    };

    this.initRows = function() {
        arikaim.ui.button('.transaction-details',function(element) {
            var uuid = $(element).attr('uuid');
      
            return arikaim.page.loadContent({
                id: 'details_content',
                component: 'checkout::admin.transactions.details',
                params: { uuid: uuid }
            });
        });
    };
};

var transactionsView = new TransactionsView();

arikaim.component.onLoaded(function() {
    transactionsView.init();
}); 