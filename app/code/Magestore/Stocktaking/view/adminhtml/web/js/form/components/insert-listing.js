/*
 * Copyright © 2020 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/components/insert-listing'
], function (Component) {
    'use strict';

    return Component.extend({
        defaults: {
            imports: {
                initParamsUpdate: "${$.provider}:data"
            },
            source_code: ''
        },

        initParamsUpdate: function(data) {
            this.source_code = data.general_information.source_code;
        },

        /**
         * Updates externalValue, from ajax request to grab selected rows data
         *
         * @param {Object} selections
         * @param {String} itemsType
         *
         * @returns {Object} request - deferred that will be resolved when ajax is done
         */
        updateFromServerData: function (selections, itemsType) {
            var filterType = selections && selections.excludeMode ? 'nin' : 'in',
                selectionsData = {},
                request;

            _.extend(selectionsData, this.params || {}, selections.params);

            if (selections[itemsType] && selections[itemsType].length) {
                selectionsData.filters = {};
                selectionsData['filters_modifier'] = {};
                selectionsData['filters_modifier'][this.indexField] = {
                    'condition_type': filterType,
                    value: selections[itemsType]
                };
            }

            selectionsData.paging = {
                notLimits: 1
            };

            if(this.source_code !== '') {
                selectionsData.source_code = this.source_code;
            }

            request = this.requestData(selectionsData, {
                method: this.requestConfig.method
            });
            request
                .done(function (data) {
                    this.setExternalValue(data.items || data);
                    this.loading(false);
                }.bind(this))
                .fail(this.onError);

            return request;
        },
    });
});
