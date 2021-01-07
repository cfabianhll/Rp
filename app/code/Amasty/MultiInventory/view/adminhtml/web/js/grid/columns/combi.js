define([
    'jquery',
    'mage/translate',
    'Magento_Ui/js/grid/columns/column'
], function ($, $t, Column) {
    'use strict';

    return Column.extend({
        defaults: {
            headerTmpl: 'Amasty_MultiInventory/grid/columns/combi',
            bodyTmpl: 'Amasty_MultiInventory/grid/cells/combi',
            fieldClass: {
                'data-grid-combi-td': true
            },

            dataWh:([])
        },
        initObservable: function () {
            this._super()
                .observe(
                    'dataWh',
                    this.dataWh
                );
            return this;
        },
        getList: function (record) {
            this.dataWh = [];
            var self = this;
            var data = $.parseJSON(record[this.index]);
            $.each(data, function (index,value) {
                self.dataWh.push({key:'wh-'+index,value:value});
            });
            return this.dataWh;
        }
    });
});
