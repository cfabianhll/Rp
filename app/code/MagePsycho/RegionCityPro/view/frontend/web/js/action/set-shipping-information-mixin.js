define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote'
], function($, wrapper, quote) {
    'use strict';
    return function (setShippingInformationAction) {
        return wrapper.wrap(setShippingInformationAction, function (originalAction) {
            var shippingAddress = quote.shippingAddress();
            if (shippingAddress['extension_attributes'] === undefined) {
                shippingAddress['extension_attributes'] = {};
            }

            shippingAddress['extension_attributes']['city_id'] = 0;
            if (shippingAddress.customAttributes !== undefined) {
                $.each(shippingAddress.customAttributes, function(index, attribute) {
                    if (attribute.attribute_code !== undefined && attribute.attribute_code === 'city_id') {
                        // in case of new address
                        shippingAddress['extension_attributes']['city_id'] = attribute.value;
                    } else if (index == 'city_id') {
                        // in case of old address
                        shippingAddress['extension_attributes']['city_id'] = attribute;
                    }
                });
            }

            // pass execution to original action
            return originalAction();
        });
    };
});
