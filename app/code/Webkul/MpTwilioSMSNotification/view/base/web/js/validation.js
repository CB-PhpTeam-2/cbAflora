/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpTwilioSMSNotification
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

(function (factory) {
    'use strict';

    if (typeof define === 'function' && define.amd) {
        define([
            'jquery',
            'jquery/ui',
            'jquery/validate',
            'mage/translate',
            'mage/validation'
        ], factory);
    } else {
        factory(jQuery);
    }
}(function ($) {
    'use strict';
    _.each({
        "wk-otp-telephone": [
            function (v) {
                return $.mage.isEmptyNoTrim(v) || /^\+\d{3,}$/.test(v);
            },
            $.mage.__('Please enter a valid phone number with country code (Ex: +918888888888)')
        ],
    }, function (rule, i) {
        rule.unshift(i);
        $.validator.addMethod.apply($.validator, rule);
    });
}));
