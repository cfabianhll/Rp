require(
    [
        'jquery',
        'Magento_Ui/js/modal/modal',
        'mage/template',
        'loader',
        'Magento_Catalog/js/product/view/product-ids-resolver'
    ],
    function(
        $,
        modal,
        mageTemplate,
        idsResolver
    ) {
        // alert("LL");
        var options = {
            type: 'popup',
            modalClass: 'amstore-popup',
            title: '<h4><p>RECOGER GRATUITAMENTE EN TIENDA</p></h4><p>Acude a la tienda con tu identificación personal y el correo electronico con la confirmación <br> Compra rápido y ahorra tiempo con nosotros!.</p>',
            modalSubTitle: "Sub title",
            responsive: true,
            innerScroll: true,
            clickableOverlay: true,
            buttons: [
            /*{
                text: $.mage.__('Ok'),
                class: 'mymodal1',
                click: function () {
                    this.closeModal();
                }
            }*/
            ]
        };

        var popup = modal(options, $('#popup-modal'));
        var productid = $("input[name=product]").val();

        // var input = ('<input type="hidden" name="shipping_type  " value="delivery">');
        // console.log(product_id);
        $('#product-addtocart-button').on('click', function () {
            var form =  $('#product_addtocart_form');
            var input = ('<input type="hidden" name="shipping_type" value="delivery">');
            if(!(form.find('input[name=shipping_type]').val())){
                $(form).children().eq(0).after(input);
            }else if(form.find('input[name=shipping_type]').val() === "pickup"){
                form.find('input[name=shipping_type]').replaceWith(input);
                form.find('input[name=pickup_store_id]').remove();
            }

        });
        $("#click-me").on('click',function(){
            var form =  $('#product_addtocart_form');
            var input = ('<input type="hidden" name="shipping_type" value="pickup">');
            console.log(form);
            if(!(form.find('input[name=shipping_type]').val())){
                $(form).children().eq(0).after(input);
            } else if (form.find('input[name=shipping_type]').val() === "delivery"){
                form.find('input[name=shipping_type]').replaceWith(input);
                // form.find('input[name=pickup_store_id]').remove(input);

            }
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
                var openModal = $("#popup-modal").modal("openModal");
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
                var html = mageTemplate('#popup-modal',
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
                $('#popup-modal').html(html);
                $(".popup-addtocart").on('click', function () {
                    var form =  $('#product_addtocart_form');
                    var store_pickup_id = $(this).parent().parent().parent().children().eq(1).attr('storepickup_id');
                    
                    // console.log($(this).parent().parent().parent().children().eq(1).attr('storepickup_id'));
                    var input = ('<input type="hidden" name="pickup_store_id" value="'+store_pickup_id+'">');
                    console.log(input);
                    if(!(form.find('input[name=pickup_store_id]').val())){
                        $(form).children().eq(0).after(input);
                    } else if(form.find('input[name=pickup_store_id]').val() !== store_pickup_id){
                        $(form).children().eq(0).replace(input);
                    }
                    $('#product_addtocart_form').submit();
                });
            });

        });
    }
);

