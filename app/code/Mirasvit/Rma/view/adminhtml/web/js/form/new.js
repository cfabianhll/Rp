define(
    [
        'jquery',
        'ko',
        'uiComponent',
        'uiRegistry'
    ],
    function(
        $,
        ko,
        Component,
        registry
    ) {
        'use strict';
        var container = '#rma-create-form .item-container';
        var removeItemButton = '#rma-create-form .item-container button[data-role="item-remove"]';
        var createRmaForm = '#rma-new-form';

        return Component.extend({
            defaults: {
                template: 'Mirasvit_Rma/form/new'
            },

            initialize: function(element, valueAccessor, allBindings) {
                this._super();
                this.itemId = 0;
                var self = this;
                $(document).on('click', removeItemButton, this.removeItem);
                $(document).on('submit', createRmaForm, this.validateItems);
                $(document).ready(function() {
                    self.addItem();
                });
            },
            addItem: function() {
                $(container).append($('#item_returnreasons').html().replace(/%%item_id%%/g, this.itemId));
                this.itemId++;
            },
            removeItem: function() {
                $(this).parents('tr').remove();
            },
            validateItems: function() {
                var result = $('.rma-one-item', container).length > 0;

                if (!result) {
                    alert($.mage.__('Add at least one item to this RMA'));
                }

                return result;
            }
        });
    }
);