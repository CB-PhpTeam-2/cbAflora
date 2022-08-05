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
    "googleMapPlaceLibrary"
],function ($, $t, modal) {
    "use strict";
    $.widget('affiliate.register', {
        _create: function () {
            var optionsData = this.options;
            var options = {
                type: 'popup',
                responsive: true,
                innerScroll: true,
                width:'200px',
                clickableOverlay: false,
                title: $t(optionsData.popupHeading),
                buttons: []
            };

            if (optionsData.isAddressSet == 0) {
                var cont = $('<div />').append($('#select-address-popup'));
                modal(options, cont);
                cont.modal('openModal');
                cont.trigger('contentUpdated').applyBindings();
            }
            var autocompleteform;
            autocompleteform = new google.maps.places.Autocomplete(
                /** @type {!HTMLInputElement} */(document.getElementById('wkautocomplete')),
                {}
            );

            // When the user selects an address from the dropdown, populate the address
            // fields in the form.
            autocompleteform.addListener('place_changed', fillInAddress);

            var custAddressType = {
                'locality' : 'long_name',
                'administrative_area_level_1' : 'long_name',
                'country' : 'long_name',
                'postal_code' : 'long_name'
            };
            var addressMap = {
                'locality' : 'city',
                'administrative_area_level_1' : 'state',
                'country' : 'country',
                'postal_code' : 'postcode'
            };
            var selector = {
                city: 1,
                state: 2,
                country: 3
            };

            function fillInAddress()
            {
                var place = autocompleteform.getPlace();
                var data = {};
                if (place != undefined) {
                    $('#wkautocomplete').attr('data-lat', place.geometry.location.lat());
                    $('#wkautocomplete').attr('data-lng', place.geometry.location.lng());
                }
                var address_components = place.address_components;
                for (var i=0; i<address_components.length; i++) {
                    var addressType = address_components[i]['types'][0];
                    if (typeof custAddressType[addressType] !== 'undefined') {
                        data[addressMap[addressType]] = address_components[i][custAddressType[addressType]];
                        if (addressType == 'country' || addressType == 'administrative_area_level_1') {
                            data[addressMap[addressType]+'_code'] = address_components[i]['short_name'];
                        }
                    }
                }

                if (data.city) {
                    $('#wkautocomplete').attr('data-city', data.city);
                } else {
                    $('#wkautocomplete').attr('data-city', '');
                }
                if (data.state) {
                    $('#wkautocomplete').attr('data-state', data.state);
                } else {
                    $('#wkautocomplete').attr('data-state', '');
                }
                if (data.state) {
                    $('#wkautocomplete').attr('data-state', data.state);
                } else {
                    $('#wkautocomplete').attr('data-state', '');
                }
                if (data.country_code) {
                    $('#wkautocomplete').attr('data-country_code', data.country_code);
                } else {
                    $('#wkautocomplete').attr('data-country_code', '');
                }
                if (data.state_code) {
                    $('#wkautocomplete').attr('data-state_code', data.state_code);
                } else {
                    $('#wkautocomplete').attr('data-state_code', '');
                }
                if (data.country) {
                    $('#wkautocomplete').attr('data-country', data.country);
                } else {
                    $('#wkautocomplete').attr('data-country', '');
                }
                if (data.postcode) {
                    $('#wkautocomplete').attr('data-postcode', data.postcode);
                } else {
                    $('#wkautocomplete').attr('data-postcode', '');
                }
                if (place.formatted_address != null) {
                    $('#wkautocomplete').attr('value', place.formatted_address);
                } else {
                    $('#wkautocomplete').attr('value', '');
                }
                saveAction();
            }

            if ($('#autocompleteform').length > 0) {
                var autocompleteform;
                autocompleteform = new google.maps.places.Autocomplete(
                    /** @type {!HTMLInputElement} */(document.getElementById('autocompleteform')),
                    {}
                );

                // When the user selects an address from the dropdown, populate the address
                // fields in the form.
                autocompleteform.addListener('place_changed', fillInPopupAddress);

                function fillInPopupAddress()
                {
                // Get the place details from the autocomplete object.
                    var placepopup = autocompleteform.getPlace();
                    var address = ($('#autocompleteform').val()).split(",");
                    for (var i = 0; i < placepopup.address_components.length; i++) {
                        var addressType = placepopup.address_components[i].types[0];
                        if (custAddressType[addressType]) {
                            var val = placepopup.address_components[i][custAddressType[addressType]];
                            if (val == address[0]) {
                                $('#address_type>option:eq('+selector[addressMap[addressType]]+')').prop('selected', true);
                            }
                        }
                    }
                    $('#latitude').val(placepopup.geometry.location.lat());
                    $('#longitude').val(placepopup.geometry.location.lng());
                }
            }

            function saveAction () {
                var address = $('#wkautocomplete');
                if (address.val()) {
                    var conf = confirm($t('On location address change cart will empty.'));
                    if (conf) {
                        $.ajax({
                            url: optionsData.saveAction,
                            data: {
                                'address':address.val(),
                                'lat':address.attr('data-lat'),
                                'lng':address.attr('data-lng'),
                                'city':address.attr('data-city'),
                                'state':address.attr('data-state'),
                                'country_code':address.attr('data-country_code'),
                                'state_code':address.attr('data-state_code'),
                                'country':address.attr('data-country'),
                                'postcode':address.attr('data-postcode') 
                            },
                            type: 'POST',
                            dataType:'html',
                            success: function (transport) {
                                var response = $.parseJSON(transport);
                                if (response.status) {
                                    window.location.href = response.redirect_url;
                                } else {
                                    $('.hyper-local-error').remove();
                                    $('.modal-footer .loader').removeClass("loader");
                                    $('#select-address-popup').before($('<span class="message-error error message hyper-local-error"/>').text(response.msg));
                                }
                            }
                        });
                    }
                } else {
                    address.focus();
                    address.css('border', '1px solid red');

                }
            }
            $('#wkautocomplete').keypress(function () {
                $(this).css('border','1px solid #c2c2c2');
                $(this).attr('data-lat','');
                $(this).attr('data-lng','');
            });
        }
    });
    return $.affiliate.register;
});
