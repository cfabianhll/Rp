define([
    'ko',
    'jquery',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/url-builder',
    'Magento_Checkout/js/model/payment-service',
    'Magento_Checkout/js/model/error-processor',
    'Mirasvit_Credit/js/model/payment/messages',
    'mage/storage',
    'Magento_Checkout/js/action/get-totals',
    'mage/translate',
    'Magento_Checkout/js/action/get-payment-information',
    'Magento_Checkout/js/model/payment/method-list'
], function (ko,
             $,
             quote,
             urlBuilder,
             paymentService,
             errorProcessor,
             messageContainer,
             storage,
             getTotalsAction,
             $t,
             getPaymentInformationAction,
             paymentMethodList) {
    'use strict';

    return function (apply, isLoading) {
        var url, message;
        var creditAmount = 0.0;

        if (apply) {
            var value = $('#credit-amount').val();
            if (!isNaN(parseFloat(value))) {
                creditAmount = parseFloat(value).toFixed(4);
            }
            if (creditAmount > window.checkoutConfig.creditConfig.amount) {
                message = $t('You can use only %1').replace('%1', window.checkoutConfig.creditConfig.amount.toFixed(2));
                messageContainer.addErrorMessage({'message': message});
                return;
            }

            url = urlBuilder.createUrl('/carts/mine/credit/apply/:creditAmount', {creditAmount: creditAmount});
            message = $t('Store credit was successfully applied');
        } else {
            url = urlBuilder.createUrl('/carts/mine/credit/cancel/:creditAmount', {creditAmount: creditAmount});
            message = $t('Store credit was successfully canceled');
            $('#credit-amount').val('');
        }
        isLoading(true);

        return storage.post(
            url,
            {},
            false
        ).done(function (response) {
            if (response) {
                var deferred = $.Deferred();
                if (response) {
                    getTotalsAction([], deferred);
                    getPaymentInformationAction(deferred);

                    $.when(deferred).done(function () {
                        paymentService.setPaymentMethods(
                            paymentMethodList()
                        );
                    });
                    messageContainer.addSuccessMessage({'message': message});
                }
            }
        }).fail(function (response) {
            errorProcessor.process(response, messageContainer);
        }).always(function () {
            isLoading(false);
        });
    };
});
