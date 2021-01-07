define([
    'Magento_Ui/js/grid/columns/column',
    'jquery',
    'underscore'
], function (Column, $,_) {
    'use strict';
    return Column.extend({
        defaults: {
            bodyTmpl: 'Amasty_MultiInventory/grid/warehouse',
            lists:([])
        },
        initObservable: function () {
            this._super();
            this.observe('lists', this.lists);
            return this;
        },
        getList: function (record) {
               this.lists = [];
                var data = $.parseJSON(record.warehouse);
                var self = this;
                $.each(data, function (index, value) {
                    self.lists.push(value);
                });
            return this.lists;
        }
    });

});
