<?php 
if(!defined('ABSPATH') && !defined('WP_UNINSTALL_PLUGIN'))
    exit();

	delete_option('upvote_posts');
	delete_option('upvote_comments');
	delete_option('upvote_dislikes');
	delete_option('upvote_posts_like_accepted');
	delete_option('upvote_no_auth');
		
	$comments = get_comments(array(
		'type' => 'like',
		'count' => false
	));	

	foreach($comments as $comment){  
		$res = wp_delete_comment($comment->comment_ID, true);
	}  

?>