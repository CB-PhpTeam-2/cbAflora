/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpHyperLocal
 * @author    Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
define(
    [
    "jquery",
    "mage/translate",
    "googleMapPlaceLibrary"
    ],
    function ($, $t) {
    "use strict";
    $.widget(
        'outlet.form',
        {
            _create: function () {
                var optionsData = this.options;
                var autocomplete;
                autocomplete = new google.maps.places.Autocomplete(
                    /** @type {!HTMLInputElement} */(document.getElementById('addressautocomplete')),
                    {types: ['geocode']}
                );

                // When the user selects an address from the dropdown, populate the address
                // fields in the form.
                autocomplete.addListener('place_changed', fillInAddress);

                function fillInAddress()
                {
                    var place = autocomplete.getPlace();
                    if (place.geometry != null && place.geometry.location != null) {
                        $('#wk_latitude').val(place.geometry.location.lat());
                        $('#wk_longitude').val(place.geometry.location.lng());
                    }
                }
            }
        }
    );
    return $.outlet.form;
    }
);
