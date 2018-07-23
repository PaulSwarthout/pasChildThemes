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
$pluginName = "Child Themes Helper";
$pluginFolder = "pasChildThemes";

require_once(dirname(__FILE__) . '/classes/currentTheme.php');
require_once(dirname(__FILE__) . '/lib/common_functions.php');
require_once(dirname(__FILE__) . '/lib/ajax_functions.php');
require_once(dirname(__FILE__) . '/lib/helper_functions.php');
require_once(dirname(__FILE__) . '/classes/debug.php');

define('NEWLINE', "\n");
define('CHILDTHEME', "child");
define('TEMPLATETHEME', "parent");

define('WINSEPARATOR', '\\');
define('SEPARATOR', "/");

function setPath($path) {
	$path = str_replace("\\", "|+|", $path);
	$path = str_replace("/", "|+|", $path);
	$path = str_replace("|+|", SEPARATOR, $path);
	return $path;
}
function getPath($path) {
	if (isWin()) {
		$subfolders = explode(SEPARATOR, $path);
		$path = implode(WINSEPARATOR, $subfolders);
	}
	return $path;
}
function combinePaths($subFolders = Array()) {
	return (implode(SEPARATOR, $subFolders));
}

add_action('admin_menu',							 'pasChildTheme_admin' );
add_action('admin_enqueue_scripts',		 'pasChildThemes_styles' );
add_action('admin_enqueue_scripts',		 'pasChildThemes_scripts');
add_action('wp_ajax_selectFile',			 'pasChildThemes_selectFile');
add_action('wp_ajax_copyFile',				 'pasChildThemes_copyFile');
add_action('wp_ajax_deleteFile',			 'pasChildThemes_deleteFile');
add_action('wp_ajax_createChildTheme', 'pasChildThemes_createChildTheme');
add_action('wp_ajax_verifyRemoveFile', 'pasChildThemes_verifyRemoveFile');

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

function pasChildTheme_admin() {
	add_menu_page( 'ChildThemesHelper', 'Child Theme Helper', 'manage_options', 'manage_child_themes', 'manage_child_themes');
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
		echo "<p class='actionReminder'>Clicking these files removes them from the child theme</p>";
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
	echo "<p class='actionReminder'>Clicking these files copies them to the child theme.</p>";
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
				echo "<li>" . "<p class='file' data-jsdata='$jsdata' onmouseover='javascript:showData(this);' onclick='javascript:selectFile(this);'><nobr>$ff</nobr></p>";
			}
			echo "</li>";
    }
    echo '</ul>';

		echo "</div>";
}

