define([
    'jquery',
    'mage/mage',
	'Magento_Ui/js/modal/modal'
], function ($, mage, modal) {
    "use strict";
    $.widget('bss.bss_config', {
        options: {
            productUrl: '',
            buttonText: '',
            isEnabled: false,
            baseUrl: '',
            productImageWrapper: '',
            productItemInfo: ''
        },

        _create: function () {
            this.renderButton();
            this._EventListener();
        },

        renderButton: function () {
            var $widget = this,
                id_product,
                productImageWrapper = '.' + this.options.productImageWrapper,
                productItemInfo = '.' + this.options.productItemInfo;
            if($widget.options.isEnabled == 1){
                $(productImageWrapper).each(function(){
                   //console.log($(this))
                    if ($(this).parents(productItemInfo).find('form input[name="product"]').val() !='') {
                        id_product = $(this).parent().parent().parent().find('form input[name="product"]').val();
						if(id_product) {
							$(this).parent().parent().parent().find('a.bss-quickview.hovercart.btn.theme_btn.img_btn').attr('data-quickview-url', $widget.options.productUrl+'id/'+ id_product);
						} else {
							id_product = $(this).parent().parent().parent().parent().find('form input[name="product"]').val();
							$(this).parent().parent().parent().parent().find('a.bss-quickview.hovercart.btn.theme_btn.img_btn').attr('data-quickview-url', $widget.options.productUrl+'id/'+ id_product);
						}
                    }
                    if (!id_product) {
                        id_product = $(this).parents(productItemInfo).find('form').data('product-id');
                    }
					if (!id_product) {
                        id_product = $(this).parent().parent().parent().parent().find('.actions-primary input[name="product"]').val();
						$(this).parent().parent().parent().parent().find('a.bss-quickview.hovercart.btn.theme_btn.img_btn').attr('data-quickview-url', $widget.options.productUrl+'id/'+ id_product);
                    }
                    if (!id_product) {
                        id_product = $(this).parent().parent().parent().parent().find('.product-item-info .price-box').attr('data-product-id');
                        $(this).parent().parent().parent().parent().find('a.bss-quickview.hovercart.btn.theme_btn.img_btn').attr('data-quickview-url', $widget.options.productUrl+'id/'+ id_product);
                    }
                    
                })

            }
        },

        _EventListener: function () {
            var $widget = this;
            if($widget.options.isEnabled == 1){

                $('a.mailto').click(function(e){
                    e.preventDefault();
                    window.top.location.href = $(this).attr('href');    
                    return true;
                });

                $('body, #layer-product-list').on('contentUpdated', function () {
                    $('.bss-bt-quickview').remove();
                    $widget.renderButton();
                });

                $(document).on('click','.bss-quickview', function() {
                    var prodUrl = $(this).attr('data-quickview-url');
                    if (prodUrl.length) {
                        $widget.openPopup(prodUrl);
                    }
                });
            }
        },

        openPopup: function (prodUrl) {
            var $widget = this,
                url = $widget.options.baseUrl + 'bss_quickview/index/updatecart';

            if (!prodUrl.length) {
                return false;
            }

            /* $.magnificPopup.open({
                items: {
                  src: prodUrl
                },
                type: 'iframe',
                closeOnBgClick: false,
                scrolling: false,
                preloader: true,
                tLoading: '',
                callbacks: {
                    open: function() {
                      $('.mfp-preloader').css('display', 'block');
                      $("iframe.mfp-iframe").contents().find("html").addClass("bss_loader");
                    },
                    beforeClose: function() {
                        $('[data-block="minicart"]').trigger('contentLoading');
                        $.ajax({
                            url: url,
                            method: "POST"
                        });
                    },
                    close: function() {
                      $('.mfp-preloader').css('display', 'none');
                    }
                }
            }); */
			$("iframe.mfp-iframe").attr('src', '');
			$('#product_quickview').on('shown.bs.modal', function (e) {
				$('.loading-mask').show();
				$("iframe.mfp-iframe").attr('src', prodUrl);
			});
			$('#product_quickview').on('hide.bs.modal', function (e) {
				$('[data-block="minicart"]').trigger('contentLoading');
					$.ajax({
						url: url,
						method: "POST"
					});
			})
        }

    });
    return $.bss.bss_config;
});
