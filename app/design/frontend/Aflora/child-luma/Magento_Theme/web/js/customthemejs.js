require([
    'jquery',
    'mage/url',
    'mage/translate',
    'jquery/ui'
    ],
    function($, urlBuilder, $t) {

        var customLink = urlBuilder.build('customer/account/login');

        $(document).ready(function() {

            var wkautocomplete = $('#wkautocomplete');
            if (typeof(wkautocomplete) != 'undefined' && wkautocomplete != null){
                $('.header.links .lcnwrp').html(wkautocomplete.val());
            }

            var current_url = window.location.href;
            var tab = location.hash.split('#').pop();
            //var tab = current_url.hash.split('#').pop();
            if(tab=="tab1"){
                $("#tab1").css('display','block'); 
                $("#tab2, #tab3, #tab5, #tab4").css('display','none');
            }else if(tab=="tab2"){
                $("#tab2").css('display','block'); 
                $("#tab1, #tab3, #tab5, #tab4").css('display','none');
            }else if(tab=="tab3"){
                $("#tab3").css('display','block'); 
                $("#tab2, #tab1, #tab5, #tab4").css('display','none');
            }else if(tab=="tab4"){
                $("#tab4").css('display','block'); 
                $("#tab2, #tab3, #tab5, #tab1").css('display','none');
            }else if(tab=="tab5"){
                $("#tab5").css('display','block'); 
                $("#tab2, #tab3, #tab1, #tab4").css('display','none');
            }
            
        });

        $(window).load(function(){
            var checkExistStoreContainer = setInterval(function() {
               if ($('.cms-index-index #wk-stores-container').length) {
                    $('html, body').stop().animate({
                        scrollTop: $('.cms-index-index #wk-stores-container').offset().top
                    }, 2500);
                    clearInterval(checkExistStoreContainer);
               }
            }, 100);

            $('.catalog-product-view .product-info-main .box-tocart,.catalog-product-view .product-info-main .product-social-links').wrapAll('<div class="pro-links"></div>');
        });


        var current_fs, next_fs, previous_fs; //fieldsets
        var opacity;
    
        $(".next").click(function(){
           current_fs = $(this).parent();
           next_fs = $(this).parent().next();
            //Add Class Active
            $("#progressbar li").eq($("fieldset").index(next_fs)).addClass("active");
             
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

        $(".previous").click(function(){
           
           current_fs = $(this).parent();
           previous_fs = $(this).parent().prev();
           
             //Remove class active
             $("#progressbar li").eq($("fieldset").index(current_fs)).removeClass("active");
             
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

        $(".cms-index-index .bannertext .bannerbtns .mainbtn.drkclr").click(function (){
            $([document.documentElement, document.body]).stop().animate({
                scrollTop: $(".column.main #shopby_section").offset().top
            }, 500);
        });

        $('#product_addtocart_form .box-tocart .control #qty_minus').click(function (){
            var qty = $(this).siblings('#qty').val();

            if(qty == ''){ qty = 1; }
            if(qty > 1){
                qty--;
            }
            $(this).siblings('#qty').val(qty);
        });

        $('#product_addtocart_form .box-tocart .control #qty_plus').click(function (){
            var qty = $(this).siblings('#qty').val();

            if(qty == ''){ qty = 0; }
            qty++;
            $(this).siblings('#qty').val(qty);
        });

        $(".aflora-attr-parent").click(function(){
            $(this).next(".aflora-attr-child").slideToggle("slow");
        });

        $('.filter-options-content:last-child .items .plus').parent('li').addClass("pls-icon");
        $('.cms-store-locator .find_a_store input[type=button]').val('Go');
        $('.page-products .sidebar.sidebar-main .filter.block').parent().addClass('compareness');
        $("a.action.showcart").click(function(){
            $(".overlay").show();
        });

        //$("button#btn-minicart-close").click(function(){
        $(document).on("click","#btn-minicart-close",function() {
            alert("hello");
            $(".overlay").hide();
        });

        $(".overlay").click(function(){
            $(this).hide();
        });

        $("input#search").attr("placeholder", "Search entire store here...");
        var value = $(".nav.item.current strong").html();
        // $("<div class ='page-title'><h2>"+value+"</h2></div>").insertBefore(".account #contentarea");
        $(".delimiter").parent(".item").hide();

        $('#btn-minicart-close').click(function(){
            window.location.href= urlBuilder.build('checkout/cart');
        });
        jQuery('.cms-store-locator .wk-mp-design.wk-mp-landingpage .wk-mp-sellerlist-container .mystore_list').wrap('<div class="strlist"></div>');
        
        $('[data-block="minicart"]').on('contentLoading', function (event) {    
            $('[data-block="minicart"]').on('contentUpdated', function ()  {
                //$('html, body').animate({scrollTop:0}, 100);
                $('html, body').stop().animate({scrollTop:0}, 500); 
            });
        });

        $(document).on("keypress", ".mobile-box input[type=number]" , function(e) {

            var charCode = (e.which) ? e.which : event.keyCode    
            if (String.fromCharCode(charCode).match(/[^0-9]/g)){    
                return false;
            }   
        });

        $(document).on("keyup", ".mobile-box input[type=number]" , function() {
            var text = $(this).val();
            var maxlength = 10;

            if($(this).val().length > maxlength){
                $(this).val(text.substr(0, maxlength)); 
            }
        });
});
