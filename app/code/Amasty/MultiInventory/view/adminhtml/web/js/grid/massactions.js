
define([
    'underscore',
    'uiRegistry',
    'mageUtils',
    'Magento_Ui/js/grid/massactions'
], function (_, registry, utils, Collapsible) {
    'use strict';

    return Collapsible.extend({
        defaults:{
            regexp:'\\\/import_number\\\/(\\w+)\\\/',
        },
        defaultCallback: function (action, data) {
            var url = document.location.href;
            var regexp = new RegExp(this.regexp, 'ig');
            var result = regexp.exec(url);
            if (_.isArray(result)) {
                var import_number = result[1];
                data.params.import_number = import_number;
            }

            this._super();

            return this;
        },

    });
});
