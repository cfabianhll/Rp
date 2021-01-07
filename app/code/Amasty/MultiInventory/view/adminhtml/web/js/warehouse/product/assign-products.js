/* global $, $H */

define([
    'Amasty_MultiInventory/js/warehouse/product/assign_abstract',
    'mage/adminhtml/grid'
], function (warehouseAbstract) {
    'use strict';

    return function (config) {
        var gridJsObject = window[config.gridJsObjectName],
            tabIndex = 1000;

        warehouseAbstract.warehouseInputId = 'in_warehouse_products';
        warehouseAbstract.initialize(config.selectedProducts);

        /**
         * Register Warehouse Product
         *
         * @param {Object} grid
         * @param {Object} element
         * @param {Boolean} checked
         */
        function registerWarehouseProduct(grid, element, checked)
        {
            warehouseAbstract.registerWarehouse(grid, element, checked);
        }

        /**
         * Click on product row
         *
         * @param {Object} grid
         * @param {String} event
         */
        function warehouseProductRowClick(grid, event)
        {
            var trElement = Event.findElement(event, 'tr'),
                isInput = Event.element(event).tagName === 'INPUT' || Event.element(event).tagName === 'SELECT',
                checked = false,
                checkbox = null;

            if (trElement) {
                checkbox = Element.getElementsBySelector(trElement, 'input');
                if (checkbox[0]) {
                    checked = isInput ? checkbox[0].checked : !checkbox[0].checked;
                    gridJsObject.setCheckboxChecked(checkbox[0], checked);
                }
            }
        }

        /**
         * Change product qty
         *
         * @param {String} event
         */
        function qtyChange(event)
        {
            warehouseAbstract.qtyChange(event);
        }

        /**
         * Change warehouse Shelf & Room
         *
         * @param {String} event
         */
        function roomShelfChange(event)
        {
            warehouseAbstract.roomShelfChange(event);
        }

        /**
         * Initialize warehouse product row
         *
         * @param {Object} grid
         * @param {String} row
         */
        function warehouseProductRowInit(grid, row)
        {

            var checkbox = $(row).getElementsByClassName('checkbox')[0],
                totalQty = $(row).getElementsByClassName('input-text')[0],
                qtySpan = $(row).getElementsByClassName('admin__grid-control-value')[0],
                roomShelf = $(row).getElementsByClassName('input-text')[1],
                qtyAvailable = $(row).getElementsByClassName('col-available_qty')[0],
                qtyShip = $(row).getElementsByClassName('col-ship_qty')[0],
                stock = $(row).getElementsBySelector('[name=stock_status]')[0],
                backorders = $(row).getElementsBySelector('[name=backorders]')[0];
            if (checkbox && totalQty && roomShelf) {
                if (!totalQty.value.length) {
                    totalQty.value = 0;
                    qtySpan.innerHTML = 0;
                    qtyAvailable.innerHTML = 0;
                    qtyShip.innerHTML = 0;
                }
                totalQty.checkboxElement
                    = roomShelf.checkboxElement
                    = checkbox.checkboxElement
                    = backorders.checkboxElement
                    = stock.checkboxElement
                    = checkbox;

                totalQty.totalQtyElement
                    = roomShelf.totalQtyElement
                    = checkbox.totalQtyElement
                    = backorders.totalQtyElement
                    = stock.totalQtyElement
                    = totalQty;

                totalQty.roomShelfElement
                    = roomShelf.roomShelfElement
                    = checkbox.roomShelfElement
                    = backorders.roomShelfElement
                    = stock.roomShelfElement
                    = roomShelf;

                totalQty.stockElement
                    = roomShelf.stockElement
                    = checkbox.stockElement
                    = backorders.stockElement
                    = stock.stockElement
                    = stock;

                totalQty.backordersElement
                    = roomShelf.backordersElement
                    = checkbox.backordersElement
                    = backorders.backordersElement
                    = stock.backordersElement
                    = backorders;

                totalQty.disabled = roomShelf.disabled = stock.disabled = backorders.disabled = !checkbox.checked;
                totalQty.tabIndex = tabIndex++;
                totalQty.qtyAvailable = qtyAvailable;
                totalQty.qtyShip = qtyShip;
                Event.observe(totalQty, 'keyup', qtyChange);
                roomShelf.tabIndex = tabIndex;
                Event.observe(roomShelf, 'keyup', roomShelfChange);
                if (checkbox.checked) {
                    warehouseAbstract.updateWarehouse(checkbox);
                }
            }
        }
        gridJsObject.rowClickCallback = warehouseProductRowClick;
        gridJsObject.initRowCallback = warehouseProductRowInit;
        gridJsObject.checkboxCheckCallback = registerWarehouseProduct;
        if (gridJsObject.rows) {
            gridJsObject.rows.each(function (row) {
                warehouseProductRowInit(gridJsObject, row);
            });
        }
    };
});
