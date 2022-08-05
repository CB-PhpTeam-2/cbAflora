/**
 * @category Webkul
 * @package Webkul_AdavncedLayeredNavigation
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
/*jshint jquery:true*/
define(
    [
    "jquery",
    'sellerProfileJsSlider',
    'mage/translate',
    'Magento_Ui/js/modal/alert',
    "jquery/ui"
    ],
    function ($, $t, alert) {
        'use strict';
        $.widget(
            'mage.manageSlider',
            {
                options: {
                    bannerId: '#banner-slide',
                    sliderWrapper:'.bjqs-wrapper',
                    previousSlider:'.bjqs-prev',
                    nextSlider:'.bjqs-next',
                },
                _create: function () {
                    var self = this;
                    var tempWidth = self.options.sliderWidth;
                    var tempHeight = self.options.sliderHeight;

                    /*-----------to slide a slider through bjqs jquery-----------*/

                    if (tempWidth > $('body').find('.column.main').outerWidth()) {
                        tempWidth = $('body').find('.column.main').outerWidth();
                    }

                    $(self.options.bannerId).bjqs(
                        {
                            animtype      : 'slide',
                            height        : tempHeight,
                            width         : tempWidth,
                            animduration  : self.options.animduration,
                            animspeed     : self.options.animspeed,
                            responsive    : true,
                            randomstart   : false
                        }
                    );
                    // if ($(self.options.bannerId).find("ol.bjqs-markers").find('li').length) {
                    //     $(self.options.bannerId).parent().removeClass('wk-mp-slider');
                    //     $(self.options.bannerId).parent().addClass('wk-mp-slider');
                    // }
                    $('body '+self.options.sliderWrapper).mouseover(
                        function (event) {
                            $('body '+self.options.previousSlider).show();
                            $('body '+self.options.nextSlider).show();
                        }
                    );
                    $('body '+self.options.previousSlider).mouseover(
                        function (event) {
                            $('body '+self.options.previousSlider).show();
                            $('body '+self.options.nextSlider).show();
                        }
                    );
                    $('body '+self.options.nextSlider).mouseover(
                        function (event) {
                            $('body '+self.options.previousSlider).show();
                            $('body '+self.options.nextSlider).show();
                        }
                    );
                    $('body '+self.options.sliderWrapper).mouseout(
                        function (event) {
                            $('body '+self.options.previousSlider).hide();
                            $('body '+self.options.nextSlider).hide();
                        }
                    );
                    $('body '+self.options.previousSlider).mouseout(
                        function (event) {
                            $('body '+self.options.previousSlider).hide();
                            $('body '+self.options.nextSlider).hide();
                        }
                    );
                    $('body '+self.options.nextSlider).mouseout(
                        function (event) {
                            $('body '+self.options.previousSlider).hide();
                            $('body '+self.options.nextSlider).hide();
                        }
                    );
                },
            }
        );
        return $.mage.manageSlider;
    }
);
