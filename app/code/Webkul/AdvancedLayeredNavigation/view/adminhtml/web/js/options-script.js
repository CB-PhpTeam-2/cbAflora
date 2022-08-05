/**
 * @category Webkul
 * @package Webkul_AdavncedLayeredNavigation
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
define([
    "jquery",
    'mage/template',
    "jquery/ui"
], function ($,mageTemplate) {
        'use strict';
        $.widget('mage.optionsScript', {
            _create: function () {
                var self = this;
                var options = this.options;
                var url = options.url;
                $(document).ready(function () {

                    $('body').on('click', '#layered_attribute_code', function () {
                        var val = $(this).val();
                        var attributeLabel = $('#layered_attribute_code option:selected').text();
                        if (val!="") {
                            $.ajax({
                                type: 'get',
                                url: url,
                                async: true,
                                dataType: 'json',
                                showLoader: true,
                                data : {
                                    'attribute' : val
                                },
                                success:function (response) {
                                    $('body').loader('hide');
                                    var $i = 0;
                                    var data = { target:response };
                                    var template = _.template($("#options-template").text());
                                    $("#output").html(template(data));

                                    $(document).find('.wk-tab-title input[type="text"]').each(function () {
                                        var txtVal = $(this).val();
                                        $(this).val(attributeLabel+':'+txtVal);
                                    });

                                    
                                }
                            });
                        }
                    });
                });
    
            }
        });
        return $.mage.optionsScript;
    });