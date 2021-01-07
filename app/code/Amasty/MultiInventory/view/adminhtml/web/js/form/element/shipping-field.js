
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
            valueUpdate: 'afterkeydown',
            methods: ([])
        },
        initialize: function () {

            var self = this;

            _.each(this.methods, function (element) {
                self.on(element.key, self.onUpdateMethod.bind(self));
            });
            this._super();
            _.each(this.methods(), function (element) {
                self[element.key](element.value);
            });

            return this;
        },
        initConfig: function (config) {
            this._super();
            var self = this;
            _.each(config.options, function (option) {
                if (option.label && option.label.length > 0) {
                    self.methods.push({key: option.value, value: option.rate, label: option.label});
                    self.listens[option.value] = 'onUpdateMethod';
                    var key = option.value;
                    _.extend(self, {
                        key: 0
                    });
                }
            });

            return this;
        },
        onUpdateMethod: function (value) {
           //should be empty for correct work component
        },
        initObservable: function () {
            this._super();
            this.observe('methods', this.methods);
            var array = [];
            _.each(this.methods(), function (element, index) {
                array.push(element.key);
            });
            this.observe(array);

            return this;
        },
        validateQty: function (value) {
            return validator('validate-number', value);
        },
        set: function (path, value, owner) {
            if (path == 'onUpdateMethod') {
                var newValue = value;
                var val = this.validateQty(value);
                if (!val.passed) {
                    newValue = newValue.slice(0, newValue.length - 1);
                    owner.component[owner.property](newValue);
                }

                value = newValue;
            }
            this._super(path, value, owner);

            return this;
        },


    });
});
