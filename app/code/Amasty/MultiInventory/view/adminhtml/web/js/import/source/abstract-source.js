define(
    [
        'mage/translate',
        'underscore',
        'uiClass',
    ],
    function ($t, _) {
        'use strict';

        var Abstract;

        Abstract = _.extend({
            data: [],
            error: [],
            notice: [],
            success:[],
            fromData: null,
            line:0,
            lineText: 'Line: ',

            init: function (Object) {
                this.fromData = Object;

            },

            validHeader: function (row) {
                var count = 0;
                if (this.fromData.fields.length > 0) {
                    count = this.fromData.fields.length;
                    if (this.fromData.fields.length == row.length) {
                        var array = _.intersection(row,this.fromData.fields);
                        if (array.length == count) {
                            return true;
                        } else {
                            this.error.push($t('Invalid file format. Wrong column names. Please recheck the column headings'));
                        }
                    } else {
                        this.error.push($t('Invalid file format. Wrong columns number. Please recheck the columns number'));
                    };
                }

                return false;
            },
            validField: function (row) {
                if (this.fromData.fields.length != row.length) {
                    this.notice.push($t('Record is not valid ') + this.toString(row) + this.writeLine());

                    return false;
                }

                return true;
            },
            replaceField: function (row) {
                var self = this;
                var newArray = {};
                var error = false;
                _.each(row, function (value, index) {
                    switch (self.fromData.fields[index]) {
                        case 'code':
                            var whId = self.beforeValue(self.fromData.codes,value, ' Invalid file data. There is no warehouse with the following Name: ', 1);
                            if (whId) {
                                newArray['warehouse_id'] = whId;
                            } else {
                                error = true;
                            }
                            break;
                        case 'id':
                            var productId = self.beforeValue(self.fromData.products, value, 'Invalid file data. There is no product with the following Id: ', 0);
                            if (productId) {
                                newArray['product_id'] = productId;
                            } else {
                                error = true;
                            }
                            break;
                        case 'sku':
                            var productId = self.beforeValue(self.fromData.products, value, 'Invalid file data. There is no product with the following SKU: ', 1);
                            if (productId) {
                                newArray['product_id'] = productId;
                            } else {
                                error = true;
                            }
                            break;
                        default:
                            newArray[self.fromData.fields[index]] = value;
                    }

                });

                if (!this.checkInArray(newArray) && !error) {
                    return newArray;
                }

                return false;
            },
            checkInArray: function (row) {
                var self = this;
                var result = false;
                _.each(this.data, function (value, index) {
                    if (row['product_id'] == value.product_id && row['warehouse_id'] == value.warehouse_id) {
                        self.notice.push(
                            $t('Invalid file data. There are duplicates, i.e. two or more lines with the same product and warehouse ')
                            + self.toString(row)
                            + self.writeLine()
                        );
                        result = true;
                    }
                });

                return result;
            },
            getData: function () {
                return this.data;
            },

            getErrors: function () {
                return this.error;
            },

            clearErrors: function () {
                this.error = [];
            },

            getNotices: function () {
                return this.notice;
            },

            clearNotices: function () {
                this.notice = [];
            },

            getSuccess: function () {
                return this.success;
            },

            clearSuccess: function () {
                this.success = [];
            },

            clearAll: function () {
                this.clearErrors();
                this.clearNotices();
                this.clearSuccess();
                this.data = [];
            },

            beforeValue: function (collection, value, message, reverse) {
                var list = null;
                if (reverse) {
                    list = _.keys(collection);
                } else {
                    list = collection;
                }
                var id = _.find(list, function (num) {
                    return num == value;
                });
                if (typeof id == 'undefined') {
                    this.notice.push($t(message) + value + this.writeLine());
                } else {
                    if (reverse) {
                        return collection[value];
                    } else {
                        return value;
                    }
                }

                return false;
            },

            writeLine: function () {
                return '<br/><b>' + $t(this.lineText) + this.line + '</b><br/>';
            },

            toString: function (row) {
                var text = '{';
                _.each(row,function (value, index) {
                    if (text.length > 1 ) {
                        text += ",";
                    }
                    text +=index + ':' + value;
                });
                text +='}';
                return text;
            },
        });

        return Abstract;
    }
);
