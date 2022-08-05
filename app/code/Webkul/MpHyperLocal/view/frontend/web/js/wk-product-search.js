/*jshint jquery:true*/
define([
    "jquery",
    "jquery/ui"
], function ($) {
    'use strict';
    $.widget('mage.wkProductSearch', {
        options: {
            searchForm: '.wk_collection_search',
            banner: '.wk-mp-collection-right',
        },
        _create: function () {
            var self = this;
            $(self.options.banner).prepend($(self.options.searchForm));
            $(self.options.searchForm).show();
            if (parseInt(self.options.isSearch)) {
                $(document).ready(function () {
                    $('html, body').animate({
                        scrollTop: $('.wk-mp-collection-container').offset().top
                    }, 'slow');
                });
            }
        },
    });
    return $.mage.wkProductSearch;
});
