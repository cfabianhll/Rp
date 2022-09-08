define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote'
], function ($, wrapper, quote) {
    'use strict';

    return function (setShippingInformationAction) {
        return wrapper.wrap(setShippingInformationAction, function (originalAction, messageContainer) {
            var address = quote.shippingAddress();
            if (address !== null) {
                var cityIdElem = $("#shipping-new-address-form [name = 'custom_attributes[city_id]'] option:selected");
                var city = cityIdElem.text();
                var cityId = cityIdElem.val();
                messageContainer.city = city;
                messageContainer.city_id = cityId;
            }
            // pass execution to original action
            return originalAction(messageContainer);
        });
    };
});
