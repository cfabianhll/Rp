/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'underscore',
    'mageUtils',
    'uiLayout',
    'Magento_Ui/js/grid/editing/record'
], function (_, utils, layout, Record) {
    'use strict';

    return Record.extend({
        defaults : {
            templates: {
                fields: {
                    text: {
                        component: 'Amasty_MultiInventory/js/source/wh',
                        template: 'Amasty_MultiInventory/form/element/wh'
                    }
                }
            }
        }
    });
});
