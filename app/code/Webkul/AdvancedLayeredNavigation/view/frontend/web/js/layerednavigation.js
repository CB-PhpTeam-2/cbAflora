/**
 * @category Webkul
 * @package Webkul_AdavncedLayeredNavigation
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
define([
    "jquery",
    "jquery/ui",
    "Webkul_AdvancedLayeredNavigation/js/jquerynstSlider",
    "Magento_Swatches/js/swatch-renderer"
    ], function ($,$u,$s,$sw) {
        'use strict';
        $.widget('mage.layerednavigation', {
            options: {},
            _create: function () {
                $(".filter-nav-sections").remove();
                $(".wk_layer_loader_bg").hide();
                $(document).on('click', 'body .wk-carousel-link', function (e) {
                    e.stopImmediatePropagation();
                    $(".filter-nav-sections").remove();
                    var attrname = $(this).attr('data-attrname');
                    var attrvalue = $(this).attr('data-attrvalue');
                    if (($('#'+attrvalue).length >0) && ($('#'+attrvalue).attr('data-attrname') != 'cat')) {
                        $('#'+attrvalue).trigger('click');
                    } else {
                        $("[name=webkul-"+attrvalue+"]").trigger('click');
                    }
                });
                var removeParameter = {};
                var self = this;
                var just_loaded = 0;
                var min_val;
                var max_val;
                var clear_url;
                var ajaxcall=false;
                $(document).ready(function () {
                    $(".block-filter.tab .button.filter").click(function(){ 
                      if($(".sidebar.sidebar-main").length >0){
                            $(".sidebar.sidebar-main").addClass('sidebar-main-data');
                            $(".sidebar.sidebar-main").removeClass('sidebar-main');
                      }else{
                          $(".sidebar").addClass('sidebar-main');
                          $(".sidebar-main").removeClass('sidebar-main-data');
                          }
                    });

                    $('#maincontent').on('click',".filter-options-item", function () {
                        $(".filter-nav-sections").remove();
                        setTimeout(function () {
                            initialize_price_slider();
                        }, 1);
                    });
                    //For category
                    $('body').on('click',"[id^='plus-']", function (e) {
                       e.stopImmediatePropagation();
                       $(".filter-nav-sections").remove();
                        var requestId = $(this).attr('id').split('-');
                        $('.child'+requestId[1]).slideToggle();
                        if ($(this).text() == '-') {
                            $(this).text('+');
                        } else {
                            $(this).text('-');
                        }
                    });
                    $('#maincontent').on('click',"#mode-list", function () {
                        $(".filter-nav-sections").remove();
                        if (ajaxcall) {
                            if ($(location).attr('href').indexOf('?') > 0) {
                                location.href = $(location).attr("href")+"&product_list_mode=list";
                            } else {
                                location.href = $(location).attr("href")+"?product_list_mode=list";
                            }
                        }
                    });
                    $('#maincontent').on('click',"#mode-grid", function () {
                        $(".filter-nav-sections").remove();
                        if (ajaxcall) {
                            location.href = $(location).attr("href").replace("product_list_mode=list", '');
                        }
                    });
                    $('#maincontent').on('click',".sorter-action", function () {
                        $(".filter-nav-sections").remove();
                        if (ajaxcall) {
                            var dataval = $(this).attr('data-value');
                            if (dataval == 'asc') {
                                location.href = $(location).attr("href").replace("product_list_dir=desc", '');
                            } else {
                                if ($(location).attr('href').indexOf('?') > 0) {
                                    location.href = $(location).attr("href")+"&product_list_dir=desc";
                                } else {
                                    location.href = $(location).attr("href")+"?product_list_dir=desc";
                                }
                            }
                        }
                    });
                    $('#maincontent').on('change',".sorter-options", function () {
                        $(".filter-nav-sections").remove();
                        if (ajaxcall) {
                            var val = $(this).val();
                            var url = $(location).attr("href");
                            if ($(location).attr('href').search('product_list_order') > 0) {
                                var urldata = $(location).attr('href').split('product_list_order=');
                                var url = $(location).attr("href").replace("product_list_order="+urldata[1].split('&')[0], '');
                            }
                            if (val != 'position') {
                                if ($(location).attr('href').indexOf('?') > 0) {
                                    location.href = url+"&product_list_order="+val;
                                } else {
                                    location.href = url+"?product_list_order="+val;
                                }
                            } else {
                                location.href = url;
                            }
                        }
                    });
                    $('#maincontent').on('change',".limiter-options", function () {
                        $(".filter-nav-sections").remove();
                        if (ajaxcall) {
                            var val = $(this).val();
                            var url = $(location).attr("href");
                            if ($(location).attr('href').search('product_list_limit') > 0) {
                                var urldata = $(location).attr('href').split('product_list_limit=');
                                var url = $(location).attr("href").replace("product_list_limit="+urldata[1].split('&')[0], '');
                            }
                            if ($(location).attr('href').indexOf('?') > 0) {
                                location.href = url+"&product_list_limit="+val;
                            } else {
                                location.href = url+"?product_list_limit="+val;
                            }
                        }
                    })
                    just_loaded++;
                    $('#maincontent').on('click','.block-subtitle.filter-current-subtitle', function (e) {
                        $(".filter-nav-sections").remove();
                        e.stopImmediatePropagation();
                        $('.filter .filter-current .items').toggle();
                    });
                    $('#maincontent').on('click','.filter-title strong', function (e) {
                        $(".filter-nav-sections").remove();
                        e.stopImmediatePropagation();
                    });
                    $('#maincontent').on('click','.filter.active .filter-title strong', function (e) {
                        $(".filter-nav-sections").remove();
                        if (ajaxcall) {
                            $('#layered-filter-block').removeClass('active');
                            $('body .page-header').attr('style', '');
                            $('body .page-wrapper').attr('style', '');
                        }
                    });
                    $('#maincontent').on('click','.swatch-attribute-options > a', function (e) {
                        e.preventDefault();
                        e.stopImmediatePropagation();
                        $(".filter-nav-sections").remove();
                        $(this).find('.swatch-option').addClass('selected');
                        var final_url = $(this).attr('href');
                        ajaxcall = true;
                        callAjaxAfterMakeUrl();
                    });
                    $('#maincontent').on('click', '.wk-filter-action', function (e) {
                        $(".filter-nav-sections").remove();
                        var actionurl = $(this).attr('data-url');
                        ajaxcall = true;
                        callAjax(actionurl);
                        e.stopImmediatePropagation();
                    })
                  
                    $('#maincontent').on('click','.layered-navigation-label', function (e) {
                        e.stopImmediatePropagation();
                        $(".filter-nav-sections").remove();
                        var final_url = $(this).attr('data-url');
                        ajaxcall = true;
                        if (($(this).find("input").prop('checked') == false) ) {
                            $(this).find("input").removeAttr('checked');
                            removeParameter[$(this).find("input").attr('data-attrname')] = $(this).find("input").attr('data-attrname');
                        }
                        callAjaxAfterMakeUrl();
                    })
                    $('#maincontent').on('keyup','.attr_filter_input', function () {
                        $(".filter-nav-sections").remove();
                        var this_input = $(this);
                        this_input.parents("ol").find("li").each(function () {
                            var this_li = $(this);
                            if (this_li.index() != 0) {
                                var this_input_value = this_input.val().toLowerCase();
                                var this_li_label_text = this_li.find("label").text().toLowerCase();
                                if (this_li_label_text.indexOf(this_input_value) < 0) {
                                    this_li.hide();
                                } else {
                                    this_li.show();
                                }
                            }
                        });
                    });
                    $('#maincontent').on('click','.attr_filter_clear', function () {
                        $(".filter-nav-sections").remove();
                        $(this).prev().val("").trigger("keyup");
                    });
                    function initialize_price_slider()
                    {
                        $(".filter-nav-sections").remove();
                        $(".range_slider").nstSlider({
                            "left_grip_selector"    : ".min_grip",
                            "right_grip_selector"   : ".max_grip",
                            "value_bar_selector"    : ".range_slider_bar",
                            "value_changed_callback": function (cause, leftValue, rightValue) {
                                $(".min_range").html(leftValue);
                                $(".max_range").html(rightValue);
                            },
                            "user_mouseup_callback" : function () {
                                sliderMouseupCallback();
                                ajaxcall = true;
                            }
                        });
                    }
                    function sliderMouseupCallback()
                    {
                        $(".filter-nav-sections").remove();
                        if ($(".min_range").text() == $(".range_slider").attr("data-range_min")) {
                            min_val = "";
                        } else {
                            $(".board.min_range").each(function () {
                                if ($(this).text()) {
                                   min_val = $(this).text();
                                   return false;
                                }
                            });
                        }
                        if ($(".max_range").text() == $(".range_slider").attr("data-range_max")) {
                            max_val = "";
                        } else {
                            $(".board.max_range").each(function () {
                                if ($(this).text()) {
                                    max_val = $(this).text();
                                   return false;
                                }
                            });
                        }
                        $(".for_price_filter").attr("id",min_val+"-"+max_val);
                        callAjaxAfterMakeUrl();
                    }
                })
                $(document).ready(function () {
                    $(".filter-nav-sections").remove();
                    $(".attr_filter_input").trigger('keyup');
                })
                function callAjax(url)
                {
                   
                    $(".filter-nav-sections").remove();
                    if (url.indexOf('price') == -1) {
                        parameters_testing2 = {};
                    }
                    ajaxcall = true;
                    var sidebarHtml = $(".sidebar-additional").html(); 
                    $.ajax({
                        url     :   url,
                        beforeSend: function () {
                            // setting a timeout
                            $('body').loader('show');
                        },
                        type    :   "GET",
                        async:true,
                        success :   function (data) {
                            $('body').loader('hide');
                            var body;
                            var dom = document.createElement("html");
                            dom.innerHTML = data;
                            if ($(window).width() >=750) {
                                if (($(data).find('.columns').length>0)) {
                                    body = $(".columns", dom);
                                    $(".columns").html(body.html());
                                    $(".wk_layer_loader_bg").hide();
                                } else {
                                    $(".wk_layer_loader_bg").hide();
                                    body = $("#maincontent", dom);
                                    $("#maincontent").html(body.html());
                                }
                            } else {
                                if (($(data).find('.columns').length>0)) {
                                    body = $(".columns", dom);
                                    $(".columns").html(body.html());
                                    $(".wk_layer_loader_bg").hide();
                                } else {
                                    body = $("#maincontent", dom);
                                    $("#maincontent").html(body.html());
                                    $(".wk_layer_loader_bg").hide();
                                }
                            } 
                           
                            
                            $(".sidebar-additional").html(sidebarHtml);
                            $(".sidebar-main").trigger('contentUpdated');
                            $("[class*= swatch-opt]").trigger('contentUpdated');
                            $('[class=filter-options-content]').each(function () {
                                $(this).css('display','none');
                            });
                            $(".attr_filter_input").trigger('keyup');
                           window.history.pushState("object", "current title", url);
                           if($(".wk-xtremo-phone-cms").length){ 
                               if( $(".block.filter.filter-nav-sections")){
                                setTimeout(function(){
                                    $(".block.filter.filter-nav-sections").remove();
                                    $(".sidebar-main").addClass('sidebar-main-data');
                                    $(".sidebar-main-data").removeClass('sidebar-main');
                                    return false;
                                }, 500);}
                           }
                        }
                    })
                    
                }
                var parameters_testing2 = {};
                
                function callAjaxAfterMakeUrl()
                {
                    $(".filter-nav-sections").remove();
                    var parameters = {};
                    var path = window.location.href;
                    var pathBreak =  path.split("?");
                    $.each(pathBreak, function (index, value) {
                        var queryString = value.split("&");
                        $.each(queryString, function (index1, value1) {
                            var urlData = value1.split("=");
                            if (urlData[1] != undefined) {
                                parameters[urlData[0]] = [urlData[1]];
                            }
                        });
                    });
                    var currentUrl = window.location.href;
                    var final_url = "";
                    var parameters_testing3 = {};
                    var parameters_testing = {};
                    $(".layered_attrs").each(function () {
                        var this_this = $(this);
                        var this_attr_name = this_this.attr("data-attrname");
                        var this_attr_value = this_this.attr("id");
                        if (this_attr_value.includes("_text_") == true) {
                            this_attr_value = this_attr_value.split('_text_')[1];
                        }
                        if (this_attr_value != "-" && this_attr_value != "") {
                            if (this_this.is(":checked") || this_this.hasClass("for_price_filter")) {
                                if (typeof parameters_testing[this_attr_name] == "undefined") {
                                    parameters_testing[this_attr_name] = new Array(this_attr_value);
                                } else {
                                    parameters_testing[this_attr_name][parameters_testing[this_attr_name].length] = this_attr_value;
                                }
                            }
                        }
                    });
                  
                    $(".swatch-layered").find('.swatch-option').each(function (e) {
                        var this_this = $(this);
                        var this_attr_name = this_this.parent().parent().parent().attr("attribute-code");
                        var this_attr_value = this_this.attr("option-id");
                        if (this_attr_value != "-" && this_attr_value != "" && this_attr_name != 'undefined') {
                            if (this_this.hasClass("selected")) {
                                if (typeof parameters_testing[this_attr_name] == "undefined") {
                                    parameters_testing[this_attr_name] = new Array(this_attr_value.trim());
                                } else {
                                    parameters_testing[this_attr_name][parameters_testing[this_attr_name].length] = this_attr_value.trim();
                                }
                            }
                        }
                    });
                    $.each(parameters, function (index, value) {
                        if (!(index in parameters_testing)) {
                            if (!(index in removeParameter)) {
                                parameters_testing[index] = value;
                            }
                        }
                    });
                    $('#layered-filter-block').removeClass('active');
                    $('body .page-header').attr('style', '');
                    $('body .page-wrapper').attr('style', '');
                    clear_url = self.options.clearUrl;
                    final_url = clear_url;
                    $.each(parameters_testing,function (index,value) {
                        if (final_url == clear_url) {
                            final_url += "?";
                        }
                        if (final_url != clear_url+"?") {
                            final_url += "&"+index+"=";
                        } else {
                            final_url += index+"=";
                        }
                        if (index == 'price') {
                            final_url += value[0];
                        } else {
                            var matchData = [];
                          
                            for (var i = 0; i < value.length; i++) {
                                if (jQuery.inArray(value[i], matchData) == -1) {
                                    final_url += value[i];
                                    if (value[i+1]) {
                                        final_url += "_";
                                    }
                                    matchData.push(value[i]);
                                }
                            }
                        }
                    });
                    final_url = getFinalUrl(final_url);
                    if (currentUrl != final_url) {
                        callAjax(final_url);
                    }
                }
             
                function getFinalUrl(final_url)
                {
                    if ($(location).attr('href').search('product_list_mode=list') > 0) {
                        final_url = getAdditionalUrl(final_url, 'product_list_mode=list');
                    }
                    if ($(location).attr('href').search('product_list_dir=desc') > 0) {
                        final_url = getAdditionalUrl(final_url, 'product_list_dir=desc');
                    }
                    if ($(location).attr('href').search('product_list_order=name') > 0) {
                        final_url = getAdditionalUrl(final_url, 'product_list_order=name');
                    }
                    if ($(location).attr('href').search('product_list_order=price') > 0) {
                        final_url = getAdditionalUrl(final_url, 'product_list_order=price');
                    }
                    if ($(location).attr('href').search('product_list_limit') > 0) {
                        var urldata = $(location).attr('href').split('product_list_limit=');
                        final_url = getAdditionalUrl(final_url, 'product_list_limit='+urldata[1].split('&')[0]);
                    }
                    /*if ($(location).attr('href').indexOf('q=') > 0) {
                        var urldata = $(location).attr('href').split('q=');
                        final_url = getAdditionalUrl(final_url, 'q='+urldata[1].split('&')[0]);
                    }*/
    
                    return final_url;
                }
                function getAdditionalUrl(final_url, value)
                {
                    if (final_url.indexOf('?') < 0) {
                        final_url = final_url+'?'+value;
                    } else {
                        final_url = final_url+'&'+value;
                    }
                    return final_url;
                }
            }
        });
        return $.mage.layerednavigation;
    });