<?php 
if(!defined('ABSPATH') && !defined('WP_UNINSTALL_PLUGIN'))
    exit();

	delete_option('casepress-upvote_like-posts');
	delete_option('casepress-upvote_like-comments');
	delete_option('casepress-upvote_dislikes');
	delete_option('casepress-upvote_position-posts');
	delete_option('casepress-upvote_position-comments');
	delete_option('casepress-upvote_buttons-style');
	delete_option('casepress-upvote_modals-style');
	delete_option('casepress-upvote_noauth_msg'); 
		
	$comments = get_comments(array(
		'type' => 'like',
		'count' => false
	));	

	foreach($comments as $comment){  
		$res = wp_delete_comment($comment->comment_ID, true);
	}  
	
	$all_user_ids = get_users( 'fields=ID' );
	foreach ( $all_user_ids as $user_id ) {
		delete_user_meta( $user_id, '_upvote_post' );
	}
	foreach ( $all_user_ids as $user_id ) {
		delete_user_meta( $user_id, '_upvote_comment' );
	}
	
?>