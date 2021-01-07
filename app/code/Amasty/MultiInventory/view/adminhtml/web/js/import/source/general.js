define(
    [
        'jquery',
        'underscore',
        'Amasty_MultiInventory/js/import/source/csv',
        'Amasty_MultiInventory/js/import/source/xml',
        'Amasty_MultiInventory/js/import/source/excel'
    ],
    function (jQuery, _, csv, xml, excel) {
        'use strict';

        return {
            getSource: function (text) {
                if (text == 'csv') {
                    return csv;
                }

                if (text == 'xml') {
                    return xml;
                }

                if (text == 'excel') {
                    return excel;
                }

                return null;
            }
        }
    }
);
