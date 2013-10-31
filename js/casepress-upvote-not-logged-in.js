(function($) {
    $(function() {
		var buttons = $('.casepress-upvote');
		
		if (buttons.length>0){
			buttons.each(function(){
				$(this).click(function(){
					$('.casepress-upvote-modal-display').show();
					return false;
				});
			});
			
			$('.casepress-upvote-modal-close-button').click(function(){
				$('.casepress-upvote-modal-display').hide();
			});
			
			$(document).click(function(event){
				if (!$('.casepress-upvote-modal-display').is(':visible')) return false;
				if ($(event.target).closest('.casepress-upvote-modal-box').length) return;
				$('.casepress-upvote-modal-display').hide();
			});
		}
	});
}(jQuery));