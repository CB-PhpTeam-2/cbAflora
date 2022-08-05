/**
 * @category Webkul
 * @package Webkul_AdavncedLayeredNavigation
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
/*jshint browser:true*/
define([
    'jquery',
    'mage/translate',
    'mage/template',
    'jquery/ui'
], function ($, $t, mageTemplate) {
    'use strict';

    $.widget('mage.customlayer', {
        options: {
            submitbutton: '.layer-filter-btn',
            filterlist: '.filter-options',
            filtercontent: '.filter-content',
            filterablecheckbox: '.xt-filter-check',
            swatchOptions: '.swatch-attribute-options .swatch-option-link-layered',
            swatchAttribute: '.swatch-attribute',
            filterPrice: '.filter-price',
            filterNavSection: '.filter-nav-sections',
            pageWrapperClass: '.page-wrapper'
        },
        /**
         * filter creation
         * @protected
         */
        _create: function () {
            var self = this;
            var newParams = {};
            if (screen.width < 768) {
                var filterHtml = $('.columns').find('.sidebar').find('.block.filter').clone();
                var sorterHtml = $('.columns').find('.column.main').children('.toolbar-products').first().children('.toolbar-sorter').clone();

                $(self.options.pageWrapperClass)
                    .children('.sections.nav-sections')
                    .after(
                        $(filterHtml).addClass('filter-nav-sections')
                    );
                $(self.options.pageWrapperClass)
                    .children('.filter-nav-sections')
                    .after(
                        $(sorterHtml).addClass('sorter-nav-sections')
                    );
                $(self.options.pageWrapperClass)
                    .children(self.options.filterNavSection)
                    .children('.filter-title')
                    .prepend(
                        $('<div>').addClass('remove-filter-nav')
                    );
                $(self.options.pageWrapperClass)
                    .children('.sorter-nav-sections')
                    .prepend(
                        $('<div>').addClass('remove-sort-nav')
                    );
                $(self.options.pageWrapperClass)
                    .children(self.options.filterNavSection)
                    .find('.filter-actions')
                    .find('.layer-filter-btn')
                    .find('span span')
                    .text($t('Apply'));
            }

            $('body').on('click','.resp-filter-wrapper button',function () {
                if ($(this).hasClass('filter')) {
                    $('html').toggleClass('filter-nav-open');
                }
                if ($(this).hasClass('sort')) {
                    $('html').toggleClass('sorter-nav-open');
                    if (!$('body').find('div.wk-loader').length) {
                        $('body').append($('<div>').addClass('wk-loader'));
                    }
                }
            });

            $('body').on('click',self.options.filterNavSection +' .remove-filter-nav',function () {
                if ($('html').hasClass('filter-nav-open')) {
                    $('html').removeClass('filter-nav-open');
                }
            });

            $('body').on('click','.sorter-nav-sections .remove-sort-nav',function () {
                if ($('html').hasClass('sorter-nav-open')) {
                    $('html').removeClass('sorter-nav-open');
                }
                if ($('body').find('div.wk-loader').length) {
                    $('body').find('div.wk-loader').remove();
                }
            });

            $('body').on('click',self.options.filterNavSection +' .filter-options-title',function () {
                $(this).toggleClass('filter-toggle');
            });

            var sorterNavSections = $(self.options.pageWrapperClass).children('.sorter.sorter-nav-sections');
            var selectSortOptions = $(sorterNavSections).find('select.sorter-options');

            var rep = $(selectSortOptions).clone();
            $(selectSortOptions).after($(rep).addClass('dropdown-sorter-options'));
            $(sorterNavSections)
                .find('.dropdown-sorter-options')
                .replaceWith(function () {
                    return $('<ul/>', {
                        class: 'dropdown-sorter-options',
                        html: this.innerHTML
                    });
                });
            $(sorterNavSections)
                .find('.dropdown-sorter-options')
                .find('option')
                .replaceWith(function () {
                    return $('<li/>', {
                        class: 'sort-opt',
                        html: this.innerHTML
                    }).attr('data-opt',$(this).val());
                });
            var optSelected = $(sorterNavSections)
                .find('select.sorter-options option:selected')
                .val();

            $(sorterNavSections)
                .find('.dropdown-sorter-options li')
                .removeAttr('data-selected')
                .removeClass('opt-selected');

            $(sorterNavSections)
                .find('.dropdown-sorter-options li[data-opt='+optSelected+']')
                .attr('data-selected',true)
                .addClass('opt-selected');

            $("body").on('click', '.dropdown-sorter-options li' , function (e) {

                if (!$(this).attr('data-selected')) {
                    $(sorterNavSections)
                        .find('.dropdown-sorter-options li')
                        .removeAttr('data-selected')
                        .removeClass('opt-selected');

                    var newOptVal = $(this).data('opt');
                    $(this).attr('data-selected',true).addClass('opt-selected');
                    $(sorterNavSections)
                        .find('select.sorter-options option')
                        .removeAttr('selected');

                    $(sorterNavSections)
                        .find('select.sorter-options option[value='+newOptVal+']')
                        .attr("selected", 'selected');
                }
                var decode = window.decodeURIComponent;
                var paramName = 'product_list_order';
                var defaultValue = 'position';
                var paramValue = $(this).data('opt');

                var urlPaths = window.location.href.split('?'),
                    baseUrl = urlPaths[0],
                    urlParams = urlPaths[1] ? urlPaths[1].split('&') : [],
                    paramData = {},
                    parameters;

                for (var i = 0; i < urlParams.length; i++) {
                    parameters = urlParams[i].split('=');
                    paramData[decode(parameters[0])] = parameters[1] !== undefined
                        ? decode(parameters[1].replace(/\+/g, '%20'))
                        : '';
                }
                var flag = true;
                if (paramData[paramName]) {
                    if (paramValue == paramData[paramName]) {
                        flag = false;
                    }
                }
                if (flag) {
                    paramData[paramName] = paramValue;

                    if (paramValue == defaultValue) {
                        delete paramData[paramName];
                    }

                    paramData = $.param(paramData);
                    location.href = baseUrl + (paramData.length ? '?' + paramData : '');
                }
            });

            $(self.options.submitbutton).on('click', function (e) {
                e.preventDefault();
                var selected = '';
                var priceFilter = [];
                if ($(document)
                    .find(self.options.filtercontent)
                    .find(self.options.filterlist)
                    .find(self.options.filterablecheckbox).length
                ) {
                    selected = $(document)
                                .find(self.options.filtercontent)
                                .find(self.options.filterlist)
                                .find('input')
                                .serializeArray();
                }
                if (selected!=="") {
                    $(selected).each(function (key,value) {
                        if (value.name=="price") {
                            var splitPrice = value.value.split('-');
                            priceFilter.push(splitPrice[0]);
                            priceFilter.push(splitPrice[1]);
                        } else {
                            if (newParams[value.name]) {
                                newParams[value.name] = newParams[value.name]+","+value.value;
                            } else {
                                newParams[value.name] = value.value;
                            }
                        }
                    });
                    if (priceFilter.length) {
                        var minPrice = Math.min.apply(Math,priceFilter);
                        var maxPrice = Math.max.apply(Math,priceFilter);
                        if (minPrice==0) {
                            minPrice = "";
                        }
                        if (maxPrice==0) {
                            maxPrice = "";
                        }
                        newParams['price'] = minPrice+"-"+maxPrice;
                    }
                    var serializedStr = jQuery.param(newParams);
                    if (self.options.category_url.indexOf('?') == -1) {
                       window.location.href = self.options.category_url+"?"+serializedStr;
                   } else {
                       window.location.href = self.options.category_url+"&"+serializedStr;
                   }
                }
            });

            $(self.options.swatchOptions).on('click', function (e) {
                e.preventDefault();
                if (!$(this).hasClass('swatch-selected')) {
                    $(this).addClass('swatch-selected')
                }
                var attrCode = $(this).parents(self.options.swatchAttribute).attr('attribute-code');
                if (newParams[attrCode]) {
                    newParams[attrCode] = newParams[attrCode]+","+$(this).children('.swatch-option').attr('option-id');
                } else {
                    newParams[attrCode] = $(this).children('.swatch-option').attr('option-id');
                }
            });

            $(self.options.filterPrice).on('click', function (e) {
                e.preventDefault();
                // var filterName = $(this).siblings(self.options.filterablecheckbox).attr('name');
                // if(newParams[filterName]){
                //     newParams[filterName] = newParams[filterName]+","+$(this).siblings(self.options.filterablecheckbox).val();
                // }else{
                //     newParams[filterName] = $(this).siblings(self.options.filterablecheckbox).val();
                // }
            });

        },
    });

    return $.mage.customlayer;
});
