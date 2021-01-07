/* global $, $H */

define([
    'underscore',
    'prototype'
], function (_) {
    'use strict';

    return {
        warehouses: $H(),
        warehouseInputId: 'in_warehouses',
        editableElementNames: {
            'stock_status' : 'stockElement',
            'backorders':'backordersElement',
            'qty':'totalQtyElement',
            'room_shelf':'roomShelfElement'
        },
        initialize: function (selectedWarehouses) {
            this.warehouses = $H(selectedWarehouses);
            $(this.warehouseInputId).value = Object.toJSON(this.warehouses);
        },

        /**
         * Register Warehouse
         *
         * @param {Object} grid
         * @param {Object} element
         * @param {Boolean} checked
         */
        registerWarehouse: function (grid, element, checked) {
            _.each(this.editableElementNames, function (index, inputName) {
                if (element.hasOwnProperty(index)) {
                    element[index].disabled = !checked;
                }
            });
            if (checked) {
                this.updateWarehouse(element);
            } else {
                this.warehouses.unset(element.value);
                $(this.warehouseInputId).value = Object.toJSON(this.warehouses);
            }

            grid.reloadParams = {
                'selected_warehouses[]': this.warehouses.keys()
            };
        },

        /**
         * @param {Object} element
         */
        updateWarehouse: function (element) {
            var values = {};
            _.each(this.editableElementNames, function (index, inputName) {
                if (element.hasOwnProperty(index)) {
                    values[inputName] = element[index].value;
                }
            });
            if (values && element.checkboxElement) {
                this.warehouses.set(element.checkboxElement.value, values);
                $(this.warehouseInputId).value = Object.toJSON(this.warehouses);
            }
        },

        /**
         * @param {String} event
         */
        qtyChange: function (event) {
            var element = Event.element(event);
            var qty = element.value = parseFloat(element.value.replace(/[^\d,-]/g, ''));
            if (isNaN(qty)) {
                return;
            }
            var ship = parseFloat(element.qtyShip.innerHTML);
            var availableQty = qty - ship;

            element.qtyAvailable.innerHTML = availableQty;

            if (availableQty == 0) {
                element.stockElement.value = 0;
            } else if (availableQty > 0) {
                element.stockElement.value = 1;
            }
            if (element.checkboxElement.checked) {
                this.updateWarehouse(element);
            }
        },

        /**
         * @param {String} event
         */
        roomShelfChange: function (event) {
            var element = Event.element(event);

            if (element && element.checkboxElement && element.checkboxElement.checked) {
                this.updateWarehouse(element);
            }
        }
    }
});
