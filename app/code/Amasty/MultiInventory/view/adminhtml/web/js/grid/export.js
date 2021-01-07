/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'jquery',
    'underscore',
    'Magento_Ui/js/grid/export',
    'mage/translate'
], function ($, _, Element, $t) {
    'use strict';

    return Element.extend({
        defaults: {
            template: 'Amasty_MultiInventory/grid/exportButton',
            warehouses: null,
            checkWh:([]), //checkboxes
            placeholder: $t('File Name'),
            input: ''
        },
        initialize: function () {
            this._super();
            this.initCheckedWh();
        },
        initObservable: function () {
            this._super()
                .observe('warehouses', this.warehouses)
                .observe('checkWh', this.checkWh)
                .observe('input', this.input);
            return this;
        },
        initCheckedWh: function () {
            var self = this;
            _.each(this.warehouses(), function (value) {
                self.checkWh.push({wh:value.value, value:1});
            });
        },
        checkVal: function (value) {
            var bool = false;
            var self = this;
            var wh = this.checkWh();
            _.each(wh, function (val, index) {
                if (val.wh == value) {
                    bool = self.setVal(val.value);
                    wh[index].value = bool;
                }
            });
            return bool;
        },
        setVal: function (value) {
            if (value == 1) {
                return 0;
            };
            if (value == 0) {
                return 1;
            }
        },
        getParams: function () {
            var selections = this.selections(),
                data = selections ? selections.getSelections() : null,
                itemsType,
                result = {};

            if (data) {
                itemsType = data.excludeMode ? 'excluded' : 'selected';
                result.filters = data.params.filters;
                result.search = data.params.search;
                result.namespace = data.params.namespace;
                var warehouses = [];
                _.each(this.checkWh(), function (value) {
                    if (value.value == 1) {
                        warehouses.push(value.wh);
                    }
                });
                result.filename = this.input;
                result.warehouses = warehouses;
                result[itemsType] = data[itemsType];

                if (!result[itemsType].length) {
                    result[itemsType] = false;
                }
            }

            return result;
        },
    });
});
