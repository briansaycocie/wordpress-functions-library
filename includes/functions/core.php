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

	// Theme setup
	add_action( 'after_setup_theme',       $n( 'i18n' )                     );
	add_action( 'after_setup_theme',       $n( 'theme_setup' )              );

	// Styles and scripts
	add_action( 'wp_enqueue_scripts',      $n( 'scripts' )                  );
	add_action( 'wp_enqueue_scripts',      $n( 'styles' )                   );
	add_action( 'admin_enqueue_scripts',   $n( 'admin_styles' )             );

	// Header meta
	add_action( 'wp_head',                 $n( 'header_meta' )              );

	// Admin
	add_action( 'widgets_init',            $n( 'register_sidebar_init' )    );
	add_action( 'wp_dashboard_setup',      $n( 'remove_dashboard_widgets' ) );
	add_action( 'admin_menu',              $n( 'remove_admin_menus' )       );
	add_filter( 'acf/settings/show_admin', $n( 'hide_acf_menu' )            );
	add_action( 'customize_register',      $n( 'remove_css_customizer', 15 ) );

	// Editor
	add_filter( 'tiny_mce_before_init',    $n( 'custom_editor' )             );
	add_filter( 'mce_buttons_2',           $n( 'add_mce_buttons' )           );
	add_filter( 'tiny_mce_before_init',    $n( 'add_mce_styles' )            );

	// Move Yoast SEO metabox below custom fields
	add_filter( 'wpseo_metabox_prio',      $n( 'move_yoast_seo_to_bottom')  );

	// Remove all Events Calendar styles/scripts
	add_action( 'wp_enqueue_scripts',      $n( 'dequeue_tribe_assets', 100 ) );

	// Remove all CF7 styles/scripts on all pages
	add_filter( 'wpcf7_load_js',           '__return_false'                  );
	add_filter( 'wpcf7_load_css',          '__return_false'                  );
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
 * Remove default dashboard widgets
 *
 * @since 0.1.0
 *
 * @return void
 */
function remove_dashboard_widgets() {
	// Use 'dashboard-network' as the second parameter to remove widgets from a network dashboard.
	remove_meta_box( 'dashboard_primary', 'dashboard', 'side' );           // WordPress News
	remove_meta_box( 'dashboard_recent_comments', 'dashboard', 'normal' ); // Recent Comments
	remove_meta_box( 'dashboard_quick_press', 'dashboard', 'normal' );     // Quick Press
}
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

	// Display for admins only
	if ( ! current_user_can( 'update_core' ) ) {
		remove_menu_page( 'wpseo_dashboard' ); // Yoast SEO
	}
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
}

/**
 * Remove the additional CSS section, introduced in 4.7, from the Customizer.
 *
 * @since 0.1.0
 *
 * @param $wp_customize WP_Customize_Manager
 * @return void
 */
function remove_css_customizer( $wp_customize ) {
	$wp_customize->remove_section( 'custom_css' );
}

/**
 * Customize TinyMCE editor
 *
 * @since 0.1.0
 *
 * @param $in TinyMCE
 * @return void
 */
function custom_editor( $in ) {
	// Keep the Toggle Toolbar open
	$in[ 'wordpress_adv_hidden' ] = FALSE;
	return $in;
}

/**
 * Visual Editor - Add buttons that are disabled by default
 *
 * @link http://codex.wordpress.org/TinyMCE_Custom_Buttons
 *
 * @since 0.1.0
 *
 * @param $buttons TinyMCE
 * @return void
 */
function add_mce_buttons( $buttons ) {
    $buttons[] = 'styleselect';
    $buttons[] = 'sup';
    $buttons[] = 'sub';
    return $buttons;
}

/**
 * Visual Editor - Add custom styles in 'styleselect' drop down list
 *
 * @link http://codex.wordpress.org/TinyMCE_Custom_Styles
 *
 * @since 0.1.0
 *
 * @param $init_array TinyMCE
 * @return void
 */
function add_mce_styles( $init_array ) {
	// Define the style_formats array
	$style_formats = array(
		// Each array child is a format with it's own settings
		array(
			'title' => 'Align Left',
			'block' => 'div',
			'classes' => 'alignleft',
			'wrapper' => true,
		),
		array(
			'title' => 'Align Right',
			'block' => 'div',
			'classes' => 'alignright',
			'wrapper' => true,
		),
		array(
			'title' => 'Center',
			'block' => 'div',
			'classes' => 'aligncenter',
			'wrapper' => true,
		),
		array(
			'title' => 'Call Out',
			'block' => 'div',
			'classes' => 'callout',
			'wrapper' => true,
		),
		array(
			'title' => 'Block Quote',
			'block' => 'blockquote',
			'classes' => 'blockquote',
		),
		array(
			'title' => 'Paragraph Lead',
			'block' => 'p',
			'classes' => 'lead',
		),
		array(
			'title' => 'Paragraph Small',
			'block' => 'p',
			'classes' => 'small',
		),
		array(
			'title' => 'Button Blue Large',
			'block' => 'a',
			'classes' => 'btn btn-first btn-lg',
		),
		array(
			'title' => 'Button Blue Medium',
			'block' => 'a',
			'classes' => 'btn btn-first btn-md',
		),
		array(
			'title' => 'Button Blue Small',
			'block' => 'a',
			'classes' => 'btn btn-first btn-sm',
		),
		array(
			'title' => 'Button Blue XSmall',
			'block' => 'a',
			'classes' => 'btn btn-first btn-xsm',
		),
		array(
			'title' => 'Button Red Large',
			'block' => 'a',
			'classes' => 'btn btn-second btn-lg',
		),
		array(
			'title' => 'Button Red Medium',
			'block' => 'a',
			'classes' => 'btn btn-second btn-md',
		),
		array(
			'title' => 'Button Red Small',
			'block' => 'a',
			'classes' => 'btn btn-second btn-sm',
		),
		array(
			'title' => 'Button Red XSmall',
			'block' => 'a',
			'classes' => 'btn btn-second btn-xsm',
		),
	);
    // Insert the array, JSON ENCODED, into 'style_formats'
	$init_array['style_formats'] = json_encode( $style_formats );
	return $init_array;
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

/**
 * The Events Calendar: Dequeue assets
 *
 * @since 0.1.0
 *
 * @return void
 */
function dequeue_tribe_assets() {
	wp_dequeue_style( 'tribe-events-full-calendar-style' );
	wp_dequeue_style( 'tribe-events-calendar-style' );
	wp_dequeue_style( 'tribe-events-calendar-full-mobile-style' );
	wp_dequeue_style( 'tribe-events-calendar-mobile-style' );
	wp_dequeue_style( 'tribe-events-full-pro-calendar-style' );
	wp_dequeue_style( 'tribe-events-calendar-pro-style' );
	wp_dequeue_style( 'tribe-events-calendar-full-pro-mobile-style' );
	wp_dequeue_style( 'tribe-events-calendar-pro-mobile-style' );
}
