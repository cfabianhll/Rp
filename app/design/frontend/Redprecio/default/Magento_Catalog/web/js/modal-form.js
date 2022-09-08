define([
    "jquery",
    'Magento_Ui/js/modal/modal',
    'mage/template',
    'loader'
], function($, modal,
            mageTemplate) {
    "use strict";

    return function (options, element) {
        $(element).click(function (event) {
            //window.open(options['path']);
            var modalForm= '#modal-form',
                modalButton= '.open-modal-form',
                productid = $(element).attr('data-product-id');

            console.log(productid);
            var form =  $(element).parent().children().eq(0);
            if(form.is(':input')){
                form = $(element).parent().parent().parent().parent();
            }
            var input = ('<input type="hidden" name="shipping_type" value="pickup">');
            console.log(form);
            if(!(form.find('input[name=shipping_type]').val())){
                $(form).children().eq(0).after(input);
            } else if (form.find('input[name=shipping_type]').val() === "delivery"){
                form.find('input[name=shipping_type]').replaceWith(input);

            }

            var options = {
                type: 'popup',
                modalClass: 'amstore-popup',
                title: '<h4><p>RECOGER GRATUITAMENTE EN TIENDA</p></h4><p>Acude a la tienda con tu identificación personal y el correo electronico con la confirmación <br> Compra rápido y ahorra tiempo con nosotros!.</p>',
                modalSubTitle: "Sub title",
                responsive: true,
                innerScroll: true,
                clickableOverlay: true,
                buttons: []
            };

            $.ajax({
                // showLoader: true,
                url: 'amlocator/index/ajax',
                data: {'product_id': productid},
                type: "POST",
                dataType: 'json',
                responsive: true,
                innerScroll: true,
                beforeSend: function () {
                    $("body").trigger('processStart');
                },
                /** @inheritdoc */
                success: function (res) {
                    $("body").trigger('processStop');
                },
            }).done(function (data) {
                $(modalForm).modal(options);
                //open modal
                $(modalForm).trigger('openModal');
                // console.log(data);
                var totalRecords = data.amlocator_block.length,
                    stock_status = data.stock_status,
                    address = [],
                    schedule = [],
                    phone_num = [],
                    name = [],
                    id = [],
                    img_url = [];
                // console.log(stock_status);
                for (var i = 0; i < totalRecords; i++) {
                    address[i] = data.amlocator_block[i].address;
                    schedule[i] = data.amlocator_block[i].schedule_string;
                    phone_num[i] = data.amlocator_block[i].phone;
                    name[i] = data.amlocator_block[i].store_name    ;
                    id[i] = data.amlocator_block[i].storepickup_id;
                    img_url[i] = data.location_img[i].url
                }
                var html = mageTemplate(modalForm,
                    {
                        posts: {
                            store_count: totalRecords,
                            location_info : {
                                location_name: name,
                                location_address: address,
                                location_schedule: "07:00PM-10:00PM",
                                location_phone_num: phone_num,
                                location_id: id,
                                location_img_url: img_url
                            },
                            stock_info: stock_status
                        }
                    });
                // console.log(id);
                $(modalForm).html(html);
                $(".popup-addtocart").on('click', function () {
                    var form =  $(element).parent().children().eq(0);
                    if(form.is(':input')){
                        form = $(element).parent().parent().parent().parent();
                    }

                    var store_pickup_id = $(this).parent().parent().eq(0).children().eq(0).children().eq(1).attr('storepickup_id');
                    var input = ('<input type="hidden" name="pickup_store_id" value="'+store_pickup_id+'">');
                    console.log(input);
                    if(!(form.find('input[name=pickup_store_id]').val())){
                        $(form).children().eq(0).after(input);
                    } else if(form.find('input[name=pickup_store_id]').val() !== store_pickup_id){
                        $(form).children().eq(0).replace(input);
                    }
                    form.submit();
                    $('.amstore-popup').find('.action-close').trigger('click');
                    setTimeout(function(){
                        form.find('input[name=pickup_store_id]').remove();
                        form.find('input[name=shipping_type]').remove();
                    }, 3000);


                });

            });

        });

    };

});