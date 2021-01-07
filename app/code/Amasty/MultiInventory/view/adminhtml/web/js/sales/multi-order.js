define(
    [
        'jquery',
        'underscore',
        'mage/translate',
        'jquery/ui'
    ],
    function (jQuery, _, $t) {

        return {
            globals: {
                data: null,
                classTable: null,
                regexp:null,
                findRegexp: null,
                findRegexpOptions: null,
                attrRegexp: null,
                numberRegexp:0,
                addSelect: false
            },
            table: null,

            findTable: function () {
                return jQuery(this.globals.classTable);
            },
            addHead: function (id, text) {
                jQuery(this.table).find('thead tr').append("<th class='col-" + id + "'>" + $t(text) + "</th>");
            },
            addTd: function (tr, id, text) {
                jQuery(this.table).children('tbody:eq(' + tr + ')').children("tr").append("<td class='col-" + id + "'>" + text + "</td>");
            },
            addBundleTd: function (option, text, id) {
                if (typeof option !== "undefined") {
                    jQuery(option.parentElement.parentElement).append('<td class="col-' + id + '">' + text + '</td>');
                }
            },
            getItems: function () {
                var items = [];
                var self = this;
                _.each(self.globals.data, function (value, index) {
                    items.push(index);
                });

                return items;
            },
            addData: function () {
                console.log(this.globals.data);
                if (_.size(this.globals.data) > 0) {
                    this.table = this.findTable();
                    var items = this.getItems();
                    var self = this;
                    this.addHead('warehouse', 'Ship from Warehouse');
                    this.addHead('room', 'Room & Shelf');
                    _.each(this.globals.data, function (value, index) {
                        var parentItem = index;
                        var tr = _.indexOf(items, parentItem);
                        if (!value.list) {
                            self.addBundle(tr, value);
                        } else {
                            self.addSimple(tr, parentItem, value, self);
                        }
                    });
                }
            },
            addSimple: function (tr, index, value, self) {
                if (!isNaN(tr) && tr != -1) {
                    if (self.globals.addSelect) {
                        var select = '<select class="admin__control-select warehouse-select" data-index="' + index + '" name="shipment[warehouse][' + index + ']">';
                        _.each(value.list, function (option) {
                            var selected = '';
                            if (option.warehouse_id == value.data.warehouse_id) {
                                selected = 'selected="selected"';
                            }
                            select += '<option value="' + option.warehouse_id + '" ' + selected + '>' + option.title + '</option>';
                        });
                        select += '</select>';
                        self.addTd(tr, "warehouse", select);
                    } else {
                        var text = '';
                        _.each(value.list, function (option) {
                            if (option.warehouse_id == value.data.warehouse_id) {
                                text = option.title;
                            }
                        });
                        self.addTd(tr, "warehouse", text);
                    }
                    self.addTd(tr, "room", value.data.room_shelf);
                }
            },
            addBundle: function (tr, value) {
                if (tr === -1) {
                    tr = 0;
                }
                var optionIds = jQuery(this.table).children('tbody:eq(' + tr + ')').find(this.globals.findRegexpOptions),
                    index = 0,
                    self = this;
                _.each(value, function (option, itemId) {
                    var roomShelf = option.data.room_shelf,
                        optionWh = option.data.warehouse_id;
                    if (self.globals.addSelect) {
                        var select = '<select class="admin__control-select warehouse-select" data-index="' + itemId + '" name="shipment[warehouse][' + itemId + ']">';
                        _.each(option.list, function (item) {
                            var selected = '';
                            if (optionWh === item.warehouse_id) {
                                selected = 'selected="selected"';
                            }
                            select += '<option value="' + item.warehouse_id + '" ' + selected + '>' + item.title + '</option>'
                        });
                        select += '</select>';
                        self.addBundleTd(optionIds[index], select, 'warehouse');
                    } else {
                        _.each(option.list, function (item) {
                            if (optionWh === item.warehouse_id) {
                                self.addBundleTd(optionIds[index], item.title, 'warehouse');
                            }
                        });
                    }
                    self.addBundleTd(optionIds[index], roomShelf, 'room');

                    index++;
                });

            }
        };

    }
);