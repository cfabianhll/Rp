define([
    'jquery',
    'underscore',
    'domReady!'
], function ($, _) {
    'use strict';
    
    $.widget('mirasvit.optimizeJsTrack', {
        urlsNumber: 0,
        
        options: {
            callbackUrl: '',
            layout:      '',
        },
        
        _create: function () {
            setInterval(function () {
                this.callback();
            }.bind(this), 1000);
        },
        
        callback: function () {
            var urls = _.keys(require.s.contexts._.urlFetched);
            
            if (_.size(urls) > this.urlsNumber) {
                this.urlsNumber = _.size(urls);
                
                $.post(this.options.callbackUrl, {
                    layout: this.options.layout,
                    urls:   urls
                })
            }
        },
    });
    
    return $.mirasvit.optimizeJsTrack;
});