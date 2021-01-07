/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
define([
    'underscore',
    'mage/translate',
    'Magento_Ui/js/grid/columns/multiselect'
], function (_, $t, Column) {
    'use strict';

    return Column.extend({
        initialize: function () {
            this._super();
            this.selectAll();

            return this;
        }
    });
});
