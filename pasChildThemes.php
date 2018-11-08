<?php
/*
	Plugin Name: Child Themes Helper
	Plugin URI: http://www.paulswarthout.com/Child-Themes-Helper/
	Description: ( 1 ) Copies files from the template theme to the child theme, perfectly duplicating the path structure. ( 2 ) Removes file from the child theme, and removes any empty folders that were made empty by the removal of the child theme file. ( 3 ) Creates new child themes from installed template themes.
	Version: 1.1.3
	Author: Paul A. Swarthout
	Author URI: http://www.PaulSwarthout.com
	License: GPL2
	License URI: https://www.gnu.org/licenses/gpl-2.0.html
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

// Constants created for this plugin
require_once( dirname( __FILE__ ) . '/lib/plugin_constants.php' );
// Current active theme information
require_once( dirname( __FILE__ ) . '/classes/class_activeThemeInfo.php' ); //
// Class to create the screenshot.png file for the active theme.
require_once( dirname( __FILE__ ) . '/classes/class_createScreenShot.php' );
// Main class for this plugin
require_once( dirname( __FILE__ ) . '/classes/class_childThemesHelper.php' );
// All functions targeted by wp_ajax_* calls.
require_once( dirname( __FILE__ ) . '/classes/class_ajax_functions.php' );
// Reads the font name from the true type font file
require_once( dirname( __FILE__ ) . '/classes/class_fontMeta.php' );
// Color selection tool
require_once( dirname( __FILE__ ) . '/classes/class_colorPicker.php' );
// Common Functions
require_once( dirname( __FILE__ ) . '/classes/class_common_functions.php' );

/* Go get the current theme information.
 * This is a wrapper for the wp_get_theme( ) function.
 * It loads the information that we'll need for our purposes and tosses everything else
 *	that is returned by the wp_get_theme( ) function.
 */
$pas_cth_pluginDirectory =
	[
		'path' => plugin_dir_path( __FILE__ ),
		'url'  => plugin_dir_url  ( __FILE__ )
	];

$pas_cth_themeInfo = new pas_cth_activeThemeInfo( );
$pas_cth_library	= new pas_cth_library_functions( ['pluginDirectory' => $pas_cth_pluginDirectory] );

$args = [
			'pluginDirectory'	=> $pas_cth_pluginDirectory,
			'pluginName'		=> 'Child Themes Helper',
			'pluginFolder'		=> 'pasChildThemes',
			'activeThemeInfo'	=> $pas_cth_themeInfo,
			'libraryFunctions'	=> $pas_cth_library,
		];

$pas_cth_colorPicker	= new pas_cth_colorPicker( $args );
$args['colorPicker']	= $pas_cth_colorPicker;

$pas_cth_ChildThemesHelper	= new pas_cth_ChildThemesHelper( $args );
$pas_cth_AJAXFunctions		= new pas_cth_AJAXFunctions( $args );

add_action( 'admin_menu',				array( $pas_cth_ChildThemesHelper,	'dashboard_menu' ) );
add_action( 'admin_enqueue_scripts',	array( $pas_cth_ChildThemesHelper,	'dashboard_styles' ) );
add_action( 'admin_enqueue_scripts',	array( $pas_cth_ChildThemesHelper,	'dashboard_scripts' ) );
add_action( 'admin_enqueue_scripts',	array( $pas_cth_colorPicker,		'color_picker_styles' ) );
add_action( 'admin_enqueue_scripts',	array( $pas_cth_colorPicker,		'color_picker_scripts' ) );

add_action( 'init',		'pas_cth_startBuffering' );		// Response Buffering
if (defined("DEMO_CAPABILITY")) {
	add_action( 'init',		'pas_cth_no_profile_access' );
}
add_action( 'wp_footer','pas_cth_flushBuffer' );		// Response Buffering

/* AJAX PHP functions may be found in the 'classes/class_ajax_functions.php' file
 * AJAX Javascript functions are in the 'js/pasChildThemes.js' file
 *
 * The following 5 ajax functions handle the functionality for removing child theme files
 * and copying template theme files to the child theme. No changes are EVER made to the template
 * theme files.
 *
 * It all starts with a user clicking on a file in either the left pane ( Child Theme ) or the
 * right pane ( Template Theme ) and triggering the onclick event to call the Javascript
 * pas_cth_js_selectFile( ) function.
 * From there the path is different based upon the $themeType, either Child ( left ) or Template ( right ).
 *
 * For removing a child theme file, the next steps, in order, are:
 * PHP selectFile( )					#1 REMOVED THIS FUNCTION and Eliminated 1 AJAX call
 * JS pas_cth_js_removeChildFile( ) #2
 * PHP verifyRemoveFile( )			#3
 * JS pas_cth_js_deleteChildFile( ) #4
 * PHP deleteFile( )					#5
 * File has been deleted. We're done.
 *
 * For copying a template theme file to the child theme, the next steps, in order, are:
 * PHP selectFile( )					#6
 * JS pas_cth_js_copyTemplateFile( ) #7
 * PHP verifyCopyFile( )				#8
 * JS pas_cth_js_overwriteFile( ) #9
 * PHP copyFile( )					#10
 * File has been copied. We're done.
 */
add_action( 'wp_ajax_verifyRemoveFile',	array( $pas_cth_AJAXFunctions, 'verifyRemoveFile' ) ); //#3
add_action( 'wp_ajax_deleteFile',		array( $pas_cth_AJAXFunctions, 'deleteFile' ) ); //#5
add_action( 'wp_ajax_verifyCopyFile',	array( $pas_cth_AJAXFunctions, 'verifyCopyFile' ) ); //#8
add_action( 'wp_ajax_copyFile',			array( $pas_cth_AJAXFunctions, 'copyFile' ) ); //#10

/* From the Create Child Theme "form", "submit" button triggers the pas_cth_js_createChildTheme( )
 * javascript function.
 * It is triggered with an AJAX call from Javascript when the
 * Create Child Theme button is clicked. */
add_action( 'wp_ajax_createChildTheme', Array( $pas_cth_AJAXFunctions, 'createChildTheme' ) );

// Save Options for generating a simple, custom, screenshot.png file for a new child theme.
add_action( 'wp_ajax_saveOptions', Array( $pas_cth_AJAXFunctions, 'saveOptions' ) );

add_action( 'wp_ajax_displayColorPicker', Array( $pas_cth_AJAXFunctions, 'chooseColor' ) );
add_action( 'wp_ajax_saveDefaultFont', Array( $pas_cth_AJAXFunctions, "saveFont" ) );

// Plugin Deactivation
function pas_cth_deactivate( ) {
	update_option( 'pas_cth_test', 'plugin-deactivated' );
	delete_option( 'pas_cth_fcColor' );
	delete_option( 'pas_cth_bcColor' );
	delete_option( 'pas_cth_font' );
	delete_option( 'pas_cth_imageWidth' );
	delete_option( 'pas_cth_imageHeight' );
	delete_option( 'pas_cth_string1' );
	delete_option( 'pas_cth_string2' );
	delete_option( 'pas_cth_string3' );
	delete_option( 'pas_cth_string4' );
	delete_option( 'pas_cth_fontList' );
}

register_deactivation_hook( __FILE__, 'pas_cth_deactivate' );//Plugin Deactivation


/*
 * The next 3 functions set up buffering on the page.
 * This is so we can wp_redirect( admin_url( "themes.php" ) ) after creating a new child theme.
 */
function pas_cth_callback( $buffer ){
	return $buffer;
}

function pas_cth_StartBuffering( ){
	ob_start( "pas_cth_callback" );
}

function pas_cth_FlushBuffer( ){
	ob_end_flush( );
}

function pas_cth_no_profile_access() {
	if (strtolower(wp_get_current_user()->user_login) == strtolower("demo")) {
		if (strpos ($_SERVER ['REQUEST_URI'] , 'wp-admin/profile.php' )){
			wp_redirect(get_option('siteurl') . "/wp-admin");
			exit;
		}
	}
}