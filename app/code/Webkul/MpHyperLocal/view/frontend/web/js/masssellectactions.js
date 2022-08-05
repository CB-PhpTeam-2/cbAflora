/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpHyperLocal
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
 
 /*jshint jquery:true*/
define([
    "jquery",
    "mage/translate",
    "jquery/ui"
], function ($, $t) {
    "use strict";
    $.widget('mphyperlocal.autofilladdress', {
        _create: function () {
            var options = this.options;

            $('#mp-hyper-local-allcheck').change(function () {
                if ($(this).is(":checked")) {
                    $('.wk-row-view  .mpcheckbox').each(function () {
                        $(this).prop('checked', true);
                    });
                } else {
                    $('.wk-row-view  .mpcheckbox').each(function () {
                        $(this).prop('checked', false);
                    });
                }
            });

            $('.mpcheckbox').change(function () {
                if ($(this).is(":checked")) {
                    var totalCheck = $('.wk-row-view  .mpcheckbox').length,
                        totalCkecked = $('.wk-row-view  .mpcheckbox:checked').length;
                    if (totalCheck == totalCkecked) {
                        $('#mp-hyper-local-allcheck').prop('checked', true);
                    }
                } else {
                    $('#mp-hyper-local-allcheck').prop('checked', false);
                }
            });

            $('#form-arealist-massdelete .delete,#form-ratelist-massdelete .delete').on('click', function () {
                var conf = confirm($t('You are sure to delete ship area.'));
                return conf ? true :false;
            });

            $('#form-arealist-massdelete,#form-ratelist-massdelete').on('submit', function () {
                if ($('.mpcheckbox:checked').length) {
                    var conf = confirm($t('You are sure to delete selected ship area.'));
                    return conf ? true :false;
                } else {
                    alert($t('Please select record for delete.'));
                    return false;
                }
            });
        }
    });
    return $.mphyperlocal.autofilladdress;
});