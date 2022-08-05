/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpHyperLocal
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define([
    "jquery",
    "mage/translate",
    "Magento_Ui/js/modal/modal",
    'Magento_Customer/js/customer-data'
], function ($, $t, modal, customerData) {
    "use strict";
    $.widget('mage.wkProductView', {
        _create: function () {
            var ajaxUrl = this.options.getAction;
            var cartUrl = this.options.cartUrl;
            var options = {
                type: 'popup',
                responsive: true,
                innerScroll: true,
                title: $t('View Product'),
                buttons: [
                    {
                        text: $t('Close'),
                        class: 'close',
                        click: function () {
                            this.closeModal();
                        }
                    }
                ]
            };

            $('body').on('click', '.item.product.product-item', function(e) {
                var productId = $(this).find('.price-box').attr('data-product-id');
                if (typeof(productId) == 'undefined') {
                    var ItemId = $(this).find('a.delete.action').attr('data-cart-item');
                    $.ajax({
                        url: cartUrl,
                        data: { 'item-id': ItemId },
                        type: 'POST',
                        dataType:'html',
                        success: function (response) {
                            response =  $.parseJSON(response);
                            if (response['status'] == 1) {
                                productId = response['id'];
                                productModal(productId);
                            }
                        }
                    });
                } else {
                    productModal(productId);
                }
                e.preventDefault();
            });

            function productModal(productId) {
                $('iframe').attr('src', ajaxUrl+'id/'+productId);
                var cont = $('<div />').append($('.popup'));
                modal(options, cont);
                cont.modal('openModal');
                $('iframe').find('.wk-seller-card-row span.wk-ask-question-link').remove();
            }

            $('body').on('hover', '.item.product.product-item', function() {
                $('.product-item-inner').remove();
            });

            $('#iframe').on('load', function() {
                $(this).contents().find('body').find('.wk-seller-card-row span.wk-ask-question-link').remove();
                $(this).contents().find('body').on('click', '.action.primary.tocart', function(e) {
                    if (true) {
                        setTimeout(function() {
                            customerData.reload(['cart'], false);
                        }, 1000);
                    }
                });
            });
        }
    });
    return $.mage.wkProductView;
});