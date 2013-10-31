<?php
/*
Plugin Name: CasePress UpVote
Plugin URI: http://casepress.org/
Description: CasePress UpVote - posts and comments rating system.
Version: 1.0.0
Author: Kochnev Dmitry
Author URI: http://casepress.org/
*/

require_once dirname(__FILE__) . '/settings.php';

class CasePressUpVote {

	function __construct(){
	
	
		add_action('wp_print_styles', array($this, 'print_styles'));
		add_action('wp_print_scripts', array($this, 'print_scripts'));
		add_action('plugins_loaded', array($this, 'locales'));
		
		add_filter('the_content', array($this, 'posts_filter'));
		add_filter('comment_text', array($this, 'comments_filter'));

		add_action('wp_ajax_upvote_post', array($this, 'upvote_post'));
		add_action('wp_ajax_upvote_comment', array($this, 'upvote_comment'));
		
		add_action('wp_footer', array($this, 'modal_render'), 100);

		add_filter('comments_array', array($this, 'list_comments'));
		
		add_shortcode('upvote', array($this, 'upvote_shortcode'));
		add_shortcode('upvote_favs', array($this, 'upvote_favs_shortcode'));

		add_action('delete_post', array($this, 'delete_post'));
		add_action('delete_comment', array($this, 'delete_comment'));

		add_filter('widget_text', 'do_shortcode');
		
		
	}

	function locales(){
		load_plugin_textdomain('upvote', false, dirname( plugin_basename( __FILE__ ) ) . '/lang/' );
	}
	
	function CasePressUpVote(){
		$this->__construct();
		
	}

	function print_styles(){
		$buttons_style = get_option('casepress-upvote_buttons-style');
		wp_enqueue_style('CasePressUpVoteButtonsStyles', plugins_url( 'styles/buttons/'.$buttons_style.'/'.$buttons_style.'.css' , __FILE__ )); 
		if(!is_user_logged_in()){
			$modals_style = get_option('casepress-upvote_modals-style');
			wp_enqueue_style('CasePressUpVoteModalWindowStyles', plugins_url( 'styles/modal/'.$modals_style.'/'.$modals_style.'.css' , __FILE__ )); 
		}
	}

	function print_scripts(){
		if(is_user_logged_in()){
			wp_enqueue_script('CasePressUpVoteLoggedInScript', plugins_url( 'js/casepress-upvote-logged-in.js', __FILE__ ), array('jquery'));
		} else {
			wp_enqueue_script('CasePressUpVoteNotLoggedInScript', plugins_url( 'js/casepress-upvote-not-logged-in.js', __FILE__ ), array('jquery'));
		}
	}

	function generate_buttons($type,$content,$custom_id = 0){
		$votebox = $html = '';
		$count = $now_value = 0;
		
		if ((get_option('casepress-upvote_like-posts') == 0 && ($type == 'post')) || (get_option('casepress-upvote_like-comments') == 0 && ($type == 'comment'))) return $html;
		
		$votebox .= '<div class="casepress-vote-box">';
			if ($type == 'post'){
				if(is_numeric($custom_id) && $custom_id>0){
					$post_id = $custom_id;
				} else {
					$post_id = get_the_ID();
				}
				$votebox .= '<input type="hidden" name="upvote_post" value="'.$post_id.'" />';
				if (is_user_logged_in()){
					$comments = get_comments(array(
									'user_id' => get_current_user_id(),
									'post_id' => $post_id,
									'type' => 'like'
								));
					if(!empty($comments)){
						$now_value = $comments[0]->comment_content;		
					}
				}
				$votebox .= '<a href="#" class="casepress-upvote casepress-upvote-vote';  
				if (is_user_logged_in()){
					if($now_value == 1) $votebox .= 'd';
				}
				$votebox .= '-up" title="'.__('Up Vote Post', 'upvote').'">'.__('Up Vote Post', 'upvote').'</a>';
				$comments = get_comments(array(
										'post_id' => $post_id,
										'type' => 'like'
									));
				foreach($comments as $comment){
					$count += $comment->comment_content;
				}
				$votebox .= '<span class="casepress-upvote-vote-count">'.$count.'</span>';
				if (get_option('casepress-upvote_dislikes') == 1){
					$votebox .= '<a href="#" class="casepress-upvote casepress-upvote-vote';
					if (is_user_logged_in()){
						if($now_value == -1) $votebox .= 'd';
					}
					$votebox .= '-down" title="'.__('Down Vote Post', 'upvote').'">'.__('Down Vote Post', 'upvote').'</a>';
				}
				$votebox .= '<a href="#" class="casepress-upvote casepress-upvote-star'; 
					if (is_user_logged_in()){
						if (in_array($post_id, get_user_meta(get_current_user_id(), '_upvote_post'))) $votebox .= 'red';
					}
				$votebox .= '" title="'.__('Favorite Post', 'upvote').'">'.__('Favorite Post', 'upvote').'</a>';
			} else {
				$votebox .= '<input type="hidden" name="upvote_comment" value="'.get_comment_ID().'" />';
				if (is_user_logged_in()){
					$comments = get_comments(array(
									'user_id' => get_current_user_id(),
									'parent' => get_comment_ID(),
									'type' => 'like'
								));
						
					if(!empty($comments)){
						$now_value = $comments[0]->comment_content;		
					}
				}
				$votebox .= '<a href="#" class="casepress-upvote casepress-upvote-vote';
				if (is_user_logged_in()){
					if($now_value == 1) $votebox .= 'd';
				}
				$votebox .= '-up" title="'.__('Up Vote Comment', 'upvote').'">'.__('Up Vote Comment', 'upvote').'</a>';
				$comments = get_comments(array(
										'parent' => get_comment_ID(),
										'type' => 'like'
									));
				foreach($comments as $comment){
					$count += $comment->comment_content;
				}
				$votebox .= '<span class="casepress-upvote-vote-count">'.$count.'</span>';
				if (get_option('casepress-upvote_dislikes') == 1){
					$votebox .= '<a href="#" class="casepress-upvote casepress-upvote-vote';
					if (is_user_logged_in()){
						if($now_value == -1) $votebox .= 'd';
					}
					$votebox .= '-down" title="'.__('Down Vote comment', 'upvote').'">'.__('Down Vote comment', 'upvote').'</a>';
				}
				$votebox .= '<a href="#" class="casepress-upvote casepress-upvote-star'; 
				if (is_user_logged_in()){
					if (in_array(get_comment_ID(), get_user_meta(get_current_user_id(), '_upvote_comment'))) $votebox .= 'red';
				}
				$votebox .= '" title="'.__('Favorite Comment', 'upvote').'">'.__('Favorite Comment', 'upvote').'</a>';
			}
		$votebox .= '</div>';
		
		if($type == 'post'){
			$position = get_option('casepress-upvote_position-posts');	
			
		} else {
			$position = get_option('casepress-upvote_position-comments');
		}
		
		switch($position){
			case 0:
				$html .= '<div class="casepress-top">';
					$html .= $votebox;
				$html .= '</div>';
				$html .= $content;
			break;
			case 1:
				$html .= '<table class="casepress-left">';
					$html .= '<tr>';
						$html .= '<td class="casepress-votecell">';
							$html .= $votebox;
						$html .= '</td>';
						$html .= '<td class="casepress-contentcell">';
							$html .= $content;
						$html .= '</td>';
					$html .= '</tr>';
				$html .= '</table>';
			break;
			case 2:
				$html .= '<table class="casepress-right">';
					$html .= '<tr>';
						$html .= '<td class="casepress-contentcell">';
							$html .= $content;
						$html .= '</td>';
						$html .= '<td class="casepress-votecell">';
							$html .= $votebox;
						$html .= '</td>';
					$html .= '</tr>';
				$html .= '</table>';
			break;
			case 3:
				$html .= $content;
				$html .= '<div class="casepress-bottom">';
					$html .= $votebox;
				$html .= '</div>';
			break;
		} 
		
		return $html;
	}

	function modal_render(){
		if(!is_user_logged_in()){
			$html = '<div class="casepress-upvote-modal-display">';
				$html .= '<div class="casepress-upvote-modal">';
					$html .= '<div class="casepress-upvote-modal-wrapper">';
						$html .=  '<div class="casepress-upvote-modal-box">';
							$html .= '<div class="casepress-upvote-modal-close">';
								$html .= '<a href="#" class="casepress-upvote-modal-close-button">'.__('Close', 'upvote').'</a>';
							$html .= '</div>';
							$html .= '<div class="casepress-upvote-modal-content">'.get_option('casepress-upvote_noauth-msg').'</div>';
						$html .= '</div>';
					$html .= '</div>';
				$html .= '</div>';
			$html .= '</div>';	
			echo $html;
		}
	}
	
	function posts_filter($content){
		return $this->generate_buttons('post',$content);
	}

	function comments_filter($content){
		return $this->generate_buttons('comment',$content);
	}
	
	function upvote_post(){
		$count = 0;
		
		if (!is_user_logged_in()) die();
		
		if (isset($_POST['id']) && is_numeric($_POST['id'])) {
			$check = get_post($_POST['id']);
			if (!$check) die();	
		} else {
			die();
		}
		
		if(!isset($_POST['type'])) die();
		
		$comments = get_comments(array(
								'user_id' => get_current_user_id(),
								'post_id' => $_POST['id'],
								'type' => 'like'
							));
		
		if(!empty($comments)){
			$now_value = $comments[0]->comment_content;		
		}
		
		switch($_POST['type']){
			case 'vote-up':
				if (isset($now_value) && $now_value == 1) die();
				if (isset($now_value) && ($now_value == 0 || $now_value == -1)) {
					$query = wp_update_comment(array(
									'comment_ID' => $comments[0]->comment_ID,	
									'comment_content' => 1
								));
				} else {
					$query = wp_insert_comment(array(  
									'comment_post_ID' => $_POST['id'],  
									'comment_author' => '',  
									'comment_author_email' => '',  
									'comment_author_url' => '',  
									'comment_content' => 1,  										
									'comment_type' => 'like',  
									'comment_parent' => 0,  
									'user_id' => get_current_user_id(),  
									'comment_author_IP' => '',  
									'comment_agent' => '',  
									'comment_date' => current_time('mysql'),  
									'comment_approved' => 1 
								));	
				}
			break;
			case 'voted-up':
				if (isset($now_value) && ($now_value == 1)) {
					$query = wp_update_comment(array(
									'comment_ID' => $comments[0]->comment_ID,	
									'comment_content' => 0
								));
				} else {
					die();
				}
			break;
			case 'vote-down':
				if (get_option('upvote_dislikes')) die();
				if (isset($now_value) && $now_value == -1) die();
				if (isset($now_value) && ($now_value == 0 || $now_value == 1)) {
					$query = wp_update_comment(array(
									'comment_ID' => $comments[0]->comment_ID,	
									'comment_content' => -1
								));
				} else {
					$query = wp_insert_comment(array(  
									'comment_post_ID' => $_POST['id'],  
									'comment_author' => '',  
									'comment_author_email' => '',  
									'comment_author_url' => '',  
									'comment_content' => -1,  										
									'comment_type' => 'like',  
									'comment_parent' => 0,  
									'user_id' => get_current_user_id() ,  
									'comment_author_IP' => '',  
									'comment_agent' => '',  
									'comment_date' => current_time('mysql'),  
									'comment_approved' => 1 
								));	
				}
			break;
			case 'voted-down':
				if (isset($now_value) && ($now_value == -1)) {
					$query = wp_update_comment(array(
									'comment_ID' => $comments[0]->comment_ID,	
									'comment_content' => 0		
								));
				} else {
					die();
				}
			break;
			case 'star':
				if (in_array($_POST['id'], get_user_meta(get_current_user_id(), '_upvote_post'))) die();
				$query = add_user_meta(get_current_user_id(), '_upvote_post', $_POST['id']);
			break;
			case 'starred':
				if (!in_array($_POST['id'], get_user_meta(get_current_user_id(), '_upvote_post'))) die();
				$query = delete_user_meta(get_current_user_id(), '_upvote_post', $_POST['id']);
			break;
		}

		if($query){
			$response = array('success' => true); 
			if($_POST['type'] != 'star' || $_POST['type'] != 'starred'){
				$comments = get_comments(array(
										'post_id' => $_POST['id'],
										'type' => 'like'
									));
				foreach($comments as $comment){
					$count += $comment->comment_content;
				}
				$response['count'] = $count;
			}
		}else{
			$response = array('success' => false);
		}
		
		$response = json_encode($response);
		die($response);
	}
	
	function upvote_comment(){
		$count = 0;
		
		if (!is_user_logged_in()) die();

		if (isset($_POST['id']) && is_numeric($_POST['id'])) {
			$check = get_comments(array(
									'ID' => $_POST['id'],
									'count' => true
								));	
			if (empty($check)) die();	
		} else {
			die();
		}
		
		if(!isset($_POST['type'])) die();
		
		$comments = get_comments(array(
								'user_id' => get_current_user_id(),
								'parent' => $_POST['id'],
								'type' => 'like'
							));
					
		if(!empty($comments)){
			$now_value = $comments[0]->comment_content;		
		}
		
		switch($_POST['type']){
			case 'vote-up':
				if (isset($now_value) && $now_value == 1) die();
				if (isset($now_value) && ($now_value == 0 || $now_value == -1)) {
					$query = wp_update_comment(array(
									'comment_ID' => $comments[0]->comment_ID,	
									'comment_content' => 1
								));
				} else {
					$query = wp_insert_comment(array(  
									'comment_post_ID' => 0,  
									'comment_author' => '',  
									'comment_author_email' => '',  
									'comment_author_url' => '',  
									'comment_content' => 1,  										
									'comment_type' => 'like',  
									'comment_parent' => $_POST['id'],  
									'user_id' => get_current_user_id() ,  
									'comment_author_IP' => '',  
									'comment_agent' => '',  
									'comment_date' => current_time('mysql'),  
									'comment_approved' => 1 
								));	
				}
			break;
			case 'voted-up':
				if (isset($now_value) && $now_value == 1) {
					$query = wp_update_comment(array(
									'comment_ID' => $comments[0]->comment_ID,	
									'comment_content' => 0
								));
				} else {
					die();
				}
			break;
			case 'vote-down':
				if (get_option('upvote_dislikes')) die();
				if (isset($now_value) && $now_value == -1) die();
				if (isset($now_value) && ($now_value == 0 || $now_value == 1)) {
					$query = wp_update_comment(array(
									'comment_ID' => $comments[0]->comment_ID,	
									'comment_content' => -1
								));
				} else {
					$query = wp_insert_comment(array(  
									'comment_post_ID' => 0,  
									'comment_author' => '',  
									'comment_author_email' => '',  
									'comment_author_url' => '',  
									'comment_content' => -1,  										
									'comment_type' => 'like',  
									'comment_parent' => $_POST['id'],  
									'user_id' => get_current_user_id() ,  
									'comment_author_IP' => '',  
									'comment_agent' => '',  
									'comment_date' => current_time('mysql'),   
									'comment_approved' => 1 
								));	
				}
			break;
			case 'voted-down':
				if (isset($now_value) && $now_value == -1) {
					$query = wp_update_comment(array(
									'comment_ID' => $comments[0]->comment_ID,	
									'comment_content' => 0
								));
				} else {
					die();
				}
			break;
			case 'star':
				if (in_array($_POST['id'], get_user_meta(get_current_user_id(), '_upvote_comment'))) die();
				$query = add_user_meta(get_current_user_id(), '_upvote_comment', $_POST['id']);
			break;
			case 'starred':
				if (!in_array($_POST['id'], get_user_meta(get_current_user_id(), '_upvote_comment'))) die();
				$query = delete_user_meta(get_current_user_id(), '_upvote_comment', $_POST['id']);
			break;
		}
		
		if($query){
			$response = array('success' => true);
			if($_POST['type'] != 'star' || $_POST['type'] != 'starred'){
				$comments = get_comments(array(
										'parent' => $_POST['id'],
										'type' => 'like'
									));
				foreach($comments as $comment){
					$count += $comment->comment_content;
				}
				$response['count'] = $count;
			}
		}else{
			$response = array('success' => false);
		}
		
		$response = json_encode($response);
		die($response);
	}
	
		function upvote_favs_shortcode($atts)
		{
			extract(shortcode_atts(array(
				"user" => get_current_user_id(),
				"type" => 'all',
			), $atts));
			
			$return_post = $return_comments = $return = '';
			
			if ($user != '' || $user != 0) {
				$fav_posts = get_user_meta($user, '_upvote_post');
				foreach ($fav_posts as $value){
					$return_post .= $value.', ';
				}
				$return_post = substr($return_post, 0, -2);
				
				$fav_comments = get_user_meta($user, '_upvote_comment');
				foreach ($fav_comments as $value){
					$return_comments .= $value.', ';
				}
				$return_comments = substr($return_comments, 0, -2);
				
				if (($type == 'posts' || $type == 'all') && $return_post != ''){
					$return = __('Posts: ', 'upvote').$return_post;
				}
				
				if (($type == 'comments' || $type == 'all') && $return_comments != ''){
					$return_comments = __('Comments: ', 'upvote').$return_comments; 
					if ($type == 'all'){ 
						$return .= ' <br />'.$return_comments; 
					}
					else {
						return $return_comments;
					}
				}
			}
			return $return;
		}
	
	function upvote_shortcode(){
		extract(shortcode_atts(array(
			"post" => '',
			"comment" => ''
		), $atts));
		
		$id = 0;
		
		if ($post <= 0 || $post == '') {
			$id = get_the_ID();
		} else {
			$id = $post;
		}
		
		if ($id != 0)
			return $this->generate_buttons('post', '', $id);
			
		if ($comment <= 0 || $comment == '') {
			$id = get_comment_ID();
		} else {
			$id = $comment;
		}
		
		if ($id != 0)
			return $this->generate_buttons('comment', '', $id);
		else 
			return false;
	}
	
	function list_comments($comments='') {
		foreach($comments as $key => $comment){  
			if ($comment->{'comment_type'} == 'like') {
				unset($comments[$key]);
			}
		}  
		return $comments;
	}
	
	function delete_post($id){
		$comments = get_comments(array(
								'post_ID' => $id,
								'type' => 'like',
								'count' => false
							));
		
		foreach($comments as $comment){  
			$res = wp_delete_comment($comment->comment_ID, true);
		}  			
	}
		
	function delete_comment($id){
		$comments = get_comments(array(
								'parent' => $id,
								'type' => 'like',
								'count' => false
							));	
		
		foreach($comments as $comment){  
			$res = wp_delete_comment($comment->comment_ID, true);
		}  
	}
}

new CasePressUpVote();
?>