define([
    'Magento_Ui/js/form/element/abstract',
    'uiRegistry',
    'jquery',
    'underscore'
], function (Select, reg, $, _, validator) {
    'use strict';
    return Select.extend({
        defaults: {
            valueUpdate:'afterkeydown'
        },
        onUpdate: function () {
            this._super();
            if (this.value() <=0) {
                this.value('');
            }
        }
    });
});
