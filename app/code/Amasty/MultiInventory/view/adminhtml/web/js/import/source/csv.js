define(
    [
        'jquery',
        'underscore',
        'mage/translate',
        './abstract-source'
    ],
    function (jQuery, _, $t, Abstract) {
        'use strict';
        var Class = _.clone(Abstract);
        return _.extend(Class,{
            parse: function () {
                return this.getDataFromUrl();
        },

        getDataFromUrl: function () {
            var object = jQuery.Deferred();
            jQuery.ajax({
                type: "GET",
                showLoader: true,
                url: this.fromData.url,
                success: function (resp) {
                    object.resolve(resp);
                    return true;
                }
                });

            return object.promise();
        },

            eachData: function (data) {
                this.line = 0;
                var self = this;
                var rows = data.split(/[\r\n]+/);
                var counter = 0;
                if (rows.length <= 1) {
                    this.error.push($t('File is empty'));
                    return false;
                }

                var status = false;

                rows.forEach(function (ourrow) {
                    self.line++;
                    if (ourrow.length > 0) {
                        var columns = ourrow.split(self.fromData.splitter);
                        if (0 == counter++) {
                            status = self.validHeader(columns);
                        } else {
                            if (status) {
                                if (self.validField(columns)) {
                                    if (self.replaceField(columns)) {
                                        self.data.push(self.replaceField(columns));
                                    }
                                }
                            }
                        }
                    }
                });
                return self.data;
            }
        });
    }
);
