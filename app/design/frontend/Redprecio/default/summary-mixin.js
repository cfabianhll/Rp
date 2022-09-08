define([
    'jquery',
    'jquery/ui',
    'ko',
    'Magento_Checkout/js/model/step-navigator'
], function($, ui, ko, stepNavigator){
    'use strict';
    var mixin = {
        isVisibleShippingButton: function () {
            return !stepNavigator.getActiveItemIndex();
        },

        isVisiblePaymentButton: function () {
            return stepNavigator.getActiveItemIndex();
        },
        isVisible: function () {
            return stepNavigator.isProcessed('shipping');
        },

        initialize: function () {
            var self = this;

            $(function() {
                $('body').on('click', '#continue-to-payment-trigger', function () {
                    $('#shipping-method-buttons-container').find('.action.continue.primary').trigger('click');
                });
                // $('body').on('click', '#place-order-trigger', function () {
                //     $('.payment-method._active').find('.action.primary.checkout').trigger('click');
                // });
                $(function() {
                    $('body').on("click", '#place-order-trigger', function () {
                        $(".payment-method.clicked").find('.action.primary.checkout').trigger( 'click' );
                    });
                });
                
            });

            this._super();
        }
    };

    return function (target) {
        return target.extend(mixin);
    };
});