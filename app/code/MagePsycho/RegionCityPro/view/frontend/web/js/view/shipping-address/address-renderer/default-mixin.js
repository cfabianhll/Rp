define([
    'jquery',
], function ($) {
    'use strict';

    return function (Component) {
        return Component.extend({
            getCustomAttributeLabel: function (attribute) {
                if ((attribute.attribute_code !== undefined && attribute.attribute_code == 'city_id')
                    || (attribute.city_id !== undefined)
                ) {
                    return;
                } else {
                    return this._super();
                }
            }
        });
    }
});
