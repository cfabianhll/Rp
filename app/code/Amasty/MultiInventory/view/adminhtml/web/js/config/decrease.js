define(
    [
        'jquery'
    ],
    function (jQuery) {
        'use strict';

        return {
            selector: 'amasty_multi_inventory_stock_decrease_stock',
            physical: 'amasty_multi_inventory_stock_decrease_physical',
            init: function () {
                var self = this;
                jQuery('#' + this.selector).on('change', function () {
                    self.stateRecord(jQuery(this).val());
                });
                jQuery(document).ready(function () {
                    var value = jQuery('#' + self.selector).val();
                    self.stateRecord(value);
                });
            },

            stateRecord: function (val) {
                if (val == 1) {
                    jQuery('#' + this.physical + " [value=1]").removeAttr("disabled");
                } else {
                    if (jQuery('#' + this.physical).val() == 1) {
                        jQuery('#' + this.physical + " [value=0]").attr("selected", "selected");
                    }
                    jQuery('#' + this.physical + " [value=1]").attr("disabled", "disabled");
                }
            },
        }
    }
);