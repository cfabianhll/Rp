/*
 * Copyright © 2017 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/element/abstract'
], function (Abstract) {
    'use strict';

    return Abstract.extend({

        /**
         * Disable weight field if gift card product's type is virtual
         *
         * @param value
         */
        isDisableWeightField: function (value) {
            if (value === '2') {
                this.disabled(true);
            } else {
                this.disabled(false);
            }
        }
    });
});
