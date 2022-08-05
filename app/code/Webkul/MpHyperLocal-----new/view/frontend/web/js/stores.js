define([
    'jquery'
], function ($) {
    'use strict';

    /**
     * @param {String} url
     */
    function processStores(url) {
        $.ajax({
            url: url,
            cache: false,
            dataType: 'html',
            showLoader: false
        }).done(function (data) {
            $('#wk-stores-container').html(data).trigger('contentUpdated');
        })
    }

    return function (config) {
        processStores(config.storesUrl);
    };
});
