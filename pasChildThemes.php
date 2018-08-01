<?php
/*
	Plugin Name: Child Themes Helper
	Plugin URI: http://www.paulswarthout.com/WordPress/
	Description: (1) Copies files from the template theme to the child theme, perfectly duplicating the path structure. (2) Removes file from the child theme, and removes any empty folders that were made empty by the removal of the child theme file. (3) Creates new child themes from installed template themes.
	Version: 1.0
	Author: Paul A. Swarthout
	License: GPL2
*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

$pluginDirectory	= plugin_dir_path( __FILE__ );
$pluginName				= "Child Themes Helper";
$pluginFolder			= "pasChildThemes";

require_once(dirname(__FILE__) . '/lib/plugin_constants.php'); // pasChildThemes constants
require_once(dirname(__FILE__) . '/lib/common_functions.php'); // General functions used throughout
require_once(dirname(__FILE__) . '/lib/ajax_functions.php');   // Functions called from Javascript using AJAX
require_once(dirname(__FILE__) . '/lib/helper_functions.php'); // Specific purpose functions.
require_once(dirname(__FILE__) . '/classes/currentTheme.php'); // Class which holds information on the currently active theme and its parent.
require_once(dirname(__FILE__) . '/classes/debug.php');        // A general debug class.
require_once(dirname(__FILE__) . '/classes/createScreenShot.php'); // Generates the screenshot.png file.

add_action('admin_menu',				 'pasChildThemes_admin' );
add_action('admin_enqueue_scripts',		 'pasChildThemes_styles' );
add_action('admin_enqueue_scripts',		 'pasChildThemes_scripts');
add_action('init',						 'pasChildThemes_add_ob_start'); // Buffering
add_action('wp_footer',					 'pasChildThemes_flush_ob_end'); // Buffering

/* AJAX functions may be found in the 'lib/ajax_functions.php' file
 *
 * The first click of a file from either the child theme or the template theme will send execution to
 * the pasChildThemes_selectFile function. The selectFile function will display a menu to the user.
 * If a child theme file was clicked, the user will be prompted to remove the file from the child theme.
 * If a template theme file was clicked, the user will be prompted to copy the file to the child theme.
 *
 */
add_action('wp_ajax_selectFile',			 'pasChildThemes_selectFile');

// If the child theme file was clicked, and this plugin discovers that the
add_action('wp_ajax_verifyRemoveFile', 'pasChildThemes_verifyRemoveFile');
add_action('wp_ajax_deleteFile',			 'pasChildThemes_deleteFile');

add_action('wp_ajax_verifyCopyFile',	 'pasChildThemes_verifyCopyFile');
add_action('wp_ajax_copyFile',				 'pasChildThemes_copyFile');

/* The createChildTheme function is triggered with an AJAX call from Javascript when the
 * Create Child Theme button is clicked. */
add_action('wp_ajax_createChildTheme', 'pasChildThemes_createChildTheme');

/* Go get the current theme information.
 * This is a wrapper for the wp_get_theme() function.
 * It loads the information that we'll need for our purposes and tosses everything else that's returned
 *   by the wp_get_theme() function.
 */
$currentThemeObject = new pasChildTheme_currentTheme();

// Load the pasChildThemes CSS style.css file
function pasChildThemes_styles() {
	$pluginDirectory = plugin_dir_url( __FILE__ );
	$debugging = constant('WP_DEBUG');
	wp_enqueue_style('pasChildThemes', $pluginDirectory . "css/style.css" . ($debugging ? "?v=" . rand(0,99999) . "&" : ""), false);
}
// Load the pasChildThemes Javascript script file
function pasChildThemes_scripts() {
	$pluginDirectory = plugin_dir_url(__FILE__);
	$debugging = constant('WP_DEBUG');
	wp_enqueue_script('pasChildThemes_Script', $pluginDirectory . "js/pasChildThemes.js" . ($debugging ? "?v=" . rand(0,99999) . "&" : ""), false);
	wp_enqueue_script('pasChildThemes_Script2', $pluginDirectory . "js/js_common_fn.js" . ($debugging ? "?v=" . rand(0,99999) . "&" : ""), false);
}
// pasChildThemes Dashboard Menu
function pasChildThemes_admin() {
	global $currentThemeObject;
	add_menu_page( 'ChildThemesHelper', 'Child Theme Helper', 'manage_options', 'manage_child_themes', 'manage_child_themes', "", 61);
	if ($currentThemeObject->isChildTheme) {
		// Screenshot generation doesn't work in Linux because of missing fonts.
		// Will attempt to find a fix.
		add_submenu_page('manage_child_themes', 'Generate ScreenShot', 'Generate ScreenShot', 'manage_options', 'genScreenShot', 'generateScreenShot');
//		add_submenu_page('manage_child_themes', 'Test Code', 'Test Code', 'manage_options', 'testCode', 'testCode');
	}
}
// Generates the screenshot.png file in the child theme, if one does not yet exist.
function generateScreenShot() {
	global $currentThemeObject;
	global $pluginDirectory;


	echo "<div class='generateScreenShot_general'>";
	echo "<span class='generateScreenShot_Header'>ScreenShot Generator</span>";
/*
	echo <<<"generateScreenShot"
		Welcome to the ScreenShot Generator, brought to you by the Child Theme Helper plugin.<br><br>
		The 'screenshot.png' file, located in a theme's root folder, is displayed on the Dashboard Themes
		page as the particular theme's graphic.
		For most of the themes that you download, the screenshot.png file is a copy of the theme's header image.
		But your child theme doesn't have a screenshot.png file by default.
		You can copy the screenshot.png file from the template theme, but then you've got two themes on the
		Dashboard Themes page that at a glance, look alike.


		'

generateScreenShot;
*/
	echo "</div>";


	$screenShotFile = $currentThemeObject->childThemeRoot . SEPARATOR . $currentThemeObject->childStylesheet . SEPARATOR . "screenshot.png";

//	if (! file_exists($screenShotFile)) {
		$args = [
			'targetFile'				=> $screenShotFile,
			'childThemeName'		=> $currentThemeObject->childThemeName,
			'templateThemeName' => $currentThemeObject->templateStylesheet,
			'pluginDirectory'		=> $pluginDirectory
			];

		// pasChildTheme_ScreenShot() generates screenshot.png and writes it out. $status not needed afterwards
		$status = new pasChildTheme_ScreenShot($args);
		unset($status); // ScreenShot.png is created in the class __construct() function.

		// All done. Reload the Dashboard Themes page.
		wp_redirect(admin_url("themes.php"));
/*
	} else {
		// Error. Write it. Return from this AJAX call. Javascript will write the message.
		$msg = "The <i>ScreenShot Generator</i> will create a recognizable, and informative "
				 . "<i>temporary</i> image for display on the Dashboard Themes page associated "
				 . "with your currently active theme. The <i>ScreenShot Generator</i> will <b><u>NOT</u></b> "
				 . "overwrite an existing ScreenShot.<br><br>"
				 . "Your currently active theme, <i>" . $currentThemeObject->childThemeName . "</i>, already has a 'screenshot.png' file. "
				 . "<br><br>No changes have been made.";
		displayError("ERROR", $msg);
		unset($msg);
	}
*/
}

function getThemeSelect($type = TEMPLATETHEME) {
	global $currentThemeObject;

	switch ($type) {
		case CHILDTHEME:
			return "<p class='themeName'>" . $currentThemeObject->childThemeName . "</p>";
			break;
		case TEMPLATETHEME:
			return "<p class='themeName'>" . $currentThemeObject->templateThemeName . "</p>";
			break;
	}

}
// showActiveChildTheme() will display the list of files for the child theme in the left-hand pane.
function showActiveChildTheme() {
	global $allThemes;
	global $currentThemeObject;

	$currentThemeInfo = $currentThemeObject; // this is an object.
	if ($currentThemeObject->templateStylesheet) {
		echo "<p class='pasChildTheme_HDR'>CHILD THEME</p>";
		echo "<p class='actionReminder'>Click a file to <u>REMOVE</u> it from the child theme</p>";
	}
	echo getThemeSelect(CHILDTHEME);

	$childThemeFolder = $currentThemeObject->getChildFolder();

	echo "<div class='innerCellLeft'>";
	listFolderFiles($childThemeFolder, CHILDTHEME);
	echo "</div>";
}

// showActiveParentTheme() will display the list of files for the template theme in the right-hand pane.
function showActiveParentTheme() {
	global $currentThemeObject;

	echo "<p class='pasChildTheme_HDR'>THEME TEMPLATE</p>";
	echo "<p class='actionReminder'>Click a file to <u>COPY</u> it to the child theme.</p>";
	echo getThemeSelect(TEMPLATETHEME);

	$parentFolder = $currentThemeObject->getTemplateFolder();

	echo "<div class='innerCellLeft'>";
	listFolderFiles($parentFolder, TEMPLATETHEME);
	echo "</div>";
}

function manage_child_themes() {
	global $currentThemeObject;

	$allThemes = enumerateThemes();
	$select = "<label for='templateTheme'>Template Theme (defaults to currently active theme)<br><select name='templateTheme' id='templateTheme'>";
	foreach ($allThemes as $key => $value) {
		if (! $value['childTheme']) {
			$selected = (strtoupper($currentThemeObject->childThemeName) == strtoupper($value['themeName']) ? " SELECTED " : "");
			$select .= "<option value='$key' $selected>" . $value['themeName'] . "</option>";
		}
	}
	$select .= "</select>";

	if (! current_user_can('manage_options')) { exit; }

	if (! $currentThemeObject->isChildTheme) {
		echo "<div class='createChildThemeBox'>";
		echo "<p class='warningHeading'>Warning</p><br><br>";
		echo "The current theme <u>" . $currentThemeObject->childThemeName . "</u> is <u>not</u> a child theme.<br><br>";
		echo "Do you want to create a child theme?<br><br>";
		echo "<form method='post' >";
		echo "<input type='hidden' name='action' value='createChildTheme'>";
		echo "<input type='hidden' name='href' value='" . admin_url("themes.php") . "'>";
		echo "<label for='childThemeName'>Child Theme Name:<br><input type='text' name='childThemeName' id='childThemeName' value=''></label><br>";
		echo $select . "<br>";
		echo "<label for='ThemeURI'>Theme URI<br><input type='text' name='themeURI' id='themeURI' value=''></label><br>";
		echo "<label for='Description'>Theme Description<br><textarea id='description' name='description'></textarea></label><br>";
		echo "<label for='authorName'>Author Name:<br><input type='text' id='authorName' name='authorName' value=''></label><br>";
		echo "<label for='authorURI'>Author URI:<br><input type='text' id='authorURI' name='authorURI' value=''></label><br>";
		echo "<label for='version'>Version:<br><input type='text' id='version' name='version' value='0.0.1' readonly></label><br>";

		echo "<br>";
		echo "<div class='questionPrompt'>";
		echo "<input type='button' value='Create Child Theme' class='blueButton' onclick='javascript:createChildTheme(this);'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"
		   . "<input type='button' value='Reset' class='blueButton' onclick='javascript:resetForm(this.form);'>";
		echo "</div>";

		echo "</div>";
		return false;
	}

	echo "<div class='pas-grid-container'>";
	echo "<div class='pas-grid-item'>"; // Start grid item 1

	showActiveChildTheme();

	echo "</div>"; // end grid item 1

	echo "<div class='pas-grid-item'>"; // start grid item 2

	showActiveParentTheme();

	echo "</div>"; // end grid item 2
	echo "</div>"; // end grid container
}
// This isn't called anywhere, yet. ListFolderFiles() currently has the entire path associated with each file.
// Changing that to only be the stylesheet and beyond.
function stripRoot($path, $themeType) {
	global $currentThemeObject;
	$sliceStart = $currentThemeObject->getFolderCount($themeType);

	$folderSegments = explode(SEPARATOR, $path);
	$folderSegments = array_slice($folderSegments, $sliceStart);
	$path = implode(SEPARATOR, $folderSegments);

	return $path;
}
/* The listFolderFiles() function is the heart of the child theme and template theme file listings.
 * It is called recursively until all of the themes' files are found.
 * It excludes the ".", "..", and ".git" folders.
 * $dir is the full path to the theme's stylesheet.
 * $themeType is either CHILDTHEME or TEMPLATETHEME.
 * CHILDTHEME and TEMPLATETHEME are constants defined in the /lib/plugin_constants.php file.
 * SEPARATOR is also used herein and is likewise defined in the /lib/plugin_constants.php file.
 */
function listFolderFiles($dir, $themeType){
    $ffs = scandir($dir);

    unset($ffs[array_search('.', $ffs, true)]);
    unset($ffs[array_search('..', $ffs, true)]);
		unset($ffs[array_search('.git', $ffs, true)]);

    // prevent empty ordered elements
    if (count($ffs) < 1)
        return;

		echo "<div class='clt'>";

    echo '<ul>';
    foreach($ffs as $ff){
			if (is_dir($dir . SEPARATOR . $ff)) {
				echo "<li><p class='pasChildThemes_directory'>" . $ff . "</p>";
				if(is_dir($dir.SEPARATOR.$ff)) listFolderFiles($dir.SEPARATOR.$ff, $themeType);
			} else {
				$shortDir = stripRoot($dir, $themeType); // Strips the full path, leaving only the stylesheet and sub folders
				$jsdata = json_encode(
						['directory'=>$shortDir,
						 'fileName'=>$ff,
						 'themeType'=>$themeType
						]
					);
				echo "<li>"
					 . "<p class='file' "
					 . "   data-jsdata='$jsdata' "
					 . "   onclick='javascript:selectFile(this);'>";
				echo "<nobr>$ff</nobr>";
				echo "</p>";
			}
			echo "</li>";
    }
    echo '</ul>';

		echo "</div>";
}
/*
 * The next 3 functions set up buffering on the page.
 * This is so we can wp_redirect(admin_url("themes.php")) after creating a new child theme.
 */
function pasChildThemes_callback($buffer){
	return $buffer;
}

function pasChildThemes_add_ob_start(){
	ob_start("pasChildThemes_callback");
}

function pasChildThemes_flush_ob_end(){
	ob_end_flush();
}

