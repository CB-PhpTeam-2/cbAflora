jQuery(document).ready(function(){     
 // jQuery(".mycontent .clmn .frstbtn").click(function () {   
     //      jQuery("body").removeClass('_has-modal');
     //      jQuery(".modals-wrapper").css("display", "none");
     //   });
    var current_fs, next_fs, previous_fs; //fieldsets
    var opacity;
         
    jQuery(".next").click(function(){
         current_fs = jQuery(this).parent();
         next_fs = jQuery(this).parent().next();
         
         //Add Class Active
         jQuery("#progressbar li").eq(jQuery("fieldset").index(next_fs)).addClass("active");
         
         //show the next fieldset
         next_fs.show();
         //hide the current fieldset with style
         current_fs.animate({opacity: 0}, {
         step: function(now) {
         // for making fielset appear animation
         opacity = 1 - now;
         
         current_fs.css({
         'display': 'none',
         'position': 'relative'
         });
         next_fs.css({'opacity': opacity});
         },
         duration: 600
         });
    });

    jQuery(".previous").click(function(){
         
         current_fs = jQuery(this).parent();
         previous_fs = jQuery(this).parent().prev();
         
         //Remove class active
         jQuery("#progressbar li").eq(jQuery("fieldset").index(current_fs)).removeClass("active");
         
         //show the previous fieldset
         previous_fs.show();
         
         //hide the current fieldset with style
         current_fs.animate({opacity: 0}, {
         step: function(now) {
                 // for making fielset appear animation
                 opacity = 1 - now;
                 current_fs.css({
                     'display': 'none',
                     'position': 'relative'
                 });
                 previous_fs.css({'opacity': opacity});
            },
         duration: 600
         });
    });

    jQuery('.radio-group .radio').click(function(){
         jQuery(this).parent().find('.radio').removeClass('selected');
         jQuery(this).addClass('selected');
    });
     
    jQuery(".submit").click(function(){
        return false;
    });
    
    var tab = location.hash.split('#').pop();
    if(tab=="tab1"){
        jQuery("#tab1").css('display','block'); 
        jQuery("#tab2, #tab3, #tab5, #tab4").css('display','none');
    }else if(tab=="tab2"){
        jQuery("#tab2").css('display','block'); 
        jQuery("#tab1, #tab3, #tab5, #tab4").css('display','none');
    }else if(tab=="tab3"){
        jQuery("#tab3").css('display','block'); 
        jQuery("#tab2, #tab1, #tab5, #tab4").css('display','none');
    }else if(tab=="tab4"){
        jQuery("#tab4").css('display','block'); 
        jQuery("#tab2, #tab3, #tab5, #tab1").css('display','none');
    }else if(tab=="tab5"){
        jQuery("#tab5").css('display','block'); 
        jQuery("#tab2, #tab3, #tab1, #tab4").css('display','none');
    }

    var searchInput = document.getElementById("search");
    if (typeof(searchInput) != 'undefined' && searchInput != null){
        searchInput.addEventListener("keyup", function(event) {
            if (event.keyCode === 13) {
                // Cancel the default action, if needed
                event.preventDefault();
                // Trigger the button element with a click
                jQuery(this).parents('form').submit();
            }
        });
    }


    /*jQuery(document).on("click","#search_autocomplete ul li",function() {
        var value = jQuery(this).find('span').html();
        jQuery('#search').val(value);
        jQuery(this).parents('form').submit();
    });*/
});

// jQuery(document).ready(function(){
//     jQuery('.wk_search_search').insertAfter('.bannertext');  
//     jQuery("#autocomplete").attr("placeholder","Enter your address or city to get started...");
// });

jQuery(window).load(function(){
     setTimeout( function(){ 
        var imgval = jQuery('#wkautocomplete').val();
        jQuery('.header.links .lcnwrp').html(imgval);
     }  , 1000 );
});



  jQuery(window).load(function(){
     setTimeout( function(){
        jQuery('.pac-container.pac-logo').appendTo('.service_types');
        jQuery('#maincontent #autocomplete').click(function(){
            jQuery('.service_types').show();
            event.stopPropagation();
        });
        jQuery('.service_types .service-type-tabs').click(function(){
            event.stopPropagation();
        });
        jQuery('#maincontent').click(function(){
            jQuery('.service_types').hide();
        });
     }  , 2000 );
    // jQuery('#autocomplete').click(function(){
    //     jQuery('.service_types').toggle();
    // });
});


  // 30-12-21
  $(document).ready(function(){
    jQuery("a:contains(Sell)").closest("li").addClass("selling-lst");
    jQuery("a:contains(Vendor Dashboard)").closest("li").addClass("vendor-lst");
    jQuery('.vendor-lst').prependTo('.customer-menu ul.header.links');
    jQuery('.cms-store-locator .wk-mp-design.wk-mp-landingpage .wk-mp-sellerlist-container .mystore_list').wrap('<div class="xyz"></div>');
  });
  jQuery(document).ready(function(){
  jQuery(".aflora-attr-parent").click(function(){
    jQuery(this).next(".aflora-attr-child").slideToggle("slow");
  });
});