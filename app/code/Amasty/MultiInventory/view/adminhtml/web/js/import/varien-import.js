define(
    [
        'jquery',
        'underscore',
        'mage/translate',
        'mage/template',
        'Amasty_MultiInventory/js/import/source/general',
        'jquery/file-uploader',
        'jquery/ui',
        'domReady!'
    ],
    function (jQuery, _, $t, mageTemplate, general) {
        'use strict';

        return {
            ifrElemName: 'import_post_target_frame',
            fileUploader: {
                dataType: 'json',
                url: null,
                autoUpload: true,
                acceptFileTypes: /(\.|\/)(csv|xml)$/i,
                sequentialUploads: true,
                maxFileSize: null,
                formData: {
                    'form_key': window.FORM_KEY
                },
            },
            form: null,
            fileData: null,
            regexp: '^text\\\/(\\w+)$',
            list: null,
            urlSend: null,
            urlClear: null,
            urlDeleteFile: null,
            urlNext: null,
            number: null,
            codes: null,
            products: null,
            exportcsv: null,

            init: function (Object) {
                this.disableSeparated(Object.type, Object.separator);
                this.list = Object.list;
                this.codes = Object.codes;
                this.products = Object.products;
                this.fileType = 0;
                this.urlSend = Object.urlSend;
                this.urlClear = Object.urlClear;
                this.urlNext = Object.urlNext;
                this.urlDeleteFile = Object.urlDeleteFile;
                this.number = FORM_KEY;
                this.fileUploader.url = Object.urlForm;
                this.fileUploader.add = this.onFileAdd.bind(this);
                this.fileUploader.done = this.onFileUploaded.bind(this);
                this.fileUploader.progress = this.onFileProgress.bind(this);
                jQuery('#import_file').attr("accept", '.csv');
                jQuery('#import_file').fileupload(this.fileUploader);
                jQuery("#import_file").hide();
                this.form = jQuery('#edit_form');

            },

            disableSeparated: function (type, separator) {
                if (jQuery("#" + type).val()> 0) {
                    jQuery("#" + separator).attr("disabled", "disabled");
                } else {
                    jQuery("#" + separator).removeAttr("disabled");
                }
            },
            acceptFormat: function (type, separator) {
                var fileType = this.list.fileType[jQuery("#" + type).val()];
                this.fileType = jQuery("#" + type).val();
                jQuery("#" + separator).attr("accept", "." + fileType);
            },

            listColumns: function (form) {
                var identifer = '';
                var self = this;
                jQuery.each(form, function (index, value) {
                    if (value.name == 'identifier') {
                        identifer = self.list.identifier[value.value];
                    }
                });
                if (identifer.length > 0) {
                    return [identifer, 'code', 'qty'];
                }

                return [];
            },

            parse: function () {
                var data = this.fileData;
                var form = this.form.serializeArray();
                var self = this;
                self.clearMessages();
                var separator = this.findInForm(form, 'import_field_separator');

                if (_.isObject(data)) {
                    jQuery.when(self.clear()).then(function () {
                            var type = data.jqXHR.responseJSON.type;
                            var result = [];
                            var regexp = new RegExp(self.regexp, 'ig');
                            result = regexp.exec(type);

                        if (_.isArray(result)) {
                            var source = result[1];
                            if (source == 'xml' && self.fileType == 2) {
                                source = 'excel';
                            }
                            var parser = general.getSource(source);
                            parser.clearAll();
                            parser.init(
                                {
                                    url: data.jqXHR.responseJSON.url,
                                    fields: self.listColumns(form),
                                    splitter: separator,
                                    codes: self.codes,
                                    products: self.products
                                    }
                            );
                            jQuery.when(parser.parse()).then(function (data) {
                                var result = parser.eachData(data);
                                var errors = parser.getErrors();
                                var notices = parser.getNotices();
                                if (errors.length > 0) {
                                    jQuery("#to_grid").hide();
                                    self.addErrors(errors);
                                } else {
                                    if (notices.length > 0) {
                                        self.addNotices(notices);
                                    }
                                    if (result.length > 0) {
                                        self.send(result);
                                    }
                                }
                            });
                        }

                    });
                }
            },
            findInForm: function (form, key) {
                var splitter = '';
                _.each(form, function (value, index) {
                    if (value.name == key) {
                        splitter = value.value;
                    }
                });

                return splitter;
            },
            clear: function () {
                var object = jQuery.Deferred();
                var self = this;
                jQuery.ajax({
                    type: "POST",
                    showLoader: true,
                    url: self.urlClear,
                    data: {number: self.number},
                    success: function (resp) {
                        object.resolve(resp);
                        return true;
                    }
                });

                return object.promise();
            },

            deleteFile: function () {
                var path = '';
                var object = jQuery.Deferred();
                if (_.isObject(this.fileData)) {
                    path = this.fileData.jqXHR.responseJSON.path + this.fileData.jqXHR.responseJSON.file;
                }
                var self = this;
                jQuery.ajax({
                    type: "POST",
                    showLoader: true,
                    url: self.urlDeleteFile,
                    data: {path: path},
                    success: function (resp) {
                        object.resolve(resp);
                        return true;
                    }
                });

                return object.promise();
            },
            send: function (data) {
                jQuery("body").loader("show");
                var form = this.form.serializeArray();
                var requests = [];
                var self = this;
                var reqCount = self.setCountRequests(_.size(data));
                var response = {};
                var counter = 0;
                var newResp = 1;
                var bigCounter = 1;
                var lastCount = _.size(data);
                _.each(data, function (row, key) {
                    if (newResp == 1) {
                        response = {import: {}, form_key: FORM_KEY};
                        newResp = 0;
                    }
                    response.import[key] = self.getObject(row);
                    counter++;
                    if ((counter / bigCounter) == reqCount || counter == lastCount) {
                        requests.push(jQuery.post(self.urlSend, response));
                        bigCounter++;
                        newResp = 1;
                    }
                });
                jQuery.when.apply(null, requests).done(
                    function () {
                        jQuery("body").loader("hide");
                        jQuery("#to_grid").show();
                        self.addSuccess([_.size(data) + $t(' product(s) stock will be updated. Please click "To grid" to finalize import.')]);
                        self.deleteFile();
                    }
                );
            },
            getObject: function (row) {
                var response = {};
                response.warehouse_id = row.warehouse_id;
                response.product_id = row.product_id;
                response.qty = row.qty;
                response.import_number = this.number;
                return response;
            },
            clearMessages: function () {
                jQuery('[data-action="show-notice"]').html("");
                jQuery('[data-action="show-error"]').html("");
                jQuery('[data-action="show-success"]').html("");
            },
            addErrors: function (errors) {
                var tempErrorMessage = document.createElement("div");
                jQuery('[data-action="show-error"]').children(".message").remove();
                tempErrorMessage.className = "message message-error error";
                _.each(errors, function (value) {
                    tempErrorMessage.innerHTML += value + "<br/>";
                });
                jQuery('[data-action="show-error"]').append(tempErrorMessage);
            },

            addNotices: function (notices) {
                var tempErrorMessage = document.createElement("div");
                jQuery('[data-action="show-notice"]').children(".message").remove();
                tempErrorMessage.className = "message message-warning warning";
                tempErrorMessage.innerHTML = '<b>' + _.size(notices) + $t(' product(s) will be ignored') + ':</b><br/><br/>';
                _.each(notices, function (value) {
                    tempErrorMessage.innerHTML += value + "<br/>";
                });
                jQuery('[data-action="show-notice"]').append(tempErrorMessage);
            },

            addSuccess: function (success) {
                var tempErrorMessage = document.createElement("div");
                jQuery('[data-action="show-success"]').children(".message").remove();
                tempErrorMessage.className = "message message-success success";
                _.each(success, function (value) {
                    tempErrorMessage.innerHTML += value + "<br/>";
                });
                jQuery('[data-action="show-success"]').append(tempErrorMessage);
            },

            onFileUploaded: function (e, data) {
                jQuery("body").loader("hide");
                if (data.result && !data.result.hasOwnProperty('errorcode')) {
                    this.fileData = data;
                    this.addSuccess([data.result.message]);
                } else {
                    this.addErrors([data.result.error]);
                }
            },

            onFileAdd: function (e, data) {
                if (_.isObject(this.fileData)) {
                    this.clear();
                    this.deleteFile();
                }
                jQuery("#to_grid").hide();
                jQuery('#out_file').html('');
                var progressTmpl = mageTemplate('#file-template'),
                    fileSize,
                    tmpl;
                jQuery.each(data.files, function (index, file) {
                    fileSize = typeof file.size == "undefined" ?
                        jQuery.mage.__('We could not detect a size.') :
                        byteConvert(file.size);

                    data.fileId = Math.random().toString(36).substr(2, 9);

                    tmpl = progressTmpl({
                        data: {
                            name: file.name,
                            size: fileSize,
                            id: data.fileId
                        }
                    });
                    jQuery(tmpl).data('image', data).appendTo('#out_file');
                });
                jQuery(e.target).fileupload('process', data).done(function () {
                    data.submit();
                });
            },

            onFileProgress: function (e, data) {
                if (jQuery('[data-role="loader"]').length < 1) {
                    jQuery("body").loader("show");
                }
            },

            setCountRequests: function (count) {
                var reqCount = count;
                if (count >= 50 && count < 100) {
                    reqCount = 50;
                } else if (count >= 100) {
                    reqCount = 100;
                }

                return reqCount;
            },

            next: function () {
                location.href = this.urlNext + '?import_number=' + this.number
            }
        };
    }
);
