<?php /**/ ?><?php
/*
Plugin Name: Artiss Code Embed
Plugin URI: http://www.artiss.co.uk/code-embed
Description: Allows you to embed code into your posts & pages
Version: 1.6.1
Author: David Artiss
Author URI: http://www.artiss.co.uk
*/

/**
* Artiss Code Embed
*
* Embed code into a post
*
* @package	Artiss-Code-Embed
* @since	1.6
*/

define( 'artiss_code_embed_version', '1.6.1' );

$functions_dir = WP_PLUGIN_DIR . '/simple-embed-code/includes/';

// Include all the various functions

include_once( $functions_dir . 'get-options.php' );							// Get the default options

if ( is_admin() ) {

	include_once( $functions_dir . 'admin-menu.php' );						// Administration menus

	if ( !has_action( 'wp_dashboard_setup', 'artiss_dashboard_widget' ) ) {

		include_once( $functions_dir . 'artiss-dashboard-widget.php' );		// Artiss dashboard widget

	}

} else {

	include_once( $functions_dir . 'code-embed-filter.php' );				// Filter to apply code embeds

}
?>