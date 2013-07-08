<?php
/*
Plugin Name: CasePress UpVote
Plugin URI: http://casepress.org/
Description: CasePress UpVote - posts and comments rating system.
Version: 0.1
Author: Rasko
Author URI: http://casepress.org/
*/

// Stop direct call
if (!empty($_SERVER['SCRIPT_FILENAME']) && basename(__file__) == basename($_SERVER['SCRIPT_FILENAME']))
	die ('Please do not load this page directly. Thanks!');

if (!class_exists('CpUpvote')) {

	require_once dirname(__FILE__) . '/settings.php';
	
	class CpUpvote {
		// Конструктор объекта
		function CpUpvote()
		{
			$this->pluginPath = dirname(__FILE__);
			$this->pluginUrl = WP_PLUGIN_URL . '/cpupvote';
			
			load_plugin_textdomain('upvote', false, basename($this->pluginPath).'/languages' );
			
			$this->actions();
			
			new UpVoteSettings();
		}
		function actions() 
		{
			add_action('wp_head', array($this, 'add_ajax_library'));
			add_action('wp_print_scripts', array($this, 'register_plugin_scripts'));
			add_action('wp_print_styles', array($this, 'register_plugin_styles'));
			
			add_filter('widget_text', 'do_shortcode');
			
			add_shortcode('upvote', array($this, 'upvote_shortcode'));
			add_shortcode('upvote_favs', array($this, 'upvote_favs_shortcode'));
			
			add_action('delete_post', array($this, 'delete_post'));
			add_action('delete_comment', array($this, 'delete_comment'));
			
			add_filter('comments_array', array($this, 'list_comments'));
			
			if (get_option('upvote_posts'))
			{
				add_filter('the_content', array($this, 'upvote_post_filter'));
			}
			if (get_option('upvote_comments'))
			{
				add_filter('comment_text', array($this, 'upvote_comment_filter'));
			}
			add_action('wp_ajax_upvote_post', array($this, 'upvote_post'));
			add_action('wp_ajax_upvote_comment', array($this, 'upvote_comment'));
		}
		function upvote_shortcode($atts)
		{	
			extract(shortcode_atts(array(
				"post" => ''
			), $atts));
			
			$count = $check_rate = $check_star = $logged_in = 0;
			$content = '';
			
			if ($post <= 0 || $post == '') {$id = get_the_ID();}
			else {$id = $post;}
			//$id = get_the_ID();
			
			$type = get_post_type($id);
			$accepted = preg_replace('/\s/', '', get_option('upvote_posts_like_accepted'));
			if ($accepted)	{
				$accepted = explode(",", $accepted);
				if(!in_array($type, $accepted)) return $content;
			}
			if (is_user_logged_in()) {
				$logged_in = 1;

				$comments = get_comments(array(
					'post_id' => $id,
					'type' => 'like',
					'user_id' => get_current_user_id()
				));
				$check_rate = $comments[0]->comment_content;
				$check_star = in_array($id, get_user_meta(get_current_user_id(), '_upvote_post'));
			}
			$comments = get_comments(array(
					'post_id' => $id,
					'type' => 'like'
				));
			foreach($comments as $comment){
				$count += $comment->comment_content;
			}
			return $this->generate_button($logged_in, 'post', $content, $check_rate, $check_star, $id, $count);
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
				
				if (($type == 'post' || $type == 'all') && $return_post != ''){
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
		function list_comments($comments='')
		{
			foreach($comments as $key => $comment){  
				if ($comment->{'comment_type'} == 'like') 
					{
						unset($comments[$key]);
					}
			}  
			return $comments;
		}
		function add_ajax_library() 
		{
			$html = '<script type="text/javascript">';
			$html .= 'var ajaxurl = "' . admin_url('admin-ajax.php') . '";';
		    $html .= '</script>';

			echo $html;
		}
		function register_plugin_scripts()
		{
			wp_enqueue_script('upvote', $this->pluginUrl . '/js/jquery.upvote.js' , array('jquery'));
			wp_enqueue_script('arcticmodal', $this->pluginUrl . '/js/jquery.arcticmodal-0.3.min.js' , array('jquery'));
		}
		function register_plugin_styles()
		{
			wp_enqueue_style('upvote', $this->pluginUrl . '/css/style.css');
		}
		function upvote_post_filter($content='') 
		{
			$count = $check_rate = $check_star = $logged_in = 0;
			$id = get_the_ID();
			$type = get_post_type($id);
			$accepted = preg_replace('/\s/', '', get_option('upvote_posts_like_accepted'));
			if ($accepted)	{
				$accepted = explode(",", $accepted);
				if(!in_array($type, $accepted)) return $content;
			}						
			if (is_user_logged_in()) {
				$logged_in = 1;

				$comments = get_comments(array(
					'post_id' => $id,
					'type' => 'like',
					'user_id' => get_current_user_id()
				));
				$check_rate = $comments[0]->comment_content;
				$check_star = in_array($id, get_user_meta(get_current_user_id(), '_upvote_post'));
			}
			$comments = get_comments(array(
					'post_id' => $id,
					'type' => 'like'
				));
			foreach($comments as $comment){
				$count += $comment->comment_content;
			}
			return $this->generate_button($logged_in, 'post', $content, $check_rate, $check_star, $id, $count);
		}
		function upvote_comment_filter($content='') 
		{
			$count = $check_rate = $check_star = $count = $logged_in = 0;
			$id = get_comment_ID();

			if (is_user_logged_in()) {
				$logged_in = 1;

				$comments = get_comments(array(
					'parent' => $id,
					'type' => 'like',
					'user_id' => get_current_user_id()
				));
				$check_rate = $comments[0]->comment_content;
				$check_star = in_array($id, get_user_meta(get_current_user_id(), '_upvote_comment'));
			}
			$comments = get_comments(array(
					'parent' => $id,
					'type' => 'like'
				));
			foreach($comments as $comment){
				$count += $comment->comment_content;
			}
			return $this->generate_button($logged_in, 'comment', $content, $check_rate, $check_star, $id, $count);
		}
		function generate_button($logged_in, $type, $content = '', $check_rate, $check_star, $id, $count)
		{
			if ($logged_in == 0) $do = 'onclick="jQuery(\'#registerModal\').arcticmodal()"';
			$content .= '<div class="upvote"><div class="upvote-frame"><a href="#" '.$do.' rel="'.$type.'_'.$id.'" class="upvote upvote-'.$type.'-'.$id.' upvote-'.$type.' '.($check_rate == 1 ? 'upvoted' : '').'"></a><span class="count count-'.$type.'-'.$id.'">'.$count.'</span>'.(!get_option('upvote_dislikes') ? '<a href="#" '.$do.' rel="'.$type.'_'.$id.'" class="downvote downvote-'.$type.'-'.$id.' upvote-'.$type.' '.($check_rate == -1 ? 'downvoted' : '').'"></a>' : '').'<a '.$do.' href="#" rel="'.$type.'_'.$id.'" class="star star-'.$type.'-'.$id.' upvote-'.$type.' '.($check_star ? 'starred' : '').'"></a></div></div>';
			$content .= '<div class="g-hidden">
							<div class="box-modal" id="registerModal">
							' . get_option('upvote_no_auth') . '
								<div class="box-modal_close arcticmodal-close">закрыть</div>
							</div>
						</div>';
			return $content;
		}
		function upvote_post()
		{
			// проверяем залогинен ли пользователь
			if (is_user_logged_in()) {
				// Нужно убедиться что айди передано и является числом
				if (isset($_POST['post_id']) && is_numeric($_POST['post_id'])) {
					// Проверка существования поста вообще
					$check = get_post($_POST['post_id']);
					if (!$check) die();						
					// Если установлен ЭКШН...
					if(isset($_POST['actionButton'])){
						$comments = get_comments(array(
								'user_id' => get_current_user_id(),
								'post_id' => $_POST['post_id'],
								'type' => 'like'
						));
						$check = $comments[0]->comment_content;		
						$time = current_time('mysql');
						switch($_POST['actionButton']){
							case 'upvote':
								if (isset($check) && $check == 1) die();
								if (isset($check) && ($check == 0 || $check = -1)) {
									$res = wp_update_comment(array(
										'comment_ID' => $comments[0]->comment_ID,	
										'comment_content' => 1
									));
								}
								else {
									$res = wp_insert_comment(array(  
										'comment_post_ID' => $_POST['post_id'],  
										'comment_author' => '',  
										'comment_author_email' => '',  
										'comment_author_url' => '',  
										'comment_content' => 1,  										
										'comment_type' => 'like',  
										'comment_parent' => 0,  
										'user_id' => get_current_user_id(),  
										'comment_author_IP' => '',  
										'comment_agent' => '',  
										'comment_date' => $time,  
										'comment_approved' => 1 
									));	
								}
							break;
							case 'upvoted':
								if (isset($check) && ($check = 1)) {
									$res = wp_update_comment(array(
										'comment_ID' => $comments[0]->comment_ID,	
										'comment_content' => 0
									));
								}
							break;
							case 'downvote':
								if (get_option('upvote_dislikes')) die();
								if (isset($check) && $check == -1) die();
								if (isset($check) && ($check == 0 || $check = 1)) {
									$res = wp_update_comment(array(
										'comment_ID' => $comments[0]->comment_ID,	
										'comment_content' => -1
									));
								}
								else {
									$res = wp_insert_comment(array(  
										'comment_post_ID' => $_POST['post_id'],  
										'comment_author' => '',  
										'comment_author_email' => '',  
										'comment_author_url' => '',  
										'comment_content' => -1,  										
										'comment_type' => 'like',  
										'comment_parent' => 0,  
										'user_id' => get_current_user_id() ,  
										'comment_author_IP' => '',  
										'comment_agent' => '',  
										'comment_date' => $time,  
										'comment_approved' => 1 
									));	
								}
							break;
							case 'downvoted':
								if (isset($check) && ($check = -1)) {
									$res = wp_update_comment(array(
										'comment_ID' => $comments[0]->comment_ID,	
										'comment_content' => 0
									));
								}
							break;
							case 'star':
								if (in_array($_POST['post_id'], get_user_meta(get_current_user_id(), '_upvote_post'))) die();
								$res = add_user_meta(get_current_user_id(), '_upvote_post', $_POST['post_id']);
							break;
							case 'starred':
								if (!in_array($_POST['post_id'], get_user_meta(get_current_user_id(), '_upvote_post'))) die();
								$res = delete_user_meta(get_current_user_id(), '_upvote_post', $_POST['post_id']);
							break;
						}
						if($res){
							$response = array('success' => true); 
							$comments = get_comments(array(
									'post_id' => $_POST['post_id'],
									'type' => 'like'
								));
							foreach($comments as $comment){
								$count += $comment->comment_content;
							}
							$response['counttext'] = $count;
						}else{
							$response = array('success' => false);
						}
						header("Content-Type: application/json");
						$response = json_encode($response);
						echo $response;
					}
					die();
				}
				die();
			}		
			die();
		}
		function upvote_comment()
		{
		// проверяем залогинен ли пользователь
			if (is_user_logged_in()) {
				// Нужно убедиться что айди передано и является числом
				if (isset($_POST['comment_id']) && is_numeric($_POST['comment_id'])) {
					// Проверка существования коммента вообще
					$check = get_comments(array(
								'ID' => $_POST['comment_id'],
								'count' => true
							));	
					if (!$check) die();
					// Если установлен ЭКШН...
					if(isset($_POST['actionButton'])){
						$comments = get_comments(array(
								'user_id' => get_current_user_id(),
								'parent' => $_POST['comment_id'],
								'type' => 'like'
						));			
						$check = $comments[0]->comment_content;
						$time = current_time('mysql');						
						switch($_POST['actionButton']){
							case 'upvote':
								if (isset($check) && $check == 1) die();
								if (isset($check) && ($check == 0 || $check = -1)) {
									$res = wp_update_comment(array(
										'comment_ID' => $comments[0]->comment_ID,	
										'comment_content' => 1
									));
								}
								else {
									$res = wp_insert_comment(array(  
										'comment_post_ID' => 0,  
										'comment_author' => '',  
										'comment_author_email' => '',  
										'comment_author_url' => '',  
										'comment_content' => 1,  										
										'comment_type' => 'like',  
										'comment_parent' => $_POST['comment_id'],  
										'user_id' => get_current_user_id() ,  
										'comment_author_IP' => '',  
										'comment_agent' => '',  
										'comment_date' => $time,  
										'comment_approved' => 1 
									));	
								}
							break;
							case 'upvoted':
								if (isset($check) && ($check = 1)) {
									$res = wp_update_comment(array(
										'comment_ID' => $comments[0]->comment_ID,	
										'comment_content' => 0
									));
								}
							break;
							case 'downvote':
								if (get_option('upvote_dislikes')) die();
								if (isset($check) && $check == -1) die();
								if (isset($check) && ($check == 0 || $check = 1)) {
									$res = wp_update_comment(array(
										'comment_ID' => $comments[0]->comment_ID,	
										'comment_content' => -1
									));
								}
								else {
									$res = wp_insert_comment(array(  
										'comment_post_ID' => 0,  
										'comment_author' => '',  
										'comment_author_email' => '',  
										'comment_author_url' => '',  
										'comment_content' => -1,  										
										'comment_type' => 'like',  
										'comment_parent' => $_POST['comment_id'],  
										'user_id' => get_current_user_id() ,  
										'comment_author_IP' => '',  
										'comment_agent' => '',  
										'comment_date' => $time,  
										'comment_approved' => 1 
									));	
								}
							break;
							case 'downvoted':
								if (isset($check) && ($check = -1)) {
									$res = wp_update_comment(array(
										'comment_ID' => $comments[0]->comment_ID,	
										'comment_content' => 0
									));
								}
							break;
							case 'star':
								if (in_array($_POST['comment_id'], get_user_meta(get_current_user_id(), '_upvote_comment'))) die();
								$res = add_user_meta(get_current_user_id(), '_upvote_comment', $_POST['comment_id']);
							break;
							case 'starred':
								if (!in_array($_POST['comment_id'], get_user_meta(get_current_user_id(), '_upvote_comment'))) die();
								$res = delete_user_meta(get_current_user_id(), '_upvote_comment', $_POST['comment_id']);
							break;
						}
						if($res){
							$response = array('success' => true);
							$comments = get_comments(array(
									'parent' => $_POST['comment_id'],
									'type' => 'like'
								));
							foreach($comments as $comment){
								$count += $comment->comment_content;
							}
							$response['counttext'] = $count;
						}else{
							$response = array('success' => false);
						}
						header("Content-Type: application/json");
						$response = json_encode($response);
						echo $response;
					}
					die();
				}
				die();
			}		
			die();
		}
		function delete_post($id) 
		{
			$comments = get_comments(array(
				'post_ID' => $id,
				'type' => 'like',
				'count' => false
			));
			
			foreach($comments as $comment){  
				$res = wp_delete_comment($comment->comment_ID, true);
			}  			
		}
		function delete_comment($id) 
		{
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
}

$cpupvote = new CpUpvote();
?>