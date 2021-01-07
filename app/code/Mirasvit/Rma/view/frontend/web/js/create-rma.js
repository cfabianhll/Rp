define([
    'underscore',
    'ko',
    'uiComponent',
    'jquery',
    'jqueryMultiFile'
], function (_, ko, Component, $, jqueryMultiFile) {
    'use strict';

    window.offlineOrderNumber = 0;
    window.offlineItemNumber = 0;

    var existingOnlineFields = document.getElementsByClassName('regularOrder');
    var existingOfflineFields = document.getElementsByClassName('offlineOrder');
    var offlineOrderCount = 0;
    var onlineOrderCount = 0;

    return Component.extend({
        showRmaAdditions: ko.observable(0),
        isAllowedToAddOrder: ko.observable(1),
        isAllowedStoreOrder: ko.observable(1),
        orderSelectorTitle: ko.observable($.mage.__('Please, select an order')),

        isAllowedOfflineOrder: 0,
        isInitFileUploader: 0,
        containerSelector: '.ui-mst-rma__create-rma',
        rmaOrderContainerSelector: '.ui-rma-order-container',
        addItemButtonContainerSelector: '.ui-add-item-button-container',
        removeItemButtonContainerSelector: '.ui-remove-item-button-container',

        defaults: {
            template:                'Mirasvit_Rma/create-rma',
            OrderTemplateUrl:        '',
            OfflineOrderTemplateUrl: '',
            isAllowedOfflineOrder:   false,
            isAllowedMulitpleOrders: false,
            allowedOrder:            []
        },

        initialize: function () {
            this._super();
            this._bind();

            return this;
        },

        _bind: function () {
            var self = this;
            var body = $('body');

            self.isAllowedOfflineOrder = parseInt(self.isAllowedOfflineOrder);
            self.isAllowedStoreOrder   = parseInt(self.isAllowedStoreOrder);
            body.on('click', '.mst-rma-create__order .remove', function () {
                var el = $(this).closest('.rma-step2');
                if ($(el).children('.ui-offline-order-container').length > 0) {
                    offlineOrderCount--;
                } else {
                    onlineOrderCount--;
                }
                el.remove();
                if ((onlineOrderCount < 1) && existingOnlineFields) {
                    $(existingOnlineFields).hide();
                    if ($(existingOnlineFields).hasClass('required')) {
                        $(existingOnlineFields).addClass('ignore-validate').addClass('required-for-online').hide()
                            .removeClass('required');
                    }
                }
                if ((offlineOrderCount < 1) && existingOfflineFields) {
                    $(existingOfflineFields).hide();
                    if ($(existingOfflineFields).hasClass('required')) {
                        $(existingOfflineFields).addClass('ignore-validate').addClass('required-for-offline').hide()
                            .removeClass('required');
                    }
                }

                if (!$('.ui-rma-order-container > div').length) {
                    self.orderSelectorTitle($.mage.__('Please, select an order'));
                    self.showRmaAdditions(0);
                    if (self.isAllowedMulitpleOrders == 0) {
                        self.isAllowedToAddOrder(1);
                    }
                }
            });

            body.on('click', this.addItemButtonContainerSelector, function () {
                var parent = $(this).closest('.ui-offline-order-container');
                var orderNumber = $('.ui-receiptnumber', parent).data('order-number');
                var html = $('#item_returnreasons').html().replace(/%%item_id%%/g, window.offlineItemNumber)
                    .replace(/%%order_id%%/g, orderNumber);
                $('.ui-offline-items-container', parent).append(html);
                window.offlineItemNumber++;
            });

            body.on('click', this.removeItemButtonContainerSelector, function () {
                $(this).closest('.rma-one-item').remove();
            });

        },

        addOfflineOrder: function () {
            if (!this.isAllowedToAddOrder()) {
                return;
            }
            this.initFileUploader();
            this.loader(true);

            var self = this;

            $.ajax({
                url:      this.OfflineOrderTemplateUrl,
                type:     'POST',
                dataType: 'json',
                complete: function (data) {
                    self.loader(false);

                    data = data.responseJSON;

                    if (data.error) {
                        alert(data.error);
                    } else {
                        window.offlineOrderNumber++;

                        var html = data.blockHtml.replace(/%%order_id%%/g, window.offlineOrderNumber)
                            .replace(/%%item_id%%/g, window.offlineItemNumber);

                        $(self.rmaOrderContainerSelector).append(html);

                        var el = $('.ui-offline-order-container', self.rmaOrderContainerSelector).last();

                        $(self.addItemButtonContainerSelector, el).click();

                        self.orderSelectorTitle($.mage.__('Add another order'));
                        self.showRmaAdditions(1);
                        offlineOrderCount++;

                        if ((onlineOrderCount < 1) && existingOnlineFields) {
                            $(existingOnlineFields).hide();
                            if ($(existingOnlineFields).hasClass('required')) {
                                $(existingOnlineFields).addClass('ignore-validate').addClass('required-for-online').hide()
                                    .removeClass('required');
                            }
                        }
                        if (offlineOrderCount > 0) {
                            $(existingOfflineFields).show();
                            if ($(existingOfflineFields).hasClass('required-for-offline')) {
                                $(existingOfflineFields).addClass('required')
                                    .removeClass('required-for-offline').removeClass('ignore-validate');
                            }
                        }

                        if (self.isAllowedMulitpleOrders == 0) {
                            self.isAllowedToAddOrder(0);
                        }
                    }
                }.bind(this)
            });
        },

        addSelectedStoreOrder: function () {
            if (!this.isAllowedToAddOrder()) {
                return;
            }
            var orderId = $('#selected_order_id').val();
            var orderSelector = '.rma-order-id-' + orderId;
            this.initFileUploader();

            if (orderId > 0) {
                if (!$(orderSelector).length) {
                    this.loader(true);

                    var data = {"order_id": orderId};
                    var self = this;
                    $.ajax({
                        url:      this.OrderTemplateUrl,
                        type:     'POST',
                        dataType: 'json',
                        data:     data,
                        complete: function (data) {
                            self.loader(false);

                            data = data.responseJSON;

                            if (data.error) {
                                alert(data.error);
                            } else {
                                $(self.rmaOrderContainerSelector).append(data.blockHtml);
                            }

                            self.orderSelectorTitle($.mage.__('Add another order'));
                            self.showRmaAdditions(1);
                            onlineOrderCount++;
                            if ((offlineOrderCount < 1) && existingOfflineFields) {
                                $(existingOfflineFields).hide();
                                if ($(existingOfflineFields).hasClass('required')) {
                                    $(existingOfflineFields).addClass('ignore-validate').addClass('required-for-offline').hide()
                                        .removeClass('required');
                                }
                            }
                            if (onlineOrderCount > 0) {
                                $(existingOnlineFields).show();
                                if ($(existingOnlineFields).hasClass('required-for-online')) {
                                    $(existingOnlineFields).addClass('required')
                                        .removeClass('required-for-online').removeClass('ignore-validate');
                                }
                            }

                            if (self.isAllowedMulitpleOrders == 0) {
                                self.isAllowedToAddOrder(0);
                            }
                        }.bind(this)
                    });
                } else {
                    alert($.mage.__('Order already exists in current RMA'));
                }
            } else {
                alert($.mage.__('Select Order'));
            }
        },

        loader: function (show) {
            if (show) {
                $(this.containerSelector).trigger('processStart');
            } else {
                $(this.containerSelector).trigger('processStop');
            }
        },

        initFileUploader: function () {
            if (!this.isInitFileUploader) {
                $('.multi').MultiFile();
                this.isInitFileUploader = 1;
            }
        }
    });
});