require([
    'jquery'
], function ($) {
    'use strict';

    jQuery(document).ajaxStop(function () {
    	
        $('input[name="product[thc_high]"]').next('label.admin__addon-prefix').find('span').remove();
        $('input[name="product[cbd_high]"]').next('label.admin__addon-prefix').find('span').remove();
    });
});