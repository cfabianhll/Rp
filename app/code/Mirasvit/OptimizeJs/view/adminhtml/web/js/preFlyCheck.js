define([
    'jquery'
], function ($) {
    'use strict';
    
    $.widget('mirasvit.preFlyCheck', {
        options: {
            callbackUrl: '',
            elementId:   ''
        },
        
        _create: function () {
            this._on({
                'click': $.proxy(this._run, this)
            });
        },
        
        _run: function () {
            const $element = $('#' + this.options.elementId);
            const originalText = $element.html();
            
            $.ajax({
                url:        this.options.callbackUrl,
                showLoader: true
            }).done(function (response) {
                $element.html(response.message);
                
                setTimeout(function () {
                    $element.html(originalText);
                }.bind(this), 10000)
            });
        }
    });
    
    return $.mirasvit.preFlyCheck;
});
