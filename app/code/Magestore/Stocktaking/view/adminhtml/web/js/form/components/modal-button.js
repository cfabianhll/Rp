/*
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/components/html'
], function (Html) {
    'use strict';

    return Html.extend({
        defaults: {
            CONST_STATUS_VERIFYING: 3,
            stocktakingCurrentStatus: '',
        },

        /**
         * Invokes initialize method of parent class,
         * contains initialization logic
         */
        initialize: function () {
            this._super()
                .initVisible();

            return this;
        },

        /**
         * Check if scan barcode input can be visible
         */
        initVisible: function () {
            if(parseInt(this.stocktakingCurrentStatus) === this.CONST_STATUS_VERIFYING) {
                this.visible(false);
            }
        },

        /**
         * Init stock-taking status
         *
         * @param data
         */
        stocktakingStatus: function(data) {
            this.stocktakingCurrentStatus = data;
        },
    });
});
