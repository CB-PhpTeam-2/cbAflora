require([
    'jquery',
    'mage/url',
    'mage/translate',
    'Magento_Ui/js/modal/alert',
    'jquery/ui',
    'googleMapPlaceLibrary',
],
function($, urlBuilder, $t, alert) {

    $(document).on('click', '.wk-mp-design.mylocation .wk_ssp_current_location', function (event) {
        var options = {
            enableHighAccuracy: true,
            timeout: 5000,
            maximumAge: 0
        };
        navigator.geolocation.getCurrentPosition(showPosition, showError, options);
    });

    function showError(err)
    {
        if (err.message.indexOf("Only secure origins are allowed") == 0) {
            alert({
                content: self.options.errorSecureMsg
            });
        }
    }

    var geocoder = new google.maps.Geocoder();
    function showPosition(position)
    {
        if (position.coords) {
            var latlng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
            geocoder.geocode({ 'latLng': latlng }, function (results, status) {
                if (status == google.maps.GeocoderStatus.OK) {
                    if (results[1]) {
                        //$('#autocomplete').val(results[1].formatted_address);
                        var city = '';
                        var region = '';
                        var country = '';
                        for (var i=0; i<results[1].address_components.length; i++){
                            if (results[1].address_components[i].types[0] == "administrative_area_level_2") {
                                //this is the object you are looking for City
                                city = results[1].address_components[i];
                            }
                            if (results[1].address_components[i].types[0] == "administrative_area_level_1") {
                                //this is the object you are looking for State
                                region = results[1].address_components[i];
                            }
                            if (results[1].address_components[i].types[0] == "country") {
                                //this is the object you are looking for
                                country = results[1].address_components[i];
                            }
                        }

                        $('#autocomplete').attr('value', results[1].formatted_address);
                        $('#autocomplete').attr('data-city', city.long_name);
                        $('#autocomplete').attr('data-state', region.long_name);
                        $('#autocomplete').attr('data-country', country.long_name);
                        $('#autocomplete').attr('data-country_code', country.short_name);
                        $('#autocomplete').attr('data-lat', position.coords.latitude);
                        $('#autocomplete').attr('data-lng', position.coords.longitude);

                        var pac_container = $('.pac-container.pac-logo');
                        if (typeof(pac_container) != 'undefined' && pac_container != null){
                            $('.pac-container.pac-logo').empty();
                        }
                    }
                }
            });
        } else {
            alert({
                content: self.options.errorRetrieveMsg
            });
        }
    }

});