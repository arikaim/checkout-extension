'use strict';

arikaim.component.onLoaded(function() {
    arikaim.ui.button('.close-button',function(element) {
        $('#right_sidebar').hide(400);
    });
}); 