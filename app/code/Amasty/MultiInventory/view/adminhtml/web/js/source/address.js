define([
    'Magento_Ui/js/form/element/abstract',
    'Amasty_MultiInventory/js/google/autocomplete',
    'uiRegistry',
    'jquery',
    'underscore'
], function (Text, Autocomplete, reg, $) {
    'use strict';
    return Text.extend({
        defaults: {
            showFallbackReset: false
        },
        initialize: function () {
            this._super();

            if (window.isAddressSuggestionEnabled != 1) {
                return;
            }

            $.async('[data-index="address"] input', function (input) {
                var parent = this.parentName;
                var componentForm = {
                    postal_code: reg.get(parent + '.zip'),
                    locality: reg.get(parent + '.city'),
                    country: reg.get(parent + '.country'),
                    administrative_area_level_1: reg.get(parent + '.state')
                };
                var autocomplete = Object.create(Autocomplete);
                autocomplete.setComponents(componentForm);
                autocomplete.regionUpdate = 1;
                autocomplete.setAddress(this);
                autocomplete.init(this.uid);
            }.bind(this));
        }
    });
});
