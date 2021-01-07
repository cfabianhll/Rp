define([
    'jquery'
], function ($) {
    'use strict';
    
    var $tabs = $('.mst-rma-box__tabs [data-rel]');
    var $tabsContent = $('.mst-rma-box__tabs-content [data-tab]');
    
    $tabs.on('click', function (e) {
        var $target = $(e.currentTarget);
        var tab = $target.data('rel');
        
        $tabs.removeClass("_active");
        $target.addClass("_active");
        
        $tabsContent.removeClass("_active");
        
        $('[data-tab=' + tab + ']').addClass("_active");
    });
    
    var $replyArea = $('[data-role=reply-area]');
    var $replyNote = $('[data-role=reply-note]');
    
    $('[data-role=reply-type]').on('change', function () {
        var type = $('[data-role=reply-type]').val();
        
        $replyArea.removeClass('internal');
        if (type == 'public') {
            $replyNote.html('');
        } else if (type == 'internal') {
            $replyArea.addClass('internal');
            $replyNote.html('Only store managers will see this message');
        }
    });
    $('[data-role=quick_reply]').on('change', function () {
        var id = $(this).val();
        if (id != 0) {
            var template = $('#htmltemplate-' + id).html();
            var val = $replyArea.val();
            if (val != '') {
                val = val + '\n';
            }
            $replyArea.val(val + template);
            $(this).val(0);
            updateSaveBtn();
        }
    });
    
    $replyArea.on('keyup', function () {
        updateSaveBtn();
    });
    
    var updateSaveBtn = function () {
        if ($replyArea.val() == '') {
            $('#update-split-button-update-button,#update-split-button-button').html('Save');
            $('#update-split-button-update-continue-button').html('Save & Continue Edit');
        } else {
            $('#update-split-button-update-button,#update-split-button-button').html('Save & Send Message');
            $('#update-split-button-update-continue-button').html('Send & Continue Edit');
        }
    }
    
    $('form#edit_form').on('invalid-form.validate', function (event, validation) {                     
        var additionalTab = $('li[data-rel=additional]');
        
        validation.errorList.forEach(function (e) {
            var child = $(e['element']);
            var isAdditionalTabHasErrors = false;

            if(child.parent(additionalTab).length) {
                isAdditionalTabHasErrors = true;
            }
            
            if(isAdditionalTabHasErrors) {
                additionalTab.click();
            }
        });
    });
});
