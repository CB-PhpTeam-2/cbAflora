/**
 * @category Webkul
 * @package Webkul_AdavncedLayeredNavigation
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
define([
    "jquery",
    "jquery/ui"
], function ($) {
    "use strict";
    $.widget('mage.optionsScript', {
        _create: function () {
            $("input[id^='check-']").click(function () {
                var id = $(this).attr('id');
                var requestId = id.split("-");
                if ($(this).prop('checked') == true) {
                    if ($("#img-"+requestId[1]).length == 0) {
                       $("#inputImg-"+requestId[1]).attr('required','required');
                        var requestId = id.split("-");
                        var attributeName = $(this).parent().next('td').text();
                        if ($("#checkOpt-"+requestId[1]).length == 0) {
                            $("#"+id).append('<input type="hidden" value="'+attributeName+'" id="checkOpt-'+requestId[1]+'" name="attributeName[]"/ >');
                        }
                    }
                } else {
                    $("#inputImg-"+requestId[1]).removeAttr('required');
                    if ($("#checkOpt-"+requestId[1]).length > 0) {
                        $("#checkOpt-"+requestId[1]).remove();
                    }
                }
            });
            // for Save button disable
            $(".save").click(function () {
                if (($("#title").hasClass('valid')) && ($('[name=attribute_code]').hasClass('valid'))
                 && ($('[name=catId]').hasClass('valid'))) {
                        $(".save").attr('disabled','disabled');
                }
            });
           
        }
    });
 
    return $.mage.optionsScript;
});
