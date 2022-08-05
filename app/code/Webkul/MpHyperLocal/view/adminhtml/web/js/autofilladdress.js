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
    "jquery/ui"
], function ($) {
    "use strict";
    $.widget('mphyperlocal.autofilladdress', {
        _create: function () {
            var options = this.options;
            $.getScript('https://maps.googleapis.com/maps/api/js?key='+options.googleApiKey+'&libraries=places', function () {
                var placeSearch, autocomplete;
                var address_Type = {
                    locality: 'long_name',
                    administrative_area_level_1: 'long_name',
                    country: 'long_name'
                  };
                var addressMap = {
                    locality: 'city',
                    administrative_area_level_1: 'state',
                    country: "country"
                  };
                var selector = {
                    city: 1,
                    state: 2,
                    country: 3
                  };
                autocomplete = new google.maps.places.Autocomplete(
                    /** @type {!HTMLInputElement} */(document.getElementById('autocomplete')),
                    {types: ['geocode']}
                );

                // When the user selects an address from the dropdown, populate the address
                // fields in the form.
                autocomplete.addListener('place_changed', fillInAddress);
                $('#autocomplete').val(options.savedAddress);

                function fillInAddress()
                {
                // Get the place details from the autocomplete object.
                    var address = ($('#autocomplete').val()).split(",");
                    var place = autocomplete.getPlace();
                    console.log(place.address_components);
                    for (var i = 0; i < place.address_components.length; i++) {
                      var addressType = place.address_components[i].types[0];
                      if (address_Type[addressType]) {
                        var val = place.address_components[i][address_Type[addressType]];
                        if (val == address[0]) {
                            console.log(addressMap[addressType]);
                            $('#address_type>option:eq('+selector[addressMap[addressType]]+')').prop('selected', true);
                        }
                      }
                    }
                    $('#mphyperlocal_general_settings_latitude').val(place.geometry.location.lat());
                    $('#mphyperlocal_general_settings_longitude').val(place.geometry.location.lng());
                }
            });
        }
    });
    return $.mphyperlocal.autofilladdress;
});