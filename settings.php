<?php

class CasePressUpVoteSettings {

	function __construct(){
		add_action('admin_init', array($this, 'register_casepress_options'));
		add_action('admin_menu', array($this, 'create_admin_page_option'));
	}

	function CasePressUpVoteSettings(){
		$this->__construct();
	}

	function register_casepress_options(){
		add_option('casepress-upvote_like-posts', 1);
		add_option('casepress-upvote_like-comments', 1);
		add_option('casepress-upvote_dislikes', 1);
		add_option('casepress-upvote_position-posts', 0);
		add_option('casepress-upvote_position-comments', 0);
		add_option('casepress-upvote_buttons-style', 'stackoverflow');
		add_option('casepress-upvote_modals-style', 'default');
		if (FALSE === get_option('casepress-upvote_noauth-msg'))
			add_option( 'casepress-upvote_noauth-msg', "<a href='/wp-login.php?action=register'>Register</a> or <a href='/wp-login.php'>log in</a> to assess the record"); 

		register_setting('casepress-upvote-options-section', 'casepress-upvote_like-posts');
		register_setting('casepress-upvote-options-section', 'casepress-upvote_like-comments');
		register_setting('casepress-upvote-options-section', 'casepress-upvote_dislikes');
		register_setting('casepress-upvote-options-section', 'casepress-upvote_position-posts');
		register_setting('casepress-upvote-options-section', 'casepress-upvote_position-comments');
		register_setting('casepress-upvote-options-section', 'casepress-upvote_buttons-style');
		register_setting('casepress-upvote-options-section', 'casepress-upvote_modals-style');
		register_setting('casepress-upvote-options-section', 'casepress-upvote_noauth-msg');
	}
	
	function create_admin_page_option(){
		add_options_page(__('Upvote Plugin Settings', 'upvote'), __('Upvote Settings', 'upvote'), 'manage_options', 'casepress-upvote', array ($this, 'render_casepress_options_page'));
	}

	function render_casepress_options_page(){ 
	?>
		<div class="wrap">
			<h2><?php _e('Upvote Settings', 'upvote'); ?></h2>
			<form method="post" action="options.php">  
				<?php settings_fields('casepress-upvote-options-section');?>
				<table>
					<tr height="30px">
						<td width="200">
							<label><?php _e('Accept posts likes', 'upvote'); ?></label>
						</td>
						<td width="300">
							<label><?php _e('Disallow', 'upvote'); ?></label>
							<input type="radio" name="casepress-upvote_like-posts" value="0" <?php checked( get_option('casepress-upvote_like-posts'), 0 ); ?>/>
							<label><?php _e('Allow', 'upvote'); ?></label>
							<input type="radio" name="casepress-upvote_like-posts" value="1" <?php checked( get_option('casepress-upvote_like-posts'), 1 ); ?>/>
						</td>
					</tr>
					<tr height="30px">
						<td>
							<label><?php _e('Accept comments likes', 'upvote'); ?></label>
						</td>
						<td>
							<label><?php _e('Disallow', 'upvote'); ?></label>
							<input type="radio" name="casepress-upvote_like-comments" value="0" <?php checked( get_option('casepress-upvote_like-comments'), 0 ); ?>/>
							<label><?php _e('Allow', 'upvote'); ?></label>
							<input type="radio" name="casepress-upvote_like-comments" value="1" <?php checked( get_option('casepress-upvote_like-comments'), 1 ); ?>/>
						</td>
					</tr>
					<tr height="30px">
						<td>
							<label><?php _e('Accept Dislikes', 'upvote'); ?></label>
						</td>
						<td>
							<label><?php _e('Disallow', 'upvote'); ?></label>
							<input type="radio" name="casepress-upvote_dislikes" value="0" <?php checked( get_option('casepress-upvote_dislikes'), 0 ); ?>/>
							<label><?php _e('Allow', 'upvote'); ?></label>
							<input type="radio" name="casepress-upvote_dislikes" value="1" <?php checked( get_option('casepress-upvote_dislikes'), 1 ); ?>/>
						</td>
					</tr>
					<tr height="30px">
						<td>
							<label for="casepress-upvote_position-posts"><?php _e('Buttons Position for Posts', 'upvote'); ?></label>
						</td>
						<td>
							<select style="width: 156px;" id="casepress-upvote_position-posts" name="casepress-upvote_position-posts">
								<option value="0" <?php selected( get_option('casepress-upvote_position-posts'), 0 ); ?>><?php _e('Top', 'upvote'); ?></option>
								<option value="1" <?php selected( get_option('casepress-upvote_position-posts'), 1 ); ?>><?php _e('Left', 'upvote'); ?></option>
								<option value="2" <?php selected( get_option('casepress-upvote_position-posts'), 2 ); ?>><?php _e('Right', 'upvote'); ?></option>
								<option value="3" <?php selected( get_option('casepress-upvote_position-posts'), 3 ); ?>><?php _e('Bottom', 'upvote'); ?></option>
							</select>
						</td>
					</tr>
					<tr height="30px">
						<td>
							<label for="casepress-upvote_position-comments"><?php _e('Buttons Position for Comments', 'upvote'); ?></label>
						</td>
						<td>
							<select style="width: 156px;" id="casepress-upvote_position-comments" name="casepress-upvote_position-comments">
								<option value="0" <?php selected( get_option('casepress-upvote_position-comments'), 0 ); ?>><?php _e('Top', 'upvote'); ?></option>
								<option value="1" <?php selected( get_option('casepress-upvote_position-comments'), 1 ); ?>><?php _e('Left', 'upvote'); ?></option>
								<option value="2" <?php selected( get_option('casepress-upvote_position-comments'), 2 ); ?>><?php _e('Right', 'upvote'); ?></option>
								<option value="3" <?php selected( get_option('casepress-upvote_position-comments'), 3 ); ?>><?php _e('Bottom', 'upvote'); ?></option>
							</select>
						</td>
					</tr>
					<tr height="30px">
						<td>
							<label for="casepress-upvote_buttons-style"><?php _e('Select Buttons Style', 'upvote'); ?></label>
						</td>
						<td>
						<?php
								/*	$files = array();
									$dir = plugin_dir_path( __FILE__ ).'/styles/buttons';
									$dh = opendir($dir);
									print_R($dir);
									while (false !== ($filename = readdir($dh))) {
										$files[] = $filename;
									}
									//sort($files);
									print_R($list);*/
									
									?>
							<select style="width: 156px;" id="casepress-upvote_buttons-style" name="casepress-upvote_buttons-style">
								<?php
									$list = scandir(plugin_dir_path( __FILE__ ).'styles/buttons/');
									unset($list[0], $list[1]);
									foreach($list as $element){ ?>
										<option value="<?php echo $element; ?>" <?php selected(  get_option('casepress-upvote_buttons-style'), $element ); ?>><?php echo $element; ?></option>
									<?php }	?>
							</select>
						</td>
					</tr>
					<tr height="30px">
						<td>
							<label for="casepress-upvote_modals-style"><?php _e('Select Modals Style', 'upvote'); ?></label>
						</td>
						<td>
							<select style="width: 156px;" id="casepress-upvote_modals-style" name="casepress-upvote_modals-style">
							<?php
									$list = scandir(plugin_dir_path( __FILE__ ).'styles/modal/');
									unset($list[0], $list[1]);
									foreach($list as $element){ ?>
										<option value="<?php echo $element; ?>" <?php selected(  get_option('casepress-upvote_modals-style'), $element ); ?>><?php echo $element; ?></option>
									<?php } ?>
							</select>
						</td>
					</tr>
					<tr height="30px">
						<td>
							<label for="casepress-upvote_noauth-msg"><?php _e('Message for not Authorized Users', 'upvote'); ?></label>
						</td>
						<td>
							<input style="width: 296px;" type="text" id="casepress-upvote_noauth-msg" name="casepress-upvote_noauth-msg" value="<?php echo esc_html(get_option('casepress-upvote_noauth-msg')); ?>" />
						</td>
					</tr>
					<tr height="30px">
						<td>
							[upvote post="123" comment="2134"] - <?php _e('Shortcode return\'s upvote buttons for posts or comments, if attributes is blank - ids get\'s automaticly', 'upvote'); ?>
						</td>
					</tr>
					<tr height="30px">
						<td>
							[upvote_favs type="all | posts | comments"] - <?php _e('Shorcode return\'s ids of favorited posts|comments|posts&comments, if attributes is blank - type = all', 'upvote'); ?>
						</td>
					</tr>
				</table>
				<?php submit_button();?>
			</form>
		</div>
	<?php
	}
	
}

new CasePressUpVoteSettings();
?>