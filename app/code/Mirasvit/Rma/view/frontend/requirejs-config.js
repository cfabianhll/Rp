var config = {

    // required for fix issue arrays
    shim: {
        'Mirasvit_Rma/js/create-rma': {
            deps: ['prototype']
        }
    },
    map:  {
        '*': {
            jqueryMultiFile: 'Mirasvit_Rma/js/multifile/jquery.MultiFile'
        }
    }
};