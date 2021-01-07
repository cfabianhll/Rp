define([
    "jquery",
    'mage/backend/validation'
], function ($) {
    'use strict';
    
    $(document).ready(function () {
        var body = $('body');
        
        body.on('click', '[data-role=rma-submit]', function () {
            if (!$('.ui-rma-order-container > div').length) {
                alert($('#error_message_no_items').html());
                return false;
            }
            
            // offline orders validation
            var offlineOrderContainerSelector = '.ui-offline-order-container';
            if ($(offlineOrderContainerSelector).length) {
                var noItems = false;
                $(offlineOrderContainerSelector).each(function () {
                    if (!$('.ui-offline-items-container > div', this).length) {
                        noItems = true;
                    }
                });
                if (noItems) {
                    alert($('#error_message_no_items').html());
                    return false;
                }
            }
            // store orders validation
            if ($(".ui-rma-items.ui-store-items-container input.ui-rma-item-checkbox").length > 0) {
                if ($(".ui-rma-items.ui-store-items-container input.ui-rma-item-checkbox:checked").length == 0) {
                    alert($('#error_message_no_items').html());
                    return false;
                }
                $(this).hide();
                
                $('.ui-store-items-container .rma-one-item').each(function (i, el) {
                    if ($("input.ui-rma-item-checkbox:checked", this).length == 0) {
                        $("input.input-text", this).val(0);
                    }
                });
            }
            
            return true;
        });
        
        $.validator.addMethod(
            'validate-rma-quantity',
            function (v, element, param) {
                if (/[^\d]/.test(v)) {
                    return false;
                }
                
                v = parseInt(v);
                if (isNaN(v) || v < 1 || v > param) {
                    return false;
                }
                return true;
            },
            $.mage.__('The quantity is incorrect.')
        );
        
        body.on('click', '.ui-rma-item-checkbox', function () {
            var field = $("#qty_requested" + $(this).data('item-id'));
            if ($(this)[0].checked) {
                field.val(field.attr('max'));
                $("#item" + $(this).data('item-id')).show();
            } else {
                field.val(0);
                $("#item" + $(this).data('item-id')).hide();
            }
        });
        
        //body.on('click', 'form#rma-new-form', function(event, validation) {
        $('form#rma-new-form').on('invalid-form.validate', function (event, validation) {
            if (validation.errorList.length) {
                $('[data-role=rma-submit]').show();
            }
        });
    });
});
