
define([
    'Magento_Ui/js/form/components/fieldset'
], function (Fieldset) {
    'use strict';

    return Fieldset.extend(
        {
            defaults: {
                valuesForOptions: [],
                imports: {
                    toggleVisibility:
                        'amasty_multi_inventory_warehouse_form.amasty_multi_inventory_warehouse_form.general.is_general:value'
                },
                isShown: false,
                inverseVisibility: false
            },
            toggleVisibility: function (selected) {
                this.isShown = selected in this.valuesForOptions;
                this.visible(this.inverseVisibility ? this.isShown : !this.isShown);
            }
        }
    );
});
