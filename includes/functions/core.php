<?php
namespace WPFL\Core;

/**
 * Set up theme defaults and register supported WordPress features.
 *
 * @since 0.1.0
 *
 * @uses add_action()
 *
 * @return void
 */
function setup() {
	$n = function( $function ) {
		return __NAMESPACE__ . "\\$function";
	};

	add_action( 'after_setup_theme',       $n( 'i18n' )                     );
	add_action( 'after_setup_theme',       $n( 'theme_setup' )              );
	add_action( 'wp_enqueue_scripts',      $n( 'scripts' )                  );
	add_action( 'wp_enqueue_scripts',      $n( 'styles' )                   );
	add_action( 'admin_enqueue_scripts',   $n( 'admin_styles' )             );
	add_action( 'wp_head',                 $n( 'header_meta' )              );
	add_action( 'wp_dashboard_setup',      $n( 'remove_dashboard_widgets' ) );
	add_action( 'widgets_init',            $n( 'register_sidebar_init' )    );
	add_action( 'admin_menu',              $n( 'remove_admin_menus' )       );
	add_filter( 'acf/settings/show_admin', $n( 'hide_acf_menu' )            );
	add_filter( 'wpseo_metabox_prio',      $n( 'move_yoast_seo_to_bottom')  );
}

/**
 * Makes WP Theme available for translation.
 *
 * Translations can be added to the /lang directory.
 * If you're building a theme based on WP Theme, use a find and replace
 * to change 'wptheme' to the name of your theme in all template files.
 *
 * @uses load_theme_textdomain() For translation/localization support.
 *
 * @since 0.1.0
 *
 * @return void
 */
function i18n() {
	load_theme_textdomain( 'wpfl', WPFL_PATH . '/languages' );
}

/**
 * Theme setup
 *
 * @since 0.1.0
 *
 * @return void
 */
function theme_setup() {
	global $wp_version;

	// Add site styles in TinyMCE editor
	add_editor_style( 'assets/css/wpfl.css' );

	// Add theme support for Automatic Feed Links
	if ( version_compare( $wp_version, '3.0', '>=' ) ) :
		add_theme_support( 'automatic-feed-links' );
	else :
		automatic_feed_links();
	endif;

	// Add theme support for Featured Images
	add_theme_support( 'post-thumbnails', array( 'page' ) );

	// This theme uses wp_nav_menu() in the header
	$locations = array(
		'main'                  => __( 'Header', 'wpfl' ),
		'sub'                   => __( 'Sidebar', 'wpfl' ),
		'footer'                => __( 'Footer', 'wpfl' ),
	);
	register_nav_menus( $locations );

	// Add theme support for search form HTML5 markup
	$markup = array( 'search-form', 'comment-form', 'comment-list', );
	add_theme_support( 'html5', $markup );
}

/**
 * Enqueue scripts for front-end.
 *
 * @uses wp_enqueue_script() to load front end scripts.
 *
 * @since 0.1.0
 *
 * @return void
 */
function scripts() {
	/**
	 * Flag whether to enable loading uncompressed/debugging assets. Default false.
	 *
	 * @param bool wpfl_script_debug
	 */
	$debug = apply_filters( 'wpfl_script_debug', false );
	$min = ( $debug || defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	// JQuery
	wp_enqueue_script( 'jquery' );

	// Main
	wp_enqueue_script(
		'wpfl',
		WPFL_TEMPLATE_URL . "/assets/js/wpfl{$min}.js",
		array(),
		WPFL_VERSION,
		true
	);
}

/**
 * Enqueue styles for front-end.
 *
 * @uses wp_enqueue_style() to load front end styles.
 *
 * @since 0.1.0
 *
 * @return void
 */
function styles() {
	/**
	 * Flag whether to enable loading uncompressed/debugging assets. Default false.
	 *
	 * @param bool wpfl_style_debug
	 */
	$debug = apply_filters( 'wpfl_style_debug', false );
	$min = ( $debug || defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	// Main
	wp_enqueue_style(
		'wpfl',
		WPFL_URL . "/assets/css/wpfl{$min}.css",
		array(),
		WPFL_VERSION
	);
}

/**
 * Enqueue styles for WordPress admin/dashboard.
 *
 * @uses admin_enqueue_scripts() to load admin styles.
 *
 * @since 0.1.0
 *
 * @return void
 */
function admin_styles() {
	/**
	 * Flag whether to enable loading uncompressed/debugging assets. Default false.
	 *
	 * @param bool wpfl_style_debug
	 */
	$debug = apply_filters( 'wpfl_style_debug', false );
	$min = ( $debug || defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ) ? '' : '.min';

	// Main
	wp_enqueue_style(
		'wpfl-admin',
		WPFL_URL . "/assets/css/wpfl-admin{$min}.css",
		array(),
		WPFL_VERSION
	);
}

/**
 * Add humans.txt to the <head> element.
 *
 * @uses apply_filters()
 *
 * @since 0.1.0
 *
 * @return void
 */
function header_meta() {
	/**
	 * Filter the path used for the site's humans.txt attribution file
	 *
	 * @param string $humanstxt
	 */
	$humanstxt = apply_filters( 'wpfl_humans', WPFL_TEMPLATE_URL . '/humans.txt' );

	echo '<link type="text/plain" rel="author" href="' . esc_url( $humanstxt ) . '" />';
}

/**
 * Remove default dashboard widgets
 *
 * @since 0.1.0
 *
 * @return void
 */
function remove_dashboard_widgets() {
	remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );   // WordPress News
	// use 'dashboard-network' as the second parameter to remove widgets from a network dashboard.
}

/**
 * Registers a widget area
 *
 * @link https://developer.wordpress.org/reference/functions/register_sidebar/
 *
 * @since 0.1.0
 *
 * @return void
 */
function register_sidebar_init() {
	register_sidebar( array(
		'name'          => __( 'Sidebar', 'wpfl' ),
		'id'            => 'internal-sidebar',
		'description'   => __( 'Add widgets here to appear in your sidebar.', 'wpfl' ),
		'before_widget' => '<section id="%1$s" class="widget %2$s">',
		'after_widget'  => '</section>',
		'before_title'  => '<h2 class="widget-title">',
		'after_title'   => '</h2>',
	) );
}

/**
 * Remove admin menus
 *
 * @since 0.1.0
 *
 * @return void
 */
function remove_admin_menus(){
	remove_menu_page( 'edit.php' );          // Posts
	remove_menu_page( 'edit-comments.php' ); // Comments
}

/**
 * ACF: Enables options page
 *
 * @link https://www.advancedcustomfields.com/add-ons/options-page/
 *
 * @since 0.1.0
 *
 * @return void
 */
if ( function_exists( 'acf_add_options_page' ) ) {
	acf_add_options_page( array(
		'page_title' => 'Site Options',
		'menu_title' => 'Site Options',
		'menu_slug'  => 'site-options',
		'capability' => 'edit_posts',
		'icon_url'   => 'dashicons-admin-settings',
		'position'   => 20,
		'redirect'   => false
	) );

	acf_add_options_sub_page( array(
		'page_title' 	=> 'Header Options',
		'menu_title'	=> 'Header',
		'parent_slug'	=> 'site-options',
	) );

	acf_add_options_sub_page( array(
		'page_title' 	=> 'Footer Options',
		'menu_title'	=> 'Footer',
		'parent_slug'	=> 'site-options',
	) );
}

/**
 * ACF: Hides admin menu
 *
 * @link https://www.advancedcustomfields.com/resources/how-to-hide-acf-menu-from-clients/
 *
 * @since 0.1.0
 *
 * @return void
 */
function hide_acf_menu( $show ) {
	return current_user_can( 'update_core' );
}

/**
 * Yoast SEO: Move meta box to the bottom
 *
 * @since 0.1.0
 *
 * @return void
 */
function move_yoast_seo_to_bottom() {
	return 'low';
}
