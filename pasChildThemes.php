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

$pluginDirectory = plugin_dir_url( __FILE__ );

require_once(dirname(__FILE__) . '/classes/currentTheme.php');
require_once(dirname(__FILE__) . '/lib/common_functions.php');
require_once(dirname(__FILE__) . '/lib/ajax_functions.php');
require_once(dirname(__FILE__) . '/lib/helper_functions.php');

if (isWin()) {
	define('SEPARATOR', '\\');
} else {
	define('SEPARATOR', '/');
}
define('NEWLINE', "\n");

add_action('admin_menu',							 'pasChildTheme_admin' );
add_action('admin_enqueue_scripts',		 'pasChildThemes_styles' );
add_action('admin_enqueue_scripts',		 'pasChildThemes_scripts');
add_action('wp_ajax_selectFile',			 'pasChildThemes_selectFile');
add_action('wp_ajax_copyFile',				 'pasChildThemes_copyFile');
add_action('wp_ajax_deleteFile',			 'pasChildThemes_deleteFile');
add_action('wp_ajax_createChildTheme', 'pasChildThemes_createChildTheme');

$currentThemeObject = new pasChildTheme_currentTheme();

// $inputs is an associative array with the following values:
// --- directory - folder path to the file clicked on.
// --- currentThemeObject - an class object containing information on the current active theme
// --- folder path delimiter - is different depending upon Windows vs Linux.
// --- theme type = "child" or "parent". Shows whether click was in the left pane or right pane.
// Function strips everything up to and including the stylesheet folder.
// So if the theme is MyTheme, and the folder path is: d:\inetpub\mysite\wp-content\themes\mytheme\template-parts\header and the file clicked on is: header-image.php
// Then this function will return: template-parts\header
function getRelativePathBeyondRoot($inputs) {
	$directory = $inputs['directory'];
	$delimiter = $inputs['delimiter'];
	$currentThemeObject = $inputs['currentThemeObject'];
	$themeType = $inputs['themeType'];

	$folderSegments = explode($delimiter, $directory);
	if ($themeType == "child") {
		$needle = $currentThemeObject->themeStylesheet();
	} else {
		$needle = $currentThemeObject->parentStylesheet();
	}
	$indexOffset = array_search($needle, $folderSegments);
	if ($indexOffset === false) {
		echo "0";
		return false;
	}
	// Remove ThemeRoot. Will use childRoot and templateRoot to rebuild the path, later.
	for ($ndx = $indexOffset; $ndx >= 0; $ndx--) {
		unset($folderSegments[$ndx]);
	}
	// The $directory is the path into the child and into the template where the chosen file is located.
	return (implode($delimiter, $folderSegments));
}

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

function pasChildTheme_admin() {
	add_menu_page( 'ChildThemesHelper', 'Child Theme Helper', 'manage_options', 'manage_child_themes', 'manage_child_themes');
}

function getThemeSelect($type = "parent") {
	global $currentThemeObject;

	switch ($type) {
		case "current":
			return "<p class='themeName'>" . $currentThemeObject->name() . "</p>";
			break;
		case "parent":
			return "<p class='themeName'>" . $currentThemeObject->parent() . "</p>";
			break;
	}

}
function showActiveChildTheme() {
	global $allThemes;
	global $currentThemeObject;

	$currentThemeInfo = $currentThemeObject; // this is an object.
	if ($currentThemeObject->parentStylesheet()) {
		echo "<p class='pasChildTheme_HDR'>CHILD THEME</p>";
		echo "<p class='actionReminder'>Clicking these files removes them from the child theme</p>";
	}
	echo getThemeSelect("current");

	$delimiter = (isWin() ? "\\" : "/");
	$folderSegments = explode($delimiter, $currentThemeObject->themeRoot());
	unset($folderSegments[count($folderSegments) - 1]);
	unset($folderSegments[count($folderSegments) - 1]);
	$folderSegments[count($folderSegments)] = "themes";
	$folderSegments[count($folderSegments)] = $currentThemeObject->themeStylesheet();

	$folder = implode($delimiter, $folderSegments);

	echo "<div class='innerCellLeft'>";
	listFolderFiles($folder, "child");
	echo "</div>";
}

function showActiveParentTheme() {
	global $currentThemeObject;

	echo "<p class='pasChildTheme_HDR'>THEME TEMPLATE</p>";
	echo "<p class='actionReminder'>Clicking these files copies them to the child theme.</p>";
	echo getThemeSelect("parent");
	$parentTheme = $currentThemeObject->parentStylesheet();
	$parentThemeRoot = $currentThemeObject->parentThemeRoot();

	$delimiter = (isWin() ? "\\" : "/");
	$folderSegments = explode($delimiter, $parentThemeRoot);
	unset($folderSegments[count($folderSegments) - 1]);
	unset($folderSegments[count($folderSegments) - 1]);
	$folderSegments[count($folderSegments)] = "themes";
	$folderSegments[count($folderSegments)] = $parentTheme;
	$folder = implode($delimiter, $folderSegments);

//		echo "<pre>" . print_r($folder, true) . "</pre>";

	echo "<div class='innerCellLeft'>";
	listFolderFiles($folder, "parent");
	echo "</div>";
}

function manage_child_themes() {
	global $currentThemeObject;

	$allThemes = enumerateThemes();
	$select = "<label for='templateTheme'>Template Theme (defaults to currently active theme)<br><select name='templateTheme' id='templateTheme'>";
	foreach ($allThemes as $key => $value) {
		$selected = (strtoupper($currentThemeObject->name()) == strtoupper($value['themeName']) ? " SELECTED " : "");
		$select .= "<option value='$key' $selected>" . $value['themeName'] . "</option>";
	}
	$select .= "</select>";

	if (!current_user_can('manage_options')) { exit; }

	if (! $currentThemeObject->status) {
		echo "<div class='createChildThemeBox'>";
		echo "<p class='warningHeading'>Warning</p><br><br>";
		echo "The current theme <u>" . $currentThemeObject->name() . "</u> is <u>not</u> a child theme.<br><br>";
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
		echo "<input type='button' value='Create Child Theme' class='blueButton' onclick='javascript:createChildTheme(this);'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;<input type='button' value='Reset' class='blueButton' onclick='javascript:resetForm(this.form);'>";
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

function listFolderFiles($dir, $themeType){
		$delimiter = (isWin() ? "\\" : "/");
    $ffs = scandir($dir);

    unset($ffs[array_search('.', $ffs, true)]);
    unset($ffs[array_search('..', $ffs, true)]);

    // prevent empty ordered elements
    if (count($ffs) < 1)
        return;

		echo "<div class='clt'>";

    echo '<ul>';
    foreach($ffs as $ff){
			if (is_dir($dir . $delimiter . $ff)) {
				echo "<li><p class='pasChildThemes_directory'>" . $ff . "</p>";
				if(is_dir($dir.$delimiter.$ff)) listFolderFiles($dir.$delimiter.$ff, $themeType);
			} else {
				$jsdata = json_encode(
						['directory'=>$dir, 
						 'file'=>$ff, 
						 'type'=>$themeType, 
						 'delimiter'=>$delimiter 
						]
					);
				echo "<li data-jsdata='$jsdata' onclick='javascript:copyFile(this);'>" . $ff;
			}
			echo "</li>";
    }
    echo '</ul>';

		echo "</div>";
}

