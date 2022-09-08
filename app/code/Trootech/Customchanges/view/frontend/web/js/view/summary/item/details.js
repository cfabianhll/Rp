/*jshint browser:true jquery:true*/
/*global alert*/
define(
    [
        'uiComponent',
        'Magento_Checkout/js/model/quote'
    ],
    function (Component,quote) {
        "use strict";
        var quoteItemData = window.checkoutConfig.quoteItemData;
        var liststoreJson = window.liststoreJson;

        return Component.extend({
            defaults: {
                template: 'Trootech_Customchanges/summary/item/details'
            },
            quoteItemData: quoteItemData,
            getValue: function(quoteItem) {
                return quoteItem.name;
            },
            getShippingType: function(quoteItem) {
                var item = this.getItem(quoteItem.item_id);
                return item.shipping_type;
            },
            getStoreAddress: function(quoteItem) {
                var item = this.getItem(quoteItem.item_id);
                return liststoreJson[0].store_name;
            },
            getItem: function(item_id) {
                var itemElement = null;
                _.each(this.quoteItemData, function(element, index) {
                    if (element.item_id == item_id) {
                        itemElement = element;
                    }
                });
                return itemElement;
            }
        });
    }
);