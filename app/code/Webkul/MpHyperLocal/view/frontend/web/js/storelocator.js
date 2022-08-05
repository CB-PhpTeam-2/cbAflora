/**
 * Webkul storelocator js.
 * @category Webkul
 * @package Webkul_SellerStorePickup
 * @author Webkul
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
define([
"jquery",
'mage/translate',
'Magento_Ui/js/modal/alert',
"jquery/ui",
'googleMapPlaceLibrary',
], function ($, $t, alert) {
    'use strict';
    var MARKER_PATH = '//maps.gstatic.com/intl/en_us/mapfiles/marker_green';
    $.widget('mage.storelocator', {
        options: {
            errorSecureMsg: $t("Only secure origins are allowed."),
            errorRetrieveMsg: $t("Unable to retrieve your location.")
        },
        _create: function () {
            var self = this;
            var map;
            var ajax;
            $(document).ready(function () {
                var viewurl = self.options.viewurl;
                var myOptions = {
                    zoom: 11,
                    center: new google.maps.LatLng(-33.867, 151.195),
                    mapTypeId: 'roadmap'
                };
                map = new google.maps.Map(document.getElementById('wk_ssp_map'), myOptions);
                var locations = self.options.location;
                var infowindow = new google.maps.InfoWindow();
                var marker, i;
                var marker_color = "FF0000";
                var marker_text_color = "FFFFFF";
                var useAplhabets = 1;
                var start_letter_code = useAplhabets ? 97 : 1;
                for (i = 0; i < locations.length; i++) {
                    var character = useAplhabets ? String.fromCharCode(start_letter_code).toUpperCase() : start_letter_code;
                    marker = new google.maps.Marker({
                        position: new google.maps.LatLng(locations[i][1], locations[i][2]),
                        map: map,
                        icon: "http://chart.apis.google.com/chart?chst=d_map_pin_letter&chld=" + character + "|" + marker_color + "|" + marker_text_color
                    });
                    google.maps.event.addListener(marker, 'click', (function (marker, i) {
                        return function () {
                            infowindow.setContent("<h4>"+locations[i][4]+"</h4> <p>"+locations[i][0]+"</p> <a href="+viewurl+"shop/"+locations[i][3]+" target='_blank'> Visit Store</a> &nbsp;&nbsp;"+" <a href='javascript:void(0);' class='wk_ssp_search_submit_view' store_address='"+locations[i][0]+"'>Get Direction</a>");
                            infowindow.open(map, marker);
                        }
                    })(marker, i));
                    start_letter_code++;
                }
                var searchlocation = self.options.searchlocation;
                map.setCenter(new google.maps.LatLng(searchlocation[0][0], searchlocation[0][1]));
                marker = new google.maps.Marker({
                    position: new google.maps.LatLng(searchlocation[0][0], searchlocation[0][1]),
                    map: map
                });
                google.maps.event.addListener(marker, 'click', (function (marker) {
                    return function () {
                        infowindow.setContent(searchlocation[0][2]);
                        infowindow.open(map, marker);
                    }
                })(marker));
                var previd;
                $(document).on('click', '#wk_ssp_make_button', function (event) {
                    var id = $(this).attr("data-id1");
                    var className = $(this).attr("class");
                    if (id != previd && className != "wk_ssp_mystore_result") {
                        $(this).next().css('display','block');
                        callAjax(id);
                    }
                    var previd = id;
                });
                $(document).on('click', '.wk_ssp_current_location', function (event) {
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
                                    $('#wk_current').val(results[1].formatted_address);
                                }
                            }
                        });
                    } else {
                        alert({
                            content: self.options.errorRetrieveMsg
                        });
                    }
                }
                function callAjax(id)
                {
                    if (ajax) {
                        ajax.abort();
                    }
                    var baseurl = self.options.url;
                    ajax = $.ajax({
                        url : baseurl+"sellerstorepickup/search/makestore",
                        data : 'id='+id,
                        type : "POST",
                        success : function (response) {
                            location.reload();
                        }
                    })
                }

                var geocoder = new google.maps.Geocoder();
                $(document).on('click', '.wk_ssp_search_submit_view', function (event) {
                    //$('.wk_ssp_current_location').click();
                    var address1 = $(this).attr('store_address');
                    address1 = address1.replace("/", "+");
                
                    setTimeout(
                      function() 
                      {
                        var address = $('#wk_current').val();
                        geocoder.geocode({ 'address': address}, function (results, status) {
                            if (status == google.maps.GeocoderStatus.OK) {
                                var addres = results[0].formatted_address;
                                var newaddr = addres.split(' ').join('+');
                                newaddr = newaddr.replace("/", "+");
                                var win = window.open('https://www.google.com/maps/dir/'+newaddr+'/'+address1, '_blank');
                            }
                        });
                      }, 300);
                    
                });
            })
        }
    });
    return $.mage.storelocator;
});
