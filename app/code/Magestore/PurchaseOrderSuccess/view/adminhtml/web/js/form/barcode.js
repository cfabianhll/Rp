/*
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'Magento_Ui/js/form/element/abstract',
    'ko',
    'jquery',
    'Magestore_PurchaseOrderSuccess/js/action/grid-action',
    'Magento_Ui/js/lib/spinner',
    'mage/translate'
], function (Element, ko, $, gridAction, loader, $t) {
    'use strict';

    return Element.extend({

        defaults: {
            template: 'Magestore_PurchaseOrderSuccess/form/barcode',
        },

        successMessage: ko.observable(''),
        warningMessage: ko.observable(''),

        handleChange: function (data, event) {
            var keyword = event.target.value;
            var params = {
                isAjax: 'true',
                barcode: keyword
            };
            loader.get('os_purchase_order_form.os_purchase_order_form').show();
            var self = this;
            $.ajax({
                method: "POST",
                url: self.save_url,
                data: params
            }).done(function (transport) {
                if (transport.result) {
                    var successMessage = $t('Barcode %s has been added to the list.').replace('%s', keyword);
                    self.setSuccessMessage(successMessage);
                    gridAction('','', 'purchaseorder_list_itemJsObject');
                    $('#purchase_sumary_total_block').replaceWith(transport);
                } else {
                    var warningMessage = $t('No product found.');
                    self.setWarningMessage(warningMessage);
                }
                loader.get('os_purchase_order_form.os_purchase_order_form').hide();
                self.focused(false);
                self.focused(true);
                $('#inventorysuccess-barcode-input').val('');
            }).fail(function () {
                loader.get('os_purchase_order_form.os_purchase_order_form').hide();
            });
        },

        /**
         * Set success message
         *
         * @param message
         */
        setSuccessMessage(message) {
            this.successMessage(message);
            this.warningMessage(false);
            if (this.messageTimeout) {
                clearTimeout(this.messageTimeout);
            }
            this.messageTimeout = setTimeout(() => this.resetMessages(), 5*1000);
        },

        /**
         * Set warning message
         *
         * @param message
         */
        setWarningMessage(message) {
            this.warningMessage(message);
            this.successMessage(false);
            if (this.messageTimeout) {
                clearTimeout(this.messageTimeout);
            }
            this.messageTimeout = setTimeout(() => this.resetMessages(), 5*1000);
        },


        /**
         * reset messages
         */
        resetMessages() {
            this.successMessage(false);
            this.warningMessage(false);
        }
    });
});
