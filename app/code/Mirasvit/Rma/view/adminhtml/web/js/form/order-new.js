define(
    [
        'jquery',
        'ko',
        'uiComponent',
        'uiRegistry'
    ],
    function(
        $,
        ko,
        Component,
        registry
    ) {
        'use strict';
        var mainContainer = '#offline-order-main';
        var orderContainerSelector = '.offline-order-container';
        var orderContainer = '.offline-order-container.order';
        var addOrderButton = '.add-order-button-container button';
        var removeOrderButton = '.rma-create-order-form .order-container button[data-role="order-remove"]';
        var itemContainer = '.create-item-container .item-container';
        var addItemButton = '.create-item-container .add-item-button-container button';
        var removeItemButton = '.create-item-container .item-container button[data-role="item-remove"]';

        return Component.extend({
            initialize: function(element, valueAccessor, allBindings) {
                this._super();
                this.orderId = 0;
                this.itemId = 0;
                var self = this;
                $(document).on('click', addOrderButton, {componentObj: this}, this.addOrder);
                $(document).on('click', removeOrderButton, {componentObj: this}, this.removeOrder);
                $(document).on('click', addItemButton, {componentObj: this}, this.addItem);
                $(document).on('click', removeItemButton, this.removeItem);
                $(document).ready(function() {
                    self.addOrder();
                    $(addItemButton).click();
                });
            },
            addOrder: function(event) {
                var componentObj = this;
                if (typeof event != 'undefined' && typeof event.data.componentObj != 'undefined') {
                    componentObj = event.data.componentObj;
                }
    
                componentObj.orderId++;
                
                $(mainContainer).append($('#order_name').html().replace(/%%order_id%%/g, componentObj.orderId));
                $(componentObj.getOrderContainerSelector()).append($('#item_container').html());
            },
            addItem: function(event) {
                var componentObj = this;
                if (typeof event != 'undefined' && typeof event.data.componentObj != 'undefined') {
                    componentObj = event.data.componentObj;
                }
                var orderEl = $(this).parents('.offline-order-container').first();
                var orderNumber = $(orderEl).data('order-number');
                $(itemContainer, orderEl).append($('#item_returnreasons').html()
                    .replace(/%%item_id%%/g, componentObj.itemId)
                    .replace(/%%order_id%%/g, orderNumber)
                );
                componentObj.itemId++;
            },
            removeOrder: function() {
                $(this).closest(orderContainerSelector).remove();
            },
            removeItem: function() {
                $(this).closest('tr').remove();
            },
            validateOrder: function() {
                var result = $('.ordername').val().length > 0;
                if (!result) {
                    alert($.mage.__('Add at least one item to this RMA'));
                }
    
                result = true;
                $('input.itemname', itemContainer).each(function(index) {
                    if (result && $(this).val().length == 0) {
                        result = false;
                    }
                });
                if (!result) {
                    alert($.mage.__('Add at least one item to this RMA'));
                }
        
                return result;
            },
            getOrderContainerSelector: function() {
                return orderContainer + this.orderId;
            }
        });
    }
);