<?php

/*
* Timber Functions
*/

	if (!class_exists('Timber')){
		add_action( 'admin_notices', function(){
			echo '<div class="error"><p>Timber not activated. Make sure you activate the plugin in <a href="' . admin_url('plugins.php#timber') . '">' . admin_url('plugins.php') . '</a></p></div>';
		});
		return;
	}

	class StarterSite extends TimberSite {

		function __construct(){
			add_theme_support('post-formats');
			add_theme_support('post-thumbnails');
			add_theme_support('menus');
			add_filter('timber_context', array($this, 'add_to_context'));
			add_filter('get_twig', array($this, 'add_to_twig'));
			add_action('init', array($this, 'register_post_types'));
			add_action('init', array($this, 'register_taxonomies'));
			parent::__construct();
		}

		function register_post_types(){
			//this is where you can register custom post types
		}

		function register_taxonomies(){
			//this is where you can register custom taxonomies
		}

		function add_to_context($context){
			$context['foo'] = 'bar';
			$context['stuff'] = 'I am a value set in your functions.php file';
			$context['notes'] = 'These values are available everytime you call Timber::get_context();';
			$context['menu'] = new TimberMenu();
			$context['site'] = $this;
			return $context;
		}

		function add_to_twig($twig){
			/* this is where you can add your own fuctions to twig */
			$twig->addExtension(new Twig_Extension_StringLoader());
			$twig->addFilter('myfoo', new Twig_Filter_Function('myfoo'));
			return $twig;
		}

	}

	new StarterSite();

	function myfoo($text){
    	$text .= ' bar!';
    	return $text;
	}

	/*
	* General theme configuration settings
	*/

	// Add support for post-thumbnails
	add_theme_support( 'post-thumbnails' );

	// Add support for automatic RSS feed links
	add_theme_support( 'automatic-feed-links' );

	/**
	* Remove unused items from Admin
	* Add as many items as you like to hide to the $restriced array
	*/

	function remove_menus () {
	global $menu;
		$restricted = array( __('Links') );
		end ($menu);
		while (prev($menu)){
			$value = explode(' ',$menu[key($menu)][0]);
			if(in_array($value[0] != NULL?$value[0]:"" , $restricted)){unset($menu[key($menu)]);}
		}
	}
	add_action('admin_menu', 'remove_menus');

	/**
	* Purge Custom Post-types from cache after update
	*/
	add_action( 'edit_post', 'w3_flush_page_custom', 10, 1 );

	function w3_flush_page_custom( $post_id ) {
		if ( function_exists('w3tc_pgcache_flush' ) ):
			w3tc_pgcache_flush();
		endif;
	}

	/**
	* Cleaner image captions
	*/
	add_filter( 'img_caption_shortcode', 'cleaner_caption', 10, 3 );

	function cleaner_caption( $output, $attr, $content ) {

		/* We're not worried abut captions in feeds, so just return the output here. */
		if ( is_feed() )
			return $output;

		/* Set up the default arguments. */
		$defaults = array(
			'id' => '',
			'align' => 'alignnone',
			'width' => '',
			'caption' => ''
		);

		/* Merge the defaults with user input. */
		$attr = shortcode_atts( $defaults, $attr );

		/* If the width is less than 1 or there is no caption, return the content wrapped between the [caption]< tags. */
		if ( 1 > $attr['width'] || empty( $attr['caption'] ) )
			return $content;

		/* Set up the attributes for the caption <div>. */
		$attributes = ( !empty( $attr['id'] ) ? ' id="' . esc_attr( $attr['id'] ) . '"' : '' );
		$attributes .= ' class="wp-caption ' . esc_attr( $attr['align'] ) . '"';

		/* Open the caption <div>. */
		$output = '<div' . $attributes .'>';

		/* Allow shortcodes for the content the caption was created for. */
		$output .= do_shortcode( $content );

		/* Append the caption text. */
		$output .= '<p class="wp-caption-text">' . $attr['caption'] . '</p>';

		/* Close the caption </div>. */
		$output .= '</div>';

		/* Return the formatted, clean caption. */
		return $output;
	}

	/**
	*	Remove nasty p's around img tags
	*/

	function filter_ptags_on_images($content){
		return preg_replace('/<p>\s*(<a .*>)?\s*(<img .* \/>)\s*(<\/a>)?\s*<\/p>/iU', '\1\2\3', $content);
	}

	add_filter('the_content', 'filter_ptags_on_images');

	/**
	* Enable custom menu support
	* Customize to your needs
	*/

	if( function_exists('register_nav_menus') ):
		register_nav_menus( array(
			'main_menu' => 'The main menu',
			'sub_menu' => 'A submenu'
			));
	endif;

	/*
	* Hide password protected posts everywhere
	*/

	// Filter to hide protected posts
	function exclude_protected($where) {
		global $wpdb;
		return $where .= " AND {$wpdb->posts}.post_password = '' ";
	}

	// Decide where to display them
	function exclude_protected_action($query) {
		if( !is_single() && !is_page() && !is_admin() ) {
			add_filter( 'posts_where', 'exclude_protected' );
		}
	}

	// Action to queue the filter at the right time
	add_action('pre_get_posts', 'exclude_protected_action');

	/**
	* External scripts
	*/

	function enqueue_theme_scripts() {
		// Unregister standard jQuery and reregister as google code.
		wp_deregister_script('jquery');
		wp_register_script( 'jquery', 'http://code.jquery.com/jquery-latest.min.js', null, '1.8.3', true );
		wp_enqueue_script( 'jquery' );

		if( WP_DEBUG ):
			// Plugins
			// For example:
			// wp_enqueue_script( 'infinitescroll', get_template_directory_uri() . '/js/jquery-infinitescroll.min.js', array('jquery'), false, true );

			// Classes
			// For example:
			// wp_enqueue_script( 'main-nav', get_template_directory_uri() . '/js/main-nav.js', array('jquery'), false, true );

			// Pages, Formats, Elements etc.
			// Scripts for pages, elements etc.
			// wp_enqueue_script( 'application', get_template_directory_uri() . '/js/application.js', array('jquery'), false, true	);
		else:
			// All concatenated and compressed JS in one file:
			// wp_enqueue_script( 'application', get_template_directory_uri() . '/js/application.min.js', array('jquery'), false, true	);
		endif;
	}

	add_action('wp_enqueue_scripts', 'enqueue_theme_scripts');

	//
	// Branding & Cleaning
	//

	// Custom admin footer text
	function yd_admin_footer_text () {
			echo '&copy;2014 - <a href="http://zidiot.com/" target="_blank">Zidiot</a>';
	}
	add_filter( 'admin_footer_text', 'yd_admin_footer_text' );

	// Remove top wp logo
	function wps_admin_bar() {
			global $wp_admin_bar;
			$wp_admin_bar->remove_node('wp-logo');
	}
	add_action( 'wp_before_admin_bar_render', 'wps_admin_bar');

	// Remove unused dashboard widgets
	function remove_dashboard_widgets() {
		global $wp_meta_boxes;
		unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_quick_press']);
		unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_incoming_links']);
		/* unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_right_now']); */
		unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_plugins']);
		unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_drafts']);
		unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_recent_comments']);
		unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_primary']);
		unset($wp_meta_boxes['dashboard']['side']['core']['dashboard_secondary']);
		unset($wp_meta_boxes['dashboard']['normal']['core']['dashboard_activity']);
	}
	add_action('wp_dashboard_setup', 'remove_dashboard_widgets' );

	//Remove menu items
	function remove_posts_menu() {
	    //remove_menu_page('edit.php');
			remove_menu_page( 'edit-comments.php' );
	}
	add_action('admin_init', 'remove_posts_menu');

	// Hide author fields
	function hide_profile_fields( $contactmethods ) {
	unset($contactmethods['aim']);
	unset($contactmethods['biografie']);
	return $contactmethods;
	}

	add_filter('user_contactmethods','hide_profile_fields',10,1);
	// Better excerpts

	function custom_excerpt_length( $length ) {
	      return 20;
	}
	add_filter( 'excerpt_length', 'custom_excerpt_length', 999 );

	// Variable & intelligent excerpt length.
	function print_excerpt($length) { // Max excerpt length. Length is set in characters
		global $post;
		$text = $post->post_excerpt;
		if ( '' == $text ) {
			$text = get_the_content('');
			$text = apply_filters('the_content', $text);
			$text = str_replace(']]>', ']]>', $text);
		}
		$text = strip_shortcodes($text); // optional, recommended
		$text = strip_tags($text); // use ' $text = strip_tags($text,'<p><a>'); ' if you want to keep some tags

		$text = substr($text,0,$length);
		$excerpt = reverse_strrchr($text, '.', 1);
		if( $excerpt ) {
			echo apply_filters('the_excerpt',$excerpt);
		} else {
			echo apply_filters('the_excerpt',$text);
		}
	}

	// Returns the portion of haystack which goes until the last occurrence of needle
	function reverse_strrchr($haystack, $needle, $trail) {
	    return strrpos($haystack, $needle) ? substr($haystack, 0, strrpos($haystack, $needle) + $trail) : false;
	}
