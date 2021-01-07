define([
    'underscore',
    'ko',
    'uiComponent',
    'jquery'
], function (_, ko, Component, $) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'Mirasvit_Rma/return-address'
        },

        initialize: function () {
            this._super();
            this._bind();

            return this;
        },
        _bind: function () {
            var defaultAddress = $('#return_address_text').html();
            $('body').on('change', '#return_address', function (e, v) {
                if ($(this).val() == 0) {
                    $('#return_address_text').html(defaultAddress);
                } else {
                    $('#return_address_text').html($(this).val().replace(/([^>\r\n]?)(\r\n|\n\r|\r|\n)/g, '$1<br>$2'));
                }
            });
        }
    });
});
