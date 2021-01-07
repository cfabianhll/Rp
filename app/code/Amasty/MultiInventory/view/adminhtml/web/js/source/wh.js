define([
    'jquery',
    'ko',
    'underscore',
    'Magento_Ui/js/form/element/abstract',
    'Magento_Ui/js/lib/validation/validator'
], function ($, ko, _, Abstract, validator) {
    'use strict';

    return Abstract.extend({
        defaults:{
            valueUpdate: 'afterkeydown',
            dataWh: ([]),
            editor: ['qty','room','stock_status'],
            qty: 0,
            room: '',
            available: 0,
            ship: 0,
            stock_status: 0,
            listens: {
                'qty': 'onUpdateQty',
                'room': 'onUpdateRoom',
                'stock_status': 'onUpdateStockStatus'
            }
        },
        initialize: function () {
            this._super();

            if (Number.isInteger(this.index)) {
                this.index = this.index.toString();
            }
            this.on('qty', this.onUpdateQty.bind(this));
            this.on('room', this.onUpdateRoom.bind(this));
            this.on('stock_status', this.onUpdateStockStatus.bind(this));
            var data = this.value();
            var self = this;
            $.each(data, function (index,value) {
                self[index](value);
            });

            return this;
        },

        initObservable: function () {
            this._super();
            this.observe(['qty', 'available', 'ship', 'room', 'stock_status']);
            this.observe('dataWh', this.dataWh);

            return this;
        },

        onUpdateQty: function (value) {
            var newValue = value;
            if (value != "-") {
                var val = this.validateQty(newValue);
                var qty = value;
                if (!val.passed) {
                    qty = newValue.slice(0, newValue.length - 1);
                }
                var object = this.value();
                object.qty = qty;
                object.available = object.qty - object.ship;
                if (object.available > 0) {
                    object.stock_status = 1;
                } else if (object.available == 0) {
                    object.stock_status = 0;
                }
                this.stock_status(object.stock_status);
                this.qty(object.qty);
                this.available(object.available);
            }
        },

        onUpdateRoom: function (value) {
            this.room(value);
        },

        onUpdateStockStatus: function (value) {
            this.stock_status(value);
        },

        getList: function () {
            this.dataWh = [];
            var self = this;
            var data = this.value();
            $.each(data, function (index,value) {
                var obsValue = value;
                self.dataWh.push({key:index,value: obsValue});
            });
            return this.dataWh;
        },

        isCheck: function (row) {
            if (_.indexOf(this.editor, row) != -1) {
                return true;
            }
            return false;
        },
        OnBlurEvent: function () {
            var object = this.value();
            object.qty = this.qty();
            object.available = this.available();
            object.room = this.room();
            object.stock_status = this.stock_status();
            this.value(object);
        },

        validateQty: function (value) {
            return validator('validate-number', value);
        },

        isStockStatus: function (key) {
            var isStockStatus = false;

            if (key === 'stock_status') {
                isStockStatus = true;
            }

            return isStockStatus;
        }
    });
});
