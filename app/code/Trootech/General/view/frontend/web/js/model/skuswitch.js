define([
    'jquery',
    'mage/utils/wrapper'
], function ($, wrapper) {
 'use strict';
 
    return function(targetModule){
        var updatePrice = targetModule.prototype._UpdatePrice;
        targetModule.prototype.configurableSku = $('div.custom-image .itemm img').html();
        var updatePriceWrapper = wrapper.wrap(updatePrice, function(original){
            var allSelected = true;
            for(var i = 0; i<this.options.jsonConfig.attributes.length;i++){
                if (!$('div.product-info-main .product-options-wrapper .swatch-attribute.' + this.options.jsonConfig.attributes[i].code).attr('option-selected')){
                 allSelected = false;
                }
            }
            var simpleImage = this.configurableSku;
            if (allSelected){
                var products = this._CalcProducts();
                simpleImage = this.options.jsonConfig.img[products.slice().shift()];
            }
            if(simpleImage != '') {
                        $('div.custom-image .itemm img').each(function() {
                                    var setImageSource = $(this).attr('src', simpleImage);      
                            });
                          return original();
        }
        });
        targetModule.prototype._UpdatePrice = updatePriceWrapper;
        return targetModule;
 };
});

