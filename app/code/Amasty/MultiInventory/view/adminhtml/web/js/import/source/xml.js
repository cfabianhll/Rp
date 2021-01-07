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

            lineText:'Stock Id: ',

            parse: function () {
                return this.getDataFromUrl();
        },
        getDataFromUrl: function () {
            var object = jQuery.Deferred();
            var self = this;
            jQuery.ajax({
                type: "GET",
                dataType: "xml",
                showLoader: true,
                url: this.fromData.url,
                success: function (resp) {
                    object.resolve(resp);
                }, error: function (error, msg, throwError) {
                    self.error.push(throwError.message);
                    object.resolve(error);
                }
                });

            return object.promise();
        },

            eachData: function (data) {
                var newData =[];
                this.line = 0;
                var self = this;
                if (_.size(jQuery(data).find('stock')) == 0) {
                     this.error.push('File is empty');
                }
                jQuery(data).find('stock').each(function () {
                    var parent = this;
                    self.line = jQuery(parent).attr("id");
                    var columns = [];
                    var headers = [];
                    jQuery(this).children().each(function () {
                        headers.push(jQuery(this).context.nodeName);
                        columns.push(jQuery(this).text());
                    });
                    if (self.validHeaderXml(headers)) {
                        if (self.replaceField(columns)) {
                            self.data.push(self.replaceField(columns));
                        }
                    }
                });
                return self.data;
            },
            validHeaderXml: function (row) {
                var count = 0;
                if (this.fromData.fields.length > 0) {
                    count = this.fromData.fields.length;
                    if (this.fromData.fields.length == row.length) {
                        var array = _.intersection(row,this.fromData.fields);
                        if (array.length == count) {
                            return true;
                        } else {
                            this.notice.push($t('Invalid file format. Wrong column names. Please recheck the column headings') + this.writeLine());
                        }
                    } else {
                        this.notice.push($t('Invalid file format. Wrong columns number. Please recheck the columns number')  + this.writeLine());
                    };
                }

                return false;
            },
        });
    }
);
