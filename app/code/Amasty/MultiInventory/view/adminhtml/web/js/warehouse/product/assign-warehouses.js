/* global $, $H */

define([
    'Amasty_MultiInventory/js/warehouse/product/assign_abstract',
    'uiRegistry',
    'mage/adminhtml/grid'
], function (warehouseAbstract, registry) {
    'use strict';

    return function (config) {
        var gridJsObject = window[config.gridJsObjectName],
            available = config.available,
            tabIndex = 1000,
            disableId = config.disableId,
            haveAssigned = false,
            stockStatusEventAttached = [];

        warehouseAbstract.warehouseInputId = 'in_warehouses';
        warehouseAbstract.initialize(config.selectedWarehouses);

        /**
         * Register Warehouse
         *
         * @param {Object} grid
         * @param {Object} element
         * @param {Boolean} checked
         */
        function registerWarehouse(grid, element, checked)
        {
            warehouseAbstract.registerWarehouse(grid, element, checked);
        }

        /**
         * Click on product row
         *
         * @param {Object} grid
         * @param {String} event
         */
        function warehouseRowClick(grid, event)
        {
            var trElement = Event.findElement(event, 'tr'),
                isInput = Event.element(event).tagName === 'INPUT' || Event.element(event).tagName === 'SELECT',
                checked = false,
                checkbox = null,
                warehouse = Element.getElementsBySelector(trElement, 'a')[0].text;

            if (trElement) {
                checkbox = Element.getElementsBySelector(trElement, 'input');
                if (checkbox[0] && checkbox[0].value != disableId) {
                    checked = isInput ? checkbox[0].checked : !checkbox[0].checked;
                    if (checkbox[0].disabled) {
                        checked = false;
                    }
                    gridJsObject.setCheckboxChecked(checkbox[0], checked);

                    /* Add event listener only once on each table row. */
                    if (stockStatusEventAttached[warehouse] === undefined && checked === true) {
                        var trSelects = Element.getElementsBySelector(trElement, 'select');
                        trSelects.forEach(function (element) {
                            element.on('change', function () {
                                warehouseAbstract.updateWarehouse(checkbox[0]);
                            });
                        });

                        stockStatusEventAttached[warehouse] = true;
                    }
                }
            }
        }

        /**
         * Change product totalQty
         *
         * @param {String} event
         */
        function totalQtyChange(event)
        {
            warehouseAbstract.qtyChange(event);

            calcTotal();
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
         * Initialize warehouse row
         *
         * @param {Object} grid
         * @param {String} row
         */
        function warehouseRowInit(grid, row)
        {
            var checkbox = $(row).getElementsByClassName('checkbox')[0],
                totalQty = $(row).getElementsByClassName('input-text')[0],
                roomShelf = $(row).getElementsByClassName('input-text')[1],
                qtyAvailable = $(row).getElementsByClassName('col-available_qty')[0],
                qtyShip = $(row).getElementsByClassName('col-ship_qty')[0],
                stock = $(row).getElementsBySelector('[name=stock_status]')[0],
                backorders = $(row).getElementsBySelector('[name=backorders]')[0];
            if (checkbox && totalQty && roomShelf) {
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
                if (checkbox.checked) {
                    haveAssigned = true;
                }
                totalQty.tabIndex = tabIndex++;
                totalQty.qtyAvailable = qtyAvailable;
                totalQty.qtyShip = qtyShip;
                roomShelf.tabIndex = tabIndex;
                if (checkbox.value == disableId) {
                    var label = $(row).getElementsByClassName('data-grid-checkbox-cell-inner')[0];
                    label.setAttribute('style', 'display:none');
                    totalQty.disabled = stock.disabled = checkbox.disabled;
                    roomShelf.setAttribute('style', 'display:none');
                    backorders.setAttribute('style', 'display:none');
                }
                Event.observe(totalQty, 'keyup', totalQtyChange);
                Event.observe(roomShelf, 'keyup', roomShelfChange);
                Event.observe(stock, 'change', changeStockStatus);
            }
        }

        function changeStockStatus(event)
        {
            var $rows = gridJsObject.rows,
                stockStatus = 0,
                totalStockStatusElement;
            for (var key in $rows) {
                if (typeof $rows[key] == 'object') {
                    var checkbox = $($rows[key]).getElementsByClassName('checkbox')[0],
                        status = $($rows[key]).getElementsBySelector('[name=stock_status]')[0];
                    if (checkbox.value == disableId) {
                        totalStockStatusElement = status;
                    }
                    if (checkbox.value != disableId && checkbox.checked && status.value == '1') {
                        stockStatus = 1;
                    }
                }
            }
            updateStockStatus(stockStatus);
            totalStockStatusElement.value = stockStatus;
        }

        function updateStockStatus(totalStockStatus)
        {
            registry.async('product_form.product_form.advanced_inventory_modal.stock_data.container_is_in_stock.is_in_stock')(
                function (provide) {
                    provide.value(totalStockStatus);
                }
            );
        }

        function calcTotal()
        {
            var $rows = gridJsObject.rows;
            var totalElement;
            var totalAvailable;
            var totalStockStatusElement;
            var total = 0;
            var totalAv = 0;
            for (var key in $rows) {
                if (typeof $rows[key] == 'object') {
                    var checkbox = $($rows[key]).getElementsByClassName('checkbox')[0],
                        totalQty = $($rows[key]).getElementsByClassName('input-text')[0],
                        qtyAvailable = $($rows[key]).getElementsByClassName('col-available_qty')[0];
                    if (checkbox.value == disableId) {
                        totalStockStatusElement = $($rows[key]).getElementsBySelector('[name=stock_status]')[0];
                        totalElement = totalQty;
                        totalAvailable = qtyAvailable;
                    }
                    if (checkbox.value != disableId) {
                        if (checkbox.checked) {
                            total += parseInt(totalQty.value);
                            totalAv += parseInt(qtyAvailable.innerHTML);
                        }
                    }
                }
            }
            totalElement.value = total;
            totalAvailable.innerHTML = totalAv;
            if (totalAv == 0) {
                totalStockStatusElement.value = 0;
                updateStockStatus(0);
            } else if (totalAv > 0) {
                totalStockStatusElement.value = 1;
                updateStockStatus(1);
            }
            updateQty(total);
        }

        function updateQty(sum)
        {
            registry.async('product_form.product_form.product-details.quantity_and_stock_status_qty.qty')(function (provide) {
                provide.value(sum);
            });
        }

        gridJsObject.rowClickCallback = warehouseRowClick;
        gridJsObject.initRowCallback = warehouseRowInit;
        gridJsObject.checkboxCheckCallback = registerWarehouse;

        if (gridJsObject.rows) {
            gridJsObject.rows.each(function (row) {
                warehouseRowInit(gridJsObject, row);
            });
        }
    };
})
;
