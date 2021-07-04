/**
 *  Arikaim
 *  @copyright  Copyright (c) Konstantin Atanasov <info@arikaim.com>
 *  @license    http://www.arikaim.com/license
 *  http://www.arikaim.com
 */
'use strict';

function TransactionsView() {
    var self = this;

    this.init = function() {
        arikaim.ui.tab('.transaction-tab-item','transactions_content');
        paginator.init('transactions_rows');   

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

            $('#right_sidebar').show();
            return arikaim.page.loadContent({
                id: 'right_sidebar',
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