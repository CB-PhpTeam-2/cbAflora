/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpTwilioSMSNotification
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define([
    "jquery",
    'mage/translate',
    'prototype',
], function($, $t) {
    "use strict";
    return function (config) {
        $('#verify').click(function () {
            var number = $('#marketplace_twiliosettings_verifytwiliophonenumber').val(),
                requestUrl  = config.url;
            $('.wk-otp-loading-mask').removeClass('wk-otp-display-none');
            jQuery.ajax({
                url: requestUrl,
                data : {
                    'number' : number
                },
                type: 'POST',
                async: true,
                global: false,
                showLoader: true,
            }).done(function (result) {
                if (result.error) {
                    $('#success').addClass('wk-otp-display-none');
                    $('#err').text(result.message).removeClass('wk-otp-display-none');
                } else {
                    $('#err').addClass('wk-otp-display-none');
                    $('#success').text(result.message).removeClass('wk-otp-display-none');
                }
            }).fail(function () { 
                $('#success').addClass('wk-otp-display-none');
                $('#err').text($t('Unable to verify Phone Number.')).removeClass('wk-otp-display-none');
            }).always(function () {
                $('.wk-otp-loading-mask').addClass('wk-otp-display-none');
            });
                    
        });
    }
});