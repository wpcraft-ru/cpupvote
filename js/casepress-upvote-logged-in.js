(function($) {
    $(function() {
		var vote_buttons = $('.casepress-upvote');
		var prefix = 'casepress-upvote-';
		var before_actions = ['vote-up', 'vote-down', 'star'];
		var after_actions = ['voted-up', 'voted-down', 'starred'];
		
		function do_action(type, action, id, elem){
			$.post(ajaxurl, {
				action: action,
				id: id,
				type: type
			}, function (response) {
				if(response){
					response = $.parseJSON(response);
				} else {
					return;
				}
				if (response.success) {
					var replace_action = -1;
					
					replace_action = $.inArray(type, before_actions);
					if(replace_action != -1){
						elem.removeClass(prefix+before_actions[replace_action]);
						if (type != 'star' && type != 'starred'){
							var siblings = elem.siblings('.'+prefix+after_actions[0]);
							if (siblings){
								siblings.removeClass(prefix+after_actions[0]);
								siblings.addClass(prefix+before_actions[0]);
							} 
							var siblings = elem.siblings('.'+prefix+after_actions[1]);
							if (siblings){
								siblings.removeClass(prefix+after_actions[1]);
								siblings.addClass(prefix+before_actions[1]);
							} 
						}
						elem.addClass(prefix+after_actions[replace_action]);
					} else {
						replace_action = $.inArray(type, after_actions);
						elem.removeClass(prefix+after_actions[replace_action]);
						elem.addClass(prefix+before_actions[replace_action]);
					}
					if (type == 'vote-up' || type == 'vote-down' || type == 'voted-up' || type == 'voted-down')
						elem.siblings('span').text(response.count);
				}
			});
		}
		
		vote_buttons.each(function() {
		
			$(this).click(function() {
				
				var type = $(this).attr('class');
				type = type.replace('casepress-upvote','');
				type = type.replace(' ','');
				type = type.replace(prefix, '');
				
				var action = $(this).siblings('input').attr('name');
				var id = $(this).siblings('input').val();
				
				do_action(type, action, id, $(this));

				return false;
			});
		
		});
	
	});
}(jQuery));