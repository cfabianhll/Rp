define([
    'jquery',
    'uiComponent',
    'ko',
    'underscore'
], function ($, Component, ko, _, subscriber, track, debug) {
    'use strict';
    return Component.extend({
        defaults: {
            template: 'Mirasvit_OptimizeInsight/pagespeed',
            url:      ''
        },
        
        initialize: function () {
            var self = this;
            
            _.bindAll(this, 'runTest');
            
            this._super();
        },
        
        runTest: function () {
            var url = 'https://www.googleapis.com/pagespeedonline/v5/runPagespeed';
            
            $.ajax({
                url:     url,
                method:  'GET',
                data:    {
                    url:      this.url,
                    strategy: 'desktop',
                    locale:   'en_US'
                },
                success: function (response) {
                    console.log(response);
                }
            })
        }
    })
});