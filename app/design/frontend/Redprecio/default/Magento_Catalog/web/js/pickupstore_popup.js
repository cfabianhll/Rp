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
            title: 'Main title',
            modalSubTitle: "Sub title",
            responsive: true,
            innerScroll: true,
            clickableOverlay: true,
            buttons: [{
                text: $.mage.__('Ok'),
                class: 'mymodal1',
                click: function () {

                    this.closeModal();
                }
            }]
        };

        var popup = modal(options, $('#popup-modal'));
        $("#click-me").on('click',function(){
            var param = 'ajax=1';
            $.ajax({
                // showLoader: true,
                url: 'amlocator/index/ajax',
                data: param,
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

                console.log(data);
                var totalRecords = data.amlocator_block.length,
                    store_id = data.amlocator_block.storepickup_id,
                    stock_status = data.stock_status,
                    address = [],
                    schedule = [],
                    phone_num = [],
                    name = [],
                    id = [],
                    img_url = [];
                console.log(stock_status);
                for (var i = 0; i < totalRecords; i++) {
                    address[i] = data.amlocator_block[i].address;
                    schedule[i] = data.amlocator_block[i].schedule_string;
                    phone_num[i] = data.amlocator_block[i].phone;
                    name[i] = data.amlocator_block[i].store_name    ;
                    id[i] = data.amlocator_block[i].storepickup_id;
                    img_url[i] = data.location_img[i]
                }
                var html = mageTemplate('#popup-modal',
                    {
                        posts: {
                            storeid: store_id,
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
                // console.log(html);
                $('#popup-modal').html(html);

                $(".popup-addtocart").on('click', function () {
                    $('#product_addtocart_form').submit();
                });
            });

        });
    }
);

