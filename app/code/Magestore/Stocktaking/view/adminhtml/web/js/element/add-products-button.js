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
            imports: {
                canVisibleOnForm: "${$.provider}:data.general_information.status"
            }
        },

        canVisibleOnForm: function(status) {
            let stocktakingType = registry.get(this.provider).data.general_information.stocktaking_type;
            if((status === 1 || status === '1')
                || ((status === 2 || status === '2') && (stocktakingType === '2' || stocktakingType === 2))) {
                this.visible(true);
            } else {
                this.visible(false);
            }
        }
    });
});
