  require(['jquery','bootstrap','popper','owl','scroll'],function($) {
    jQuery(document).ready(function(){
      jQuery('select#gender option:first-child').text('Please select gender..');
    //set content bottom padding
    var footerHeight = jQuery(".footer").outerHeight()
    jQuery("#content").css("padding-bottom", jQuery("footer").outerHeight()+'px');
    jQuery("#content").css("padding-top", jQuery("header").outerHeight()+'px');

    jQuery('#content').css('min-height','calc(100vh - '+footerHeight+'px)');
    if(jQuery('body').hasClass('catalog-category-view')) {
      jQuery(".left_menu_bar").addClass("sidebar-col");
    jQuery(".filter-btn").click(function () {
      if(jQuery(".sidebar-col").hasClass("sidebar-open")) {
  jQuery(".sidebar-col").removeClass("sidebar-open");
  jQuery(".sidebar-col").addClass("gw-sidebar");
} else {
  jQuery(".sidebar-col").addClass("sidebar-open");
  jQuery(".sidebar-col").removeClass("gw-sidebar");
}
});
    jQuery(".close-btn").click(function () {
  jQuery(".sidebar-col").removeClass("sidebar-open");
});
  }
// jQuery(".close-btn").click(function () {
//   jQuery(".sidebar-col").removeClass("sidebar-open");
// });
    var highestBox = 0;
    jQuery('#amasty-shopby-product-list .product_div').each(function() {
      if (jQuery(this).height() > highestBox) {
        highestBox = jQuery(this).height();
      }
    });
    jQuery('#amasty-shopby-product-list .product_div').height(highestBox);

    /**product page tab**/
    jQuery(".product_review .nav-tabs li.data.item.nav-link:first-child").addClass('active');
    jQuery(".product_review .tab-content > div.tab-pane.fade.in:first-child").addClass('active show');
    jQuery(".product_review .nav-tabs li.data.item.nav-link").on("click", function(){
      jQuery(".product_review .nav-tabs li.data.item.nav-link").removeClass('active');
      jQuery(this).addClass('active');
    });
    /*21-9-020*/
    //if(jQuery(window).width() >= 992) {
      setTimeout(function() {
        jQuery('.catalog-category-view ul#narrow-by-list li.add_plus').each(function(){
          jQuery(this).removeClass('arrow-down');
          jQuery(this).addClass('arrow-up active');
          jQuery(this).find('form ol').show();
          jQuery(this).find('form ul').show();
          size_ul = jQuery(this).find('form div ul li').length;
          x=5;
          size_li = jQuery(this).find('form ol li').length;
          x=5;
          if(size_li > 5) {       
            jQuery(this).find('form ol').append("<span class='load-more__btn' id='loadMoree'><i class='fas fa-chevron-down' aria-hidden='true'></i>Ver Más</span>");
           // jQuery(this).find('form ol li').hide();
            //jQuery(this).find('form ol li:lt('+x+')').show();
          }
          if(size_ul > 5) {      
            jQuery(this).find('form div ul').append("<span class='load-more__btn' id='loadMoree'><i class='fas fa-chevron-down' aria-hidden='true'></i>Ver Más</span>");
           // jQuery(this).find('form div ul li').hide();
           // jQuery(this).find('form div ul li:lt('+x+')').show();
          }
        });
        jQuery('.catalogsearch-result-index ul#narrow-by-list li.add_plus').each(function(){
          jQuery(this).removeClass('arrow-down');
          jQuery(this).addClass('arrow-up active');
          jQuery(this).find('form ol').show();
          jQuery(this).find('form ul').show();
          size_ul = jQuery(this).find('form div ul li').length;
          x=5;
          size_li = jQuery(this).find('form ol li').length;
          x=5;
          if(size_li > 5) {       
            jQuery(this).find('form ol').append("<span class='load-more__btn' id='loadMoree'><i class='fas fa-chevron-down' aria-hidden='true'></i>Ver Más</span>");
            //jQuery(this).find('form ol li').hide();
           // jQuery(this).find('form ol li:lt('+x+')').show();
          }
          if(size_ul > 5) {      
            jQuery(this).find('form div ul').append("<span class='load-more__btn' id='loadMoree'><i class='fas fa-chevron-down' aria-hidden='true'></i>Ver Más</span>");
           // jQuery(this).find('form div ul li').hide();
           // jQuery(this).find('form div ul li:lt('+x+')').show();
          }
        });
        jQuery('.catalog-category-view ul#narrow-by-list #loadMoree').on( "click", function() {
          if(jQuery(this).parent().find('li:visible').length == 5) {
            jQuery(this).parent().find('li').css("display","flex");
            jQuery(this).html('<i class="fas fa-chevron-up" aria-hidden="true"></i>Ver menos');
          } else if(jQuery(this).parent().find('li:visible').length > 5) {
            jQuery(this).parent().find('li').css("display","none");
            jQuery(this).parent().find('li:lt(5)').css("display","flex");
            jQuery(this).html('<i class="fas fa-chevron-down" aria-hidden="true"></i>Ver Más');
          }
        });
        jQuery('.catalogsearch-result-index ul#narrow-by-list #loadMoree').on( "click", function() {
          if(jQuery(this).parent().find('li:visible').length == 5) {
            jQuery(this).parent().find('li').css("display","flex");
            jQuery(this).html('<i class="fas fa-chevron-up" aria-hidden="true"></i>Ver menos');
          } else if(jQuery(this).parent().find('li:visible').length > 5) {
            jQuery(this).parent().find('li').css("display","none");
            jQuery(this).parent().find('li:lt(5)').css("display","flex");
            jQuery(this).html('<i class="fas fa-chevron-down" aria-hidden="true"></i>Ver Más');
          }
        });
      }, 1000);
    //}
   // }
    /******/
    if(jQuery(window).width() >= 1024)
    {
      
      
      jQuery("input#search").on("click", function(){
        // jQuery(this).parent().parent().parent().parent().parent().css("border-left", "1px solid #c2292f");
        // jQuery(this).parent().parent().parent().parent().parent().css("border-right", "1px solid #c2292f");
        // jQuery(this).parent().parent().parent().parent().parent().css("border-bottom", "1px solid #c2292f");
        // jQuery(this).parent().parent().parent().parent().parent().css("border-top", "1px solid #c2292f");
    //console.log("dfsfdsfsdffsf");
    if(!jQuery('.overlay').hasClass('show')) {
      jQuery('.overlay').addClass('show');
    }
  });

      jQuery("div#wrapper").on("click", function(evt){
         if(evt.target.id == "search")
          return;
       // Check if click was triggered on or within #ccEle
       if(jQuery(evt.target).closest('#search').length){
          return;
       }  
   // console.log("11111");
      // jQuery(this).find('.search_menu').css("border-left", "none");
      //   jQuery(this).find('.search_menu').css("border-right", "none");
      //   jQuery(this).find('.search_menu').css("border-bottom", "none");
      //   jQuery(this).find('.search_menu').css("border-top", "1px solid #f2f2f2");
   if(jQuery('.overlay').hasClass('show')) {
    jQuery('.overlay').removeClass('show');
  }
});
      jQuery(".overlay").on("click", function(){
    //console.log("11111");
    if(jQuery(this).hasClass('show')) {
      jQuery(this).removeClass('show');
    }
  });
    }
    /*29-09-020 temp fix prod image*/
      if(jQuery('body').hasClass('catalog-product-view')) {
        var src = jQuery(".mz-figure img").attr('data-mst-lazy-src');
        jQuery(".mz-figure img").attr('src' , src);
        jQuery(".mcs-item:first-child a.mt-thumb-switcher.white-bg.mz-thumb").addClass("mz-thumb-selected active-selector");
        jQuery('.custom-brand-name').insertBefore(jQuery('div#mtImageContainer'));
      }
    /***/
    /*dynamic height End*/
  // checkout
  setTimeout(function() {
    if(jQuery('body').hasClass('checkout-index-index')) {
    //jQuery("iframe#product-quickview").remove();
    //console.log("fdsf");
    if(jQuery('.checkout-shipping-address select[name="country_id"]').find(":selected").text() == "Panama") {
      jQuery('div[name="shippingAddress.postcode"] > label > span').hide();
    } else {
      jQuery('div[name="shippingAddress.postcode"] > label > span').show();
    }
    
    if(jQuery('.checkout-billing-address select[name="country_id"]').find(":selected").text() == "Panama") {
      jQuery('div[name="billingAddress.postcode"] > label > span').hide();
    } else {
      jQuery('div[name="billingAddress.postcode"] > label > span').show();
    }
    
    jQuery('.checkout-shipping-address select[name="country_id"]').change(function () {
      if(jQuery(this).val() == "PA") {
        jQuery('div[name="shippingAddress.postcode"] > label > span').hide();
      } else {
        jQuery('div[name="shippingAddress.postcode"] > label > span').show();
      }
    });
    
    jQuery('.checkout-billing-address select[name="country_id"]').change(function () {
      if(jQuery(this).val() == "PA") {
        jQuery('div[name="billingAddress.postcode"] > label > span').hide();
      } else {
        jQuery('div[name="billingAddress.postcode"] > label > span').show();
      }
    });

  } }, 7000);
      // menu start
      var nav = function () {
        jQuery('.gw-nav > li > a').on("click",function () {
          var gw_nav = jQuery('.gw-nav');
          jQuery(this).parent().removeClass('active');
          //gw_nav.find('li').removeClass('active');
          //console.log("fdddfsf");
          jQuery('.gw-nav > li > ul > li').removeClass('active');
          jQuery('.gw-nav > li > ol > li').removeClass('active');

          var checkElement = jQuery(this).parent();
          var ulDom = checkElement.find('.gw-submenu')[0];

          if (ulDom == undefined) {
            checkElement.addClass('active');
            jQuery('.gw-nav').find('li').find('ul:visible').slideUp();
            jQuery('.gw-nav').find('li').find('ol:visible').slideUp();
            return;
          }
          if (ulDom.style.display != 'block') {
            jQuery(this).find('li').find('ul:visible').slideUp();
            jQuery(this).find('li.init-arrow-up').removeClass('init-arrow-up').addClass('arrow-down');
            jQuery(this).find('li.arrow-up').removeClass('arrow-up').addClass('arrow-down');
            checkElement.removeClass('init-arrow-down');
            checkElement.removeClass('arrow-down');
            checkElement.addClass('arrow-up');
            checkElement.addClass('active');
            checkElement.find('ul').slideDown(300);
            checkElement.find('ol').slideDown(300);
          } else {
            checkElement.removeClass('init-arrow-up');
            checkElement.removeClass('arrow-up');
            checkElement.removeClass('active');
            checkElement.addClass('arrow-down');
            checkElement.find('ul').slideUp(300);
            checkElement.find('ol').slideUp(300);

          }
        });
        jQuery('.gw-nav > li > ul > li > a').click(function () {
          jQuery(this).parent().parent().parent().removeClass('active');
          jQuery('.gw-nav > li > ul > li').removeClass('active');
          jQuery(this).parent().addClass('active')
        });
        jQuery('.gw-nav > li > ol > li > a').click(function () {
          jQuery(this).parent().parent().parent().removeClass('active');
          jQuery('.gw-nav > li > ol > li').removeClass('active');
          jQuery(this).parent().addClass('active')
        });
      };
      nav();

  // menu End

  //mega menu start
  var nav1 = function () 
  {
     var myCounter = 0;
    jQuery('.left_mega_menu > li').hover(function () 
    {
      //console.log("jhjkkhj");
     if(jQuery(window).width() > 768){
      if(!jQuery('.overlay').hasClass('show')){
        jQuery('.overlay').addClass('show');
      }
    //  console.log(jQuery(".sub_mega_menu").width());
  //    if(jQuery(".sub_mega_menu").width() == 0 )
  //     jQuery(this).children(".sub_mega_menu").animate({
  //       width: "0"
  //     });
  // }
  jQuery(this).children(".sub_mega_menu").width(1000);
     //   jQuery(this).children(".sub_mega_menu").animate({
     //   width: "1000px"
     // },);
      jQuery(this).children('.sub_mega_menu').show();
     // console.log(myCounter);
    // if(myCounter == 0) {
       
     //  jQuery(this).children(".sub_mega_menu").animate({
     //   width: "1000px"
     // });
// } else {
    // jQuery(".sub_mega_menu").animate({
    //    width: "1000px"
    //  });
    jQuery(this).parent().find(".sub_mega_menu").width(1000);
 //}
       myCounter++;
    }

    var x = jQuery('.left_mega_menu > li:first-child').position();
    var top = jQuery(".fixed_header2").outerHeight();
    jQuery(this).children('.sub_mega_menu').css({'top' : top + 'px'});

    jQuery("body").addClass('hidden')
  },

  function () 
  {
    jQuery(this).children('.sub_mega_menu').hide();
    jQuery("body").removeClass('hidden');
    // if(jQuery(window).width() > 768){
    //   jQuery(this).children(".sub_mega_menu").animate({
    //     width: "0"
    //   });
    // }
    if(jQuery(window).width() > 768){
      if(jQuery('.overlay').hasClass('show')){
        jQuery('.overlay').removeClass('show');
      }}
    });
      setTimeout(function() {
    jQuery('.navigation.desktop_menu').mouseleave(function(){
     // console.log("fdsfdfs");
      jQuery(this).find('li').children(".sub_mega_menu").animate({
        width: "1000px"
      });
    });
  }, 3000);
    
    jQuery('.sub_mega_menu').mouseleave(function(){
      jQuery(this).hide();
      jQuery("body").removeClass('hidden');
      
      if(jQuery(window).width() > 768){
        if(jQuery('.overlay').hasClass('show')){
          jQuery('.overlay').removeClass('show');
        }}
        
      });

    if(jQuery(window).width() <= 768)
    {
    //    jQuery('.left_mega_menu li').each(function(){
      //  if(jQuery(this).children('.sub_mega_menu').length > 0 ) {
      //    jQuery(this).find('a.level-top').attr("href", "#");
      //  }
      // });
      jQuery('.left_mega_menu  li.level0  i').on("click", function () {
        jQuery(this).parent().find('.sub_mega_menu').slideToggle();
        //console.log(this);

      });

      jQuery(".left_mega_menu  li.level0 a").on('click', function(){
          //console.log("fsdf"+jQuery(this).attr('href'));
          window.location.href = jQuery(this).attr('href');
        });

    } 

  };

  nav1();

//   //mega menu End 
//    setTimeout(function() {
// jQuery('.left_mega_menu').mouseleave(function(){
//       console.log("fdsfdfs");
//       jQuery(this).find('li').children(".sub_mega_menu").animate({
//         width: "0"
//       });
//     });
// }, 1000);
  /* fade out messages    */
  jQuery(document).ajaxComplete(function(event, xhr, settings) { 
    if (settings.url.indexOf("/customer/section/load/?sections=") !== -1) {
      setTimeout(function() {
        jQuery(".page.messages div[data-bind='html: message.text']").each(function() {
          jQuery(this).parent().delay(5000).fadeOut('slow');
        });
      }, 1000);
    }
  }); 
  setTimeout(function() {
    if (jQuery(".page.messages div[data-bind='html: message.text']").length) {
      jQuery(".page.messages div[data-bind='html: message.text']").each(function() {
        jQuery(this).parent().delay(5000).fadeOut('slow');
      });
    } else {
      setTimeout(function() {
        if (jQuery(".page.messages div[data-bind='html: message.text']").length) {
          jQuery(".page.messages div[data-bind='html: message.text']").each(function() {
            jQuery(this).parent().delay(5000).fadeOut('slow');
          });
        }
      }, 1000);
    }
  }, 1000);
  
  /* end of fade out messages */


  // slimscroll
  // jQuery('.nano-content, .main_mega_menu').slimScroll({
  //   height: '100%'
  // });
  jQuery('.nano-content').slimScroll({
    height: '100%'
  });

  // toggle_menu
  jQuery(".menu_icon").click(function(){
    jQuery(".left_menu_bar").slideToggle();
    jQuery("body").removeClass('hidden')
  });


  jQuery(".slider_conatiner").owlCarousel({
        loop: false,
    rewind: true,
        navigation : true, // Show next and prev buttons
        slideSpeed : 300,
        paginationSpeed : 400,
        singleItem:true,
        items : 1, 
        navText: ["<i class='fa fa-chevron-left'></i>", "<i class='fa fa-chevron-right'></i>"],
        nav:true,
        responsive:{
          0:{
            items:1,
            nav:false,
            dots:true
          },
          768:{
            items:1,
            nav:false,
            dots:true
          },

          1024:{
            items:1,
            nav:false,
            arrow:false,
            dots:true
          },
          1025:{
            items:1 
          }
        }

      });


  // shop category
  jQuery(".category_slider").owlCarousel({

        navigation : true, // Show next and prev buttons
        slideSpeed : 300,
        paginationSpeed : 400,
        items :6, 
        navText: ["<i class='fa fa-chevron-left'></i>", "<i class='fa fa-chevron-right'></i>"],
        nav:true,
        responsive:{
          0:{
            items:1,
            nav:true,
            dots:false
          },
          400:{
            items:2,
            nav:true,
            dots:false
          },
          768:{
            items:4,
            nav:false,
            dots:false
          },

          1024:{
            items:4,
            nav:false,
            arrow:false,
            dots:false,
          },
          1111:{
            items:5,
            dots:false,
          },
          1366:{
            items:5,
            dots:false,
          },
          1921:{
            items:10,
            dots:false,
          }
        }

      });


  // shop category

  // brands

  jQuery(".brand_new").owlCarousel({
 navigation : true, // Show next and prev buttons
        slideSpeed : 300,
        paginationSpeed : 400,
        items :8, 
        navText: ["<i class='fa fa-chevron-left'></i>", "<i class='fa fa-chevron-right'></i>"],
        nav:true,
        loop: false,
    rewind: true,
        responsive:{
          0:{
            items:1,
            nav:true,
            dots:false
          },
          400:{
            items:2,
            nav:true,
            dots:false
          },
          768:{
            items:4,
            nav:true,
            dots:false
          },

          1024:{
            items:4,
            nav:true,
            dots:false,
          },
          1111:{
            items:7,
            dots:false,
          },
          1366:{
            items:7,
            dots:false,
          },
          1921:{
            items:10,
            dots:false,
          }
        }
      });

  jQuery(".pre_order_slider_main").owlCarousel({

        navigation : true, // Show next and prev buttons
        slideSpeed : 300,
        margin:50,
        paginationSpeed : 400,
        items :1, 
        navText: ["<i class='fa fa-chevron-left'></i>", "<i class='fa fa-chevron-right'></i>"],
        nav:true,
        responsive:{
          0:{
            items:1,
            nav:false,
            dots:true
          },
          768:{
            items:1,
            nav:false,
            dots:true
          },

          1024:{
            items:1,
            dots:false,
          }
        }
      });


  // shop category

  jQuery(".deal_of_day").owlCarousel({

        navigation : true, // Show next and prev buttons
        slideSpeed : 300,
        paginationSpeed : 400,
        items :2, 
        margin:30,
        navText: ["<i class='fa fa-chevron-left'></i>", "<i class='fa fa-chevron-right'></i>"],
        nav:true,
        responsive:{
          0:{
            items:1,
            nav:false,
            dots:true
          },
          768:{
            items:1,
            nav:false,
            dots:true
          },

          1024:{
            items:1,
            dots:false,
          },
          1025:{
            items:2,
            dots:false,
          }
        }

      });

  // new product
  jQuery(".new_product_slider").owlCarousel({

        navigation : true, // Show next and prev buttons
        slideSpeed : 300,
        paginationSpeed : 400,
        items :4, 
        margin:30,
        navText: ["<i class='fa fa-chevron-left'></i>", "<i class='fa fa-chevron-right'></i>"],
        nav:true,
        responsive:{
          0:{
            items:1,
            nav:false,
            dots:true
          },
          425:{
            items:2,
            nav:false,
            dots:true
          },
          768:{
            items:2,
            nav:false,
            dots:true
          },

          1024:{
            items:3,
            dots:false,
          },
          1025:{
            items:4,
            dots:false,
          }
        }

      });
  // fixed menu
//if(jQuery('body').hasClass('catalog-category-view')== false) {
  //console.log(jQuery('body').hasClass('catalog-category-view'));
  if (jQuery(window).width() > 768) {  

    jQuery(window).bind('scroll', function () 
    {
      if (jQuery(window).scrollTop() >100) 
      {
       
        jQuery('header').removeClass('fixed_header');
        jQuery('.cat_sticky').removeClass('fixed_headerr');
        jQuery('header').addClass('fixed_header');
        jQuery('.cat_sticky').addClass('fixed_headerr');
        jQuery('.cat_sticky.fixed_headerr').height(jQuery(".search_menu").height());
      } 
      else 
      {
        jQuery('header').removeClass('fixed_header');
        jQuery('.cat_sticky').removeClass('fixed_headerr');
        jQuery('.cat_sticky').height(jQuery(".search_menu").height());
      }
    });

  }
  //}
  jQuery('.cat_sticky').height(jQuery(".search_menu").height()); 

  /*product details slider*/
  var sync1 = jQuery("#sync1");
  var sync2 = jQuery("#sync2");
      var slidesPerPage = 5; //globaly define number of elements per page
      var syncedSecondary = true;

      sync1.owlCarousel({
        items: 1,
        slideSpeed: 2000,
        nav: false,
        autoplay: false, 
        dots: false,
        loop: true,
        responsiveRefreshRate: 200,
      }).on('changed.owl.carousel', syncPosition);

      sync2
      .on('initialized.owl.carousel', function() {
        sync2.find(".owl-item").eq(0).addClass("current");
      })
      .owlCarousel({
        items: slidesPerPage,
        dots: false,
        loop:false,
        nav: true,
        smartSpeed: 200,
        responsive:{
          0:{
            items:2,
          },
          768:{
            items:2,
            nav:false,
          },

          1024:{
            items:2,
          },
          1025:{
            items:5,
          }
          
        },
        slideSpeed: 500,
              slideBy: slidesPerPage, // alternatively you can slide by 1, this way the active slide will stick to the first item in the second carousel
              responsiveRefreshRate: 100
            }).on('changed.owl.carousel', syncPosition2);

      function syncPosition(el) {
          //if you set loop to false, you have to restore this next line
          //var current = el.item.index;

          //if you disable loop you have to comment this block
          var count = el.item.count - 1;
          var current = Math.round(el.item.index - (el.item.count / 2) - .5);

          if (current < 0) {
            current = count;
          }
          if (current > count) {
            current = 0;
          }

          //end block

          sync2
          .find(".owl-item")
          .removeClass("current")
          .eq(current)
          .addClass("current");
          var onscreen = sync2.find('.owl-item.active').length - 1;
          var start = sync2.find('.owl-item.active').first().index();
          var end = sync2.find('.owl-item.active').last().index();

          if (current > end) {
            sync2.data('owl.carousel').to(current, 100, true);
          }
          if (current < start) {
            sync2.data('owl.carousel').to(current - onscreen, 100, true);
          }
        }

        function syncPosition2(el) {
          if (syncedSecondary) {
            var number = el.item.index;
            sync1.data('owl.carousel').to(number, 100, true);
          }
        }

        sync2.on("click", ".owl-item", function(e) {
          e.preventDefault();
          var number = jQuery(this).index();
          sync1.data('owl.carousel').to(number, 300, true);
        });

        /*product slider End*/

        /*price slider*/
        (function( jQuery ){
          jQuery( document ).ready( function() {
            jQuery( '.input-range').each(function(){
              var value = jQuery(this).attr('data-slider-value');
              var separator = value.indexOf(',');
              if( separator !== -1 ){
                value = value.split(',');
                value.forEach(function(item, i, arr) {
                  arr[ i ] = parseFloat( item );
                });
              } else {
                value = parseFloat( value );
              }
              jQuery( this ).slider({
                formatter: function(value) {
                 
                  return 'jQuery' + value;
                },
                min: parseFloat( jQuery( this ).attr('data-slider-min') ),
                max: parseFloat( jQuery( this ).attr('data-slider-max') ), 
                range: jQuery( this ).attr('data-slider-range'),
                value: value,
                tooltip_split: jQuery( this ).attr('data-slider-tooltip_split'),
                tooltip: jQuery( this ).attr('data-slider-tooltip')
              });
            });
            
          } );
        } )( jQuery );
        /*price slider End*/

      }); /*ready End*/



});
  function closeNav() {
   jQuery('#mySidenav').hide();
 }
 var nav2 = function () 
 {
  jQuery('#mySidenav').toggle();

  var x = jQuery('.desktop_visible_one').offset().top;
  console.log(x);
  jQuery('#mySidenav').css({'top' : x + 'px'});
};
function productQuickView() {
  require(['jquery'],function($){
    var iFrameID = document.getElementById('product-quickview');
        //jQuery("#product-quickview").parent().css("height",(iFrameID.contentWindow.document.body.scrollHeight)+'px');
        if(iFrameID.contentWindow.document.body.scrollHeight){
          setTimeout(function(){
            jQuery("#product-quickview").parent().css("height",(iFrameID.contentWindow.document.body.scrollHeight)+'px');
            $('.loading-mask').hide();
          },500);
        }
      });
}

require(['jquery'],function($){
  jQuery(document).ready(function(){
    if(jQuery(window).width() < 992 ) {
    console.clear()

    const navExpand = [].slice.call(document.querySelectorAll('.nav-expand'))
    const backLink = `<li class="nav-item">
      <a class="nav-link nav-back-link" href="javascript:;">
        Solver
        <i class="fas fa-chevron-left"></i>
      </a>
    </li>`

    navExpand.forEach(item => {
      item.querySelector('.nav-expand-content').insertAdjacentHTML('afterbegin', backLink)
      //item.querySelector('.nav-link').addEventListener('click', () => item.classList.add('active'))
      item.querySelector('.nav-back-link').addEventListener('click', () => item.classList.remove('active'))
    })


    // ---------------------------------------
    // not-so-important stuff starts here

    const ham = document.getElementById('ham')
    ham.addEventListener('click', function() {
      //console.log("gfgfgd");
      document.body.classList.toggle('nav-is-toggled')
    })

    jQuery(".mobile_nav li.nav-item.nav-expand i").on("click", function(){
      jQuery(this).parent().addClass("active");
    });
  }
  });
});

function qtyKeyDown(e) {
  e = e || window.event;
      // Allow: backspace, delete, tab, escape, enter and .
      if (jQuery.inArray(e.keyCode, [46, 8, 9, 27, 13, 190]) !== -1 ||
           // Allow: Ctrl+A
           (e.keyCode == 65 && e.ctrlKey === true) || 
           // Allow: home, end, left, right
           (e.keyCode >= 35 && e.keyCode <= 39)) {
               // let it happen, don't do anything
             return;
           }
      // Ensure that it is a number and stop the keypress
      if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
        e.preventDefault();
      }
    }

