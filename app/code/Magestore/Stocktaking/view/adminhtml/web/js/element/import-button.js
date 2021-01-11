/*
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Ui/js/form/components/button',
    'uiRegistry'
], function ($, Component, registry) {
    'use strict';

    return Component.extend({
        defaults: {
            elementTmpl: 'Magestore_Stocktaking/element/import-button',
            imports: {
                canVisibleOnForm: "${$.provider}:data.general_information.status"
            }
        },

        /**
         * Handle click
         */
        handleOnclick: function () {
            $('#import-form').modal('openModal');
        },

        /**
         * Can visible on form
         *
         * @param status
         */
        canVisibleOnForm: function(status) {
            if((status === 1 || status === '1') || (status === 2 || status === '2')) {
                this.visible(true);
            } else {
                this.visible(false);
            }
        }
    });
});
