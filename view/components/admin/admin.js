/**
 *  Arikaim
 *  @copyright  Copyright (c) Konstantin Atanasov <info@arikaim.com>
 *  @license    http://www.arikaim.com/license
 *  http://www.arikaim.com
 */
'use strict';

function CheckoutControlPanel() {
   
    this.init = function() {    
        arikaim.ui.tab('.tab-item','tab_content');
    };   
}

var checkout = new CheckoutControlPanel();

arikaim.component.onLoaded(function() {
    checkout.init();
});