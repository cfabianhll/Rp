/*
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'Magento_Ui/js/form/components/button'
], function ($, Component) {
    'use strict';

    return Component.extend({
        defaults: {
            elementTmpl: 'Magestore_PurchaseOrderSuccess/form/element/scan-barcode-button'
        },

        handleOnclick: function () {
            if ($('#inventorysuccess-barcode-input-wrapper').length) {
                $('#inventorysuccess-barcode-input-wrapper').toggle();
            } else {
                $('#inventorysuccess-barcode-input').toggle();
            }
            $('#inventorysuccess-barcode-input').focus();
        }
    });
});
