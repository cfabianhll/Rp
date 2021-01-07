define([
    'underscore',
    'mageUtils',
    'Magento_Ui/js/form/element/abstract',
    'ko',
    'Magento_Ui/js/lib/validation/validator'
], function (_, utils, Abstract, ko, validator) {
    'use strict';

    return Abstract.extend({
        defaults: {
            list: ([]),
            valueUpdate: 'afterkeydown',
            listens: {
                'valueArea': 'onUpdateArea'
            }
        },

        initialize: function () {
            this._super();
            this.on('value', this.onUpdateArea.bind(this));
            var self = this;
            var list = this.value().split(",");
            _.each(list, function (value, index) {
                if (value.length > 0) {
                    self.list.push(value.trim());
                }
            });

            return this;
        },

        initObservable: function () {
            this._super();
            this.observe(['valueArea']);
            this.observe('list', this.list);
            return this;
        },

        onUpdateArea: function (value) {
            if (value.length > 1) {
                if (value.indexOf(",") !== -1 || value.indexOf(" ") !== -1) {
                    var newValue = value.slice(0, -1);
                    this.correctValue(newValue);
                }
            }
        },

        correctValue: function (email) {
            if (this.hasEmail(email)) {
                this.valueArea('');
                return false;
            }
            if (this.isValidEmail(email).passed) {
                this.list.push(email);
                this.joinList(this.list());
                this.valueArea('');
                return true;
            }

            return false;
        },

        isValidEmail: function (email) {
            return validator('validate-email', email);
        },

        OnBlurEvent: function (object) {
            if (this.valueArea() && this.valueArea().length > 0) {
                if (!this.correctValue(this.valueArea())) {
                    this.valueArea('');
                }
            }
        },

        deleteEmail: function (self, value, event) {
            event ? event.stopPropagation() : false;
            var key = -1;
            _.each(this.list(), function (element, index) {
                if (value == element) {
                    key = index;
                }
            });
            if (key > -1) {
                this.list.splice(key, 1);
                this.joinList(this.list());
                this.valueArea('');
            }
        },

        joinList: function (array) {
            this.value(array.join(","));
        },

        hasEmail: function (value) {
            return this.list().indexOf(value) != -1;
        }
    });
});
