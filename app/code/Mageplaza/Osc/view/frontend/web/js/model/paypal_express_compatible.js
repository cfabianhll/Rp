/**
 * Mageplaza
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Mageplaza.com license that is
 * available through the world-wide-web at this URL:
 * https://www.mageplaza.com/LICENSE.txt
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade this extension to newer
 * version in the future.
 *
 * @category    Mageplaza
 * @package     Mageplaza_Osc
 * @copyright   Copyright (c) Mageplaza (https://www.mageplaza.com/)
 * @license     https://www.mageplaza.com/LICENSE.txt
 */

define(
    [
        'jquery'
    ],
    function ($) {
        'use strict';

        return {
            togglePlaceOrderButton: function (paymentMethod) {
                if (!window.checkoutConfig.checkVersion) {
                    if (window.checkoutConfig.payment.paypalExpress.isContextCheckout) {
                        var actionsToolbar = $('.osc-place-order-wrapper .actions-toolbar'),
                            actionsToolbarContent = $('.osc-place-order-wrapper .payment-methods .payment-group .payment-method .payment-method-content .actions-toolbar');

                        if (paymentMethod && paymentMethod.method === 'paypal_express') {
                            actionsToolbar.hide();
                            actionsToolbarContent.show();
                        } else {
                            actionsToolbar.show();
                            actionsToolbarContent.hide();
                        }
                    }
                }
            }
        };
    }
);
