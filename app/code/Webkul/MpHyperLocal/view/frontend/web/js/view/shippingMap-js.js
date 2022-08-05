/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpHyperLocal
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

define([
    'jquery',
    'uiComponent',
    'ko',
    'mage/translate',
    'Magento_Ui/js/modal/alert',
    'Magento_Ui/js/modal/modal'
], function ($, Component, ko, $t, alert, modal) {
    'use strict';
    return Component.extend({
        initialize: function () {
           return this._super();
        
        },
        onElementRender: function () {
            var timer = setInterval(function() {
                if ($(document).find("div[name ='shippingAddress.country_id'] select[name='country_id']").length > 0) {
                    var data = JSON.parse($.cookie('hyper_local'));
                    var country = data['country_code'];
                    clearInterval(timer);
                    $(document).find("div[name ='shippingAddress.country_id'] select[name='country_id'] option[value='"+country+"']").attr("selected",true);
                    $(document).find("div[name ='shippingAddress.country_id'] select[name='country_id']").trigger('change');

                    var state = data['state'];
                    $(document).find('div[name ="shippingAddress.region_id"] select[name = "region_id"] option:contains("'+state+'")').attr("selected",true);
                    $(document).find('div[name ="shippingAddress.region_id"] select[name = "region_id"]').trigger('change');
                                                
                    $(document).find("div[name ='shippingAddress.region'] input[name = region]").val(state);
                    $(document).find("div[name ='shippingAddress.region'] input[name = region]").trigger('keyup');
                    
                    var city = data['city'];
                    $(document).find('div[name ="shippingAddress.city"] input[name="city"]').val(city);
                    $(document).find('div[name ="shippingAddress.city"] input[name="city"]').trigger('keyup');

                    var postal = data['postcode'];
                    if (postal != '') {
                        $(document).find('div[name ="shippingAddress.postcode"] input[name="postcode"]').val(postal);
                        $(document).find('div[name ="shippingAddress.postcode"] input[name="postcode"]').trigger('keyup');
                    }
                }
            }, 500);
        }
    });
});
  