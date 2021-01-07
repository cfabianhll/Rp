define(
    [
        'jquery',
        'underscore',
        'uiRegistry'
    ],
    function (jQuery, _, registry) {

        return {
            globals: {
                isSimple: 0,
                enabled: 0,
                haveAssigned: 0
            },

            disable: function () {
                if (this.globals.isSimple == 1 && this.globals.enabled == 1) {
                    registry.async('product_form.product_form.product-details.quantity_and_stock_status_qty.qty')(function (provide) {
                        provide.disabled(true);
                    }.bind(this));
                    registry.async('product_form.product_form.advanced_inventory_modal.stock_data.qty')(function (provide) {
                        provide.disabled(true);
                    }.bind(this));
                    registry.async('product_form.product_form.product-details.container_quantity_and_stock_status.quantity_and_stock_status')(function (provide) {
                        provide.disabled(true);
                    }.bind(this));
                    registry.async('product_form.product_form.advanced_inventory_modal.stock_data.container_is_in_stock.is_in_stock')(function (provide) {
                        provide.disabled(true);
                    }.bind(this));
                }
            }
        };
    }
);
