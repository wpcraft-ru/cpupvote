<?php

// Stop direct call
if (!empty($_SERVER['SCRIPT_FILENAME']) && basename(__file__) == basename($_SERVER['SCRIPT_FILENAME']))
	die ('Please do not load this page directly. Thanks!');
	
class UpVoteSettings {
	
	var $settingOptionPage;
	// Конструктор объекта
	function UpVoteSettings()
	{
		$this->init();
		$this->actions();
	}
		
	function init()
	{
		$this->settingOptionPage = "upvote_options";
	}
	
	function actions() 
	{
		add_action('admin_menu', array($this,'create_admin_page_option'));
		add_action('admin_init', array($this,'setup_plugin_options'));
	}
	
	// Пункта в настройках будет достаточно.... создадим....
	function create_admin_page_option() 
	{
		add_options_page(__('Setting UpVote Plugin', 'upvote'), 'UpVote', 'manage_options', $this->settingOptionPage, array ($this, 'printAdminPage'));
	}
	
	function setup_plugin_options() 
	{
		add_option('upvote_posts', 1);
		add_option('upvote_comments', 1);
		add_option('upvote_dislikes', 0);
		add_option('upvote_posts_like_accepted', '');
		if (FALSE == get_option('upvote_no_auth')) { add_option( "upvote_no_auth", "<a href='/wp-login.php?action=register'>Register</a> or <a href='/wp-login.php'>log in</a> to assess the record"); }
		
		//  Сначала создаём секцию.
		add_settings_section(
			'upvote_options_plugin_section',	
			__('Genetal Settings', 'upvote'),
			array($this,'description_upvote_settings_section_callback'),
			$this->settingOptionPage
		); 

		add_settings_field(   
			'upvote_posts',	// ID used to identify the field throughout the theme  
			__('Add upvote to posts', 'upvote'),	// The label to the left of the option interface element  
			array($this,'upvote_posts_callback'),	// The name of the function responsible for rendering the option interface  
			$this->settingOptionPage,	// The page on which this option will be displayed  
			'upvote_options_plugin_section',	// The name of the section to which this field belongs  
			array()	// Arg  
		); 
		
		add_settings_field(   
			'upvote_like_accepted',	// ID used to identify the field throughout the theme  
			__('Types of posts, which allowed to assess, separated by commas (blank = all)'),	// The label to the left of the option interface element  
			array($this,'upvote_posts_like_accepted_callback'),	// The name of the function responsible for rendering the option interface  
			$this->settingOptionPage,	// The page on which this option will be displayed  
			'upvote_options_plugin_section',	// The name of the section to which this field belongs  
			array()	// Arg  
		); 
		
		add_settings_field(   
			'upvote_comments',	// ID used to identify the field throughout the theme  
			__('Add upvote to comments', 'upvote'),	// The label to the left of the option interface element  
			array($this,'upvote_comments_callback'),	// The name of the function responsible for rendering the option interface  
			$this->settingOptionPage,	// The page on which this option will be displayed  
			'upvote_options_plugin_section',	// The name of the section to which this field belongs  
			array()	// Arg  
		);
		
		add_settings_field(   
			'upvote_dislikes',	// ID used to identify the field throughout the theme  
			__('Disable dislikes', 'upvote'),	// The label to the left of the option interface element  
			array($this,'upvote_dislikes_callback'),	// The name of the function responsible for rendering the option interface  
			$this->settingOptionPage,	// The page on which this option will be displayed  
			'upvote_options_plugin_section',	// The name of the section to which this field belongs  
			array()	// Arg  
		);
		
		add_settings_field(   
			'upvote_no_auth',	// ID used to identify the field throughout the theme  
			__('Text of box for unregistered users', 'upvote'),	// The label to the left of the option interface element  
			array(&$this,'upvote_no_auth_callback'),	// The name of the function responsible for rendering the option interface  
			$this->settingOptionPage,	// The page on which this option will be displayed  
			'upvote_options_plugin_section',	// The name of the section to which this field belongs  
			array()	// Arg  
		); 
		
		register_setting('upvote_options_plugin_section', 'upvote_posts');
		register_setting('upvote_options_plugin_section', 'upvote_posts_like_accepted');
		register_setting('upvote_options_plugin_section', 'upvote_comments');
		register_setting('upvote_options_plugin_section', 'upvote_dislikes');
		register_setting('upvote_options_plugin_section', 'upvote_no_auth');
	}
	function description_upvote_settings_section_callback()
	{
		_e('You can enable or disable the UpVote buttons where needed', 'upvote');
	}
	function upvote_posts_callback()
	{
		echo "<input name='upvote_posts' type='checkbox' value='1' " . checked( 1, get_option('upvote_posts'), false ) . " />";
	}
	function upvote_posts_like_accepted_callback()
	{
		echo '<input name="upvote_posts_like_accepted" type="text" value="' . get_option('upvote_posts_like_accepted') . '" />';
	}
	function upvote_comments_callback()
	{
		echo "<input name='upvote_comments' type='checkbox' value='1' " . checked( 1, get_option('upvote_comments'), false ) . " />";
	}
	function upvote_dislikes_callback()
	{
		echo "<input name='upvote_dislikes' type='checkbox' value='1' " . checked( 1, get_option('upvote_dislikes'), false ) . " />";
	}
	function upvote_no_auth_callback()
	{
		echo '<input name="upvote_no_auth" type="text" value="' . get_option('upvote_no_auth') . '" />';
	}
	function printAdminPage(){ ?>
		<div class=wrap>
			<h2><?php _e('Upvote Settings', 'upvote');?></h2>
			 
			<form method="post" action="options.php">  
				<?php settings_fields('upvote_options_plugin_section');?>
				<?php do_settings_sections( $this->settingOptionPage );?>
				<?php submit_button();?></form>
		</div>
		<?php }
}

?>