<?php
   /*
   Plugin Name: Child Themes Helper
   Plugin URI: http://www.paulswarthout.com/index.php/wordpress/
   Description: It is such a hassle to open up an FTP connection, copy a file from the parent theme template to the local system, then FTP it back to the child theme. This plugin solves that by moving the file directly.
   Version: 1.1
   Author: Paul A. Swarthout
   License: GPL2
   */

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

register_deactivation_hook(__FILE__, 'pas_version_deactivate' );

add_action('admin_menu', 'pasChildTheme_admin' );
//add_action('admin_enqueue_scripts', 'pas_version_script' );
//add_action('wp_ajax_hideMenuOption', 'hideMenuOption');

function pasChildTheme_admin() {
	echo "Admin page";
}