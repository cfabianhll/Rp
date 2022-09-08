require(
    [
        'jquery',
        'Magento_Ui/js/modal/modal',
        'mage/template',
        'loader',
    ],
    function(
        $,
        modal,
        mageTemplate
    ) {
        var options = {
            type: 'popup',
            modalClass: 'amstore-popup-stock',
            title: '',
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

        var popup = modal(options, $('#store-popup-modal'));
        var productid = $("input[name=product]").val();

        $("#click-me-info").on('click',function(){
            
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
                 $("#store-popup-modal").modal("openModal");
                // console.log(data);
                var productName = data.product_name,
                 productImage = data.product_image,
                 totalRecords = data.amlocator_block.length,
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
                var html = mageTemplate('#store-popup-modal',
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
                            stock_info: stock_status,
                            product_name: productName,
                            product_image: productImage
                        }
                    });
                // console.log(id);
                $('#store-popup-modal').html(html);
                
            });

        });
    }
);

