define([
    'Magento_Ui/js/form/element/select',
    'jquery'
], function(Abstract, $) {
    'use strict';

    return Abstract.extend({
        initialize: function () {
            this._super();

            return this;
        },
        /**
         * Change value of radio
         */
        change: function(value) {
            alert(value);
            if (value === 'Home') {
                jQuery("input[name='address_type']:checked").val()
                // logic
            } else if (value === 'Work') {
                jQuery("input[name='address_type']:checked").val()
                // logic
            }else if (value === 'Other') {
                jQuery("input[name='address_type']:checked").val()
                // logic
            }
        }
    });
});
