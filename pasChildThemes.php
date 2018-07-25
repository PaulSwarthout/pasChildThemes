<?php
/*
	Plugin Name: Child Themes Helper
	Plugin URI: http://www.paulswarthout.com/WordPress/
	Description: (1) Copies files from the template theme to the child theme, perfectly duplicating the path structure. (2) Removes file from the child theme, and removes any empty folders that were made empty by the removal of the child theme file. (3) Creates new child themes from installed template themes.
	Version: 1.0
	Author: Paul A. Swarthout
	License: GPL2
*/

/*
	The Child Themes Helper plugin makes heavy use of AJAX. The process flow is as follows.

*/

// Exit if accessed directly
if ( ! defined( 'ABSPATH' ) ) exit;

$pluginDirectory	= plugin_dir_url( __FILE__ );
$pluginName				= "Child Themes Helper";
$pluginFolder			= "pasChildThemes";

require_once(dirname(__FILE__) . '/lib/plugin_constants.php');
require_once(dirname(__FILE__) . '/classes/currentTheme.php');
require_once(dirname(__FILE__) . '/lib/common_functions.php');
require_once(dirname(__FILE__) . '/lib/ajax_functions.php');
require_once(dirname(__FILE__) . '/lib/helper_functions.php');
require_once(dirname(__FILE__) . '/classes/debug.php');
require_once(dirname(__FILE__) . '/classes/createScreenShot.php');

add_action('admin_menu',							 'pasChildThemes_admin' );
add_action('admin_enqueue_scripts',		 'pasChildThemes_styles' );
add_action('admin_enqueue_scripts',		 'pasChildThemes_scripts');

/* AJAX functions may be found in the 'lib/ajax_functions.php' file */
// The first click of a file from either the child theme or the template theme will send execution to
// the pasChildThemes_selectFile function. The selectFile function will display a menu to the user.
// If a child theme file was clicked, the user will be prompted to remove the file from the child theme.
// If a template theme file was clicked, the user will be prompted to copy the file to the child theme.
add_action('wp_ajax_selectFile',			 'pasChildThemes_selectFile');

// If the child theme file was clicked, and this plugin discovers that the 
add_action('wp_ajax_verifyRemoveFile', 'pasChildThemes_verifyRemoveFile');
add_action('wp_ajax_deleteFile',			 'pasChildThemes_deleteFile');

add_action('wp_ajax_verifyCopyFile',	 'pasChildThemes_verifyCopyFile');
add_action('wp_ajax_copyFile',				 'pasChildThemes_copyFile');

// The createChildTheme function is triggered with an AJAX call from Javascript when the
// Create Child Theme button is clicked.
add_action('wp_ajax_createChildTheme', 'pasChildThemes_createChildTheme');

// Go get the current theme information.
$currentThemeObject = new pasChildTheme_currentTheme();

function pasChildThemes_styles() {
	$pluginDirectory = plugin_dir_url( __FILE__ );
	$debugging = constant('WP_DEBUG');
	wp_enqueue_style('pasChildThemes', $pluginDirectory . "css/style.css" . ($debugging ? "?v=" . rand(0,99999) . "&" : ""), false);
}
function pasChildThemes_scripts() {
	$pluginDirectory = plugin_dir_url(__FILE__);
	$debugging = constant('WP_DEBUG');
	wp_enqueue_script('pasChildThemes_Script', $pluginDirectory . "js/pasChildThemes.js" . ($debugging ? "?v=" . rand(0,99999) . "&" : ""), false);
}
/*
function pasChildThemes_admin() {
	add_theme_page( 'ChildThemesHelper', '<font style="background-color:white;color:black;font-weight:bold;">Child Theme Helper</font>', 'manage_options', 'manage_this_themes', 'manage_child_themes', "", 2);
}
*/
function pasChildThemes_admin() {
	add_menu_page( 'ChildThemesHelper', 'Child Theme Helper', 'manage_options', 'manage_child_themes', 'manage_child_themes', "", 61);
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

	if (!current_user_can('manage_options')) { exit; }

	if (! $currentThemeObject->status) {
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
// The listFolderFiles function is the heart of the file listings. It is called recursively until all of the themes' files are listed.
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
				$jsdata = json_encode(
						['directory'=>$dir, 
						 'fileName'=>$ff,
						 'themeType'=>$themeType
						]
					);
				echo "<li>" . "<p class='file' data-jsdata='$jsdata' onclick='javascript:selectFile(this);'><nobr>$ff</nobr></p>";
			}
			echo "</li>";
    }
    echo '</ul>';

		echo "</div>";
}

