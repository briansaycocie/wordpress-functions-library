<?php

/**
 * WordPress Functions Library
 *
 * @package WPFL
 * @since 0.1.0
 */

// Useful global constants
define( 'WPFL_VERSION',      '0.1.0' );
define( 'WPFL_URL',          get_stylesheet_directory_uri() );
define( 'WPFL_TEMPLATE_URL', get_template_directory_uri() );
define( 'WPFL_PATH',         get_template_directory() . '/' );
define( 'WPFL_INC',          WPFL_PATH . 'includes/' );

// Include compartmentalized functions
require_once WPFL_INC . 'functions/core.php';
require_once WPFL_INC . 'functions/helpers.php';

// Run the setup functions
WPFL\Core\setup();
