define([
    'jquery',
    'underscore',
    'mageUtils',
    'uiLayout',
    'mage/translate',
    'Magento_Ui/js/grid/editing/editor'
], function ($, _, utils, layout, $t, Editor) {
    'use strict';
    Editor.defaults.bulkEnabled = false;
    return Editor.extend({
        defaults: {
            templates: {
                record: {
                    component: 'Amasty_MultiInventory/js/grid/editing/record'
                },
            },
        },
        getRowData: function (id, isIndex) {
            id = this.getId(id, isIndex);
            var self = this;
            return _.find(this.rowsData, function (row) {
                row = self.scopeData(row);
                return row[this.indexField] === id;
            }, this);
        },
        scopeData: function (row) {
            var self = this;
            _.each(row, function (value, index) {
                if (self.isJsonString(value)) {
                    var data = JSON.parse(value);
                    if (_.isObject(data)) {
                        row[index] = data;
                    }
                }
            });
            return row;
        },
        isJsonString: function (str) {
            try {
                JSON.parse(str);
            } catch (e) {
                return false;
            }
            return true;
        },
    });
});
