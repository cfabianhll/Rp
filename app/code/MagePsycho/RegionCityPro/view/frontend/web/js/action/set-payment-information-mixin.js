/*jshint browser:true jquery:true*/
/*global alert*/
define([
    'jquery',
    'mage/utils/wrapper',
    'Magento_Checkout/js/model/quote'
], function ($, wrapper, quote) {
    'use strict';

    return function (setPaymentInformationAction) {

        return wrapper.wrap(setPaymentInformationAction, function (originalAction, messageContainer, paymentData) {
            var billingAddress = quote.billingAddress();
            if (billingAddress['extension_attributes'] === undefined) {
                billingAddress['extension_attributes'] = {};
            }

            billingAddress['extension_attributes']['city_id'] = 0;
            if (billingAddress.customAttributes !== undefined) {
                $.each(billingAddress.customAttributes, function(index, attribute) {
                    if (attribute.attribute_code !== undefined && attribute.attribute_code === 'city_id') {
                        // in case of new address
                        billingAddress['extension_attributes']['city_id'] = attribute.value;
                    } else if (index == 'city_id') {
                        // in case of old address
                        billingAddress['extension_attributes']['city_id'] = attribute;
                    }
                });
            }

            // pass execution to original action
            return originalAction(messageContainer, paymentData);
        });
    };
});
