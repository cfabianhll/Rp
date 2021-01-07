define(
    [
        'jquery',
        'underscore',
        'mage/translate',
        './xml'
    ],
    function (jQuery, _, $t, Abstract) {
        'use strict';

        var Class = _.clone(Abstract);
        return _.extend(Class,{
            eachData: function (data) {
                var newData =[];
                this.line = 0;
                var self = this;
                if (_.size(jQuery(data).find('Worksheet')) == 0) {
                     this.error.push('File is empty');
                }
                var dataExcel = jQuery(data).find('Worksheet').find('Table').find('Row');
                var counter = 0;
                jQuery(dataExcel).each(function (key, row) {
                    var columns = [];
                    var headers = [];
                    var elements = jQuery(row).find('Cell').find('Data');
                    jQuery(elements).each(function () {
                        var parent = this;
                        if (counter == 0) {
                            headers.push(jQuery(parent)[0].innerHTML);
                        } else {
                            columns.push(jQuery(parent)[0].innerHTML);
                        }
                    });
                    if (counter == 0) {
                        self.validHeaderXml(headers);
                    } else {
                        if (self.replaceField(columns)) {
                            self.data.push(self.replaceField(columns));
                        }
                    }
                    counter++;
                });

                return self.data;
            }
        });
    }
);
