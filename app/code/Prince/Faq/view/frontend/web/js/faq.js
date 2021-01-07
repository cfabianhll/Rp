require ([
    "jquery", 
    "jquery/ui"
], function($){
    $('a[href^="#"]').on('click', function(event) {
        var target = $(this.getAttribute('href'));
        if( target.length ) {
            event.preventDefault();
            $('html, body').stop().animate({
                scrollTop: target.offset().top
            }, 1000);
        }
    });
	jQuery(".faq-group .faq-question.first").trigger("click");
	jQuery("#faqsearch").keypress(function(e){
		if(e.which == 13){
			var searchQuery = jQuery("#faqsearch").val();
			if(!jQuery.trim(searchQuery)){
				jQuery("#faq-validation").show();
			} else {
				jQuery("#faq-validation").hide();
				jQuery("#searchfaq").trigger("click");				
			}
		}
	});	
});
function ajaxgetcontent(element,id,ajaxUrl,search){	
	var searchQuery = '';
	if(search){
		searchQuery = jQuery("#faqsearch").val();
		if(!jQuery.trim(searchQuery)){
			jQuery("#faq-validation").show();
			return false;
		}
        jQuery("#faq-validation").hide();
	}
    if(jQuery(element).length){
        jQuery(element).addClass("active");
        jQuery(element).siblings().removeClass("active");
    } else {
        jQuery(".faq-question").removeClass("active");
    }
	jQuery.ajax({
		url: ajaxUrl,
		showLoader: true,
		data:{cat_id:id,query:searchQuery},
		success: function(result){
			jQuery(".tab-content .faq-content-main").html(result);			
			/*faqAccordian();*/
		}
	});
}
function faqAccordian(){
	require ([
		"jquery", 
		"jquery/ui"
	], function($){
		jQuery(".faq-content .faq-right-contant .faq_list").each(function(){
			jQuery(this).find('h3[data-role="trigger"]').click(function(){
                jQuery(this).toggleClass("active");
				jQuery(this).next(".faq-text-content").slideToggle("slow");
			});
		});
	});
}