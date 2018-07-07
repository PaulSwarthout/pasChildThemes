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

register_deactivation_hook(__FILE__, 'pas_version_deactivate' );

add_action('admin_menu', 'pasChildTheme_admin' );
add_action('admin_enqueue_scripts', 'pasChildThemes_styles' );
add_action('admin_enqueue_scripts', 'pasChildThemes_scripts');

function isWin() {
	return (substr(PHP_OS, 0, 3) == "WIN" ? true : false);
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

$currentThemeObject = new pasChildTheme_currentTheme();
$allThemes = enumerateThemes();

function enumerateThemes() {
	global $currentThemeObject;
	$themes = array();

	// Loads all theme data
	$all_themes = wp_get_themes();

	// Loads theme names into themes array
	foreach ($all_themes as $theme) {
		$name = $theme->get('Name');
		$stylesheet = $theme->get_stylesheet();

		$parent = $theme->get('Template');
		$parentStylesheet = $theme->get_stylesheet();

		$themes[$stylesheet] = Array ('themeName' => $name, 'themeStylesheet' => $stylesheet, 'themeParent' => $parent, 'parentStylesheet' => $parentStylesheet);
	}

	return $themes;
}
function pasChildTheme_admin() {
	add_menu_page( 'ChildThemes', 'Child Theme Tools', 'manage_options', 'manage_child_themes', 'manage_child_themes');
}
function getThemeSelect($type = "parent") {
	global $allThemes;
	global $currentThemeObject;

	if (count($allThemes) > 0) {
		$htmlSelect = "<select><option value=''>Choose Target Theme</option>";
		if ($type == "current") {
			$selectedTheme = $currentThemeObject->name();

			foreach ($allThemes as $key => $theme) {
				$selected = ($key == $currentThemeObject->themeStylesheet() ? " SELECTED " : "");
				$htmlSelect .= "<option value='$key' $selected>" . $theme['themeName'] . "</option>";
			}

		} else {
			$selectedTheme = $currentThemeObject->parent();
			foreach ($allThemes as $key => $theme) {
				$selected = ($key == $currentThemeObject->parentStylesheet() ? " SELECTED " : "");
				$htmlSelect .= "<option value='$key' $selected>" . $theme['themeName'] . "</option>";
			}
		}

		$htmlSelect .= "</select>";

		return $htmlSelect;
	} else {
		return "";
	}
}
function showActiveChildTheme() {
	global $allThemes;
	global $currentThemeObject;

	$currentThemeInfo = $currentThemeObject; // this is an object.
	if ($currentThemeObject->parentStylesheet()) {
		echo "<p class='pasChildTheme_HDR'>CHILD THEME</p>";
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
	listFolderFiles($folder);
	echo "</div>";
}

function showActiveParentTheme() {
		global $currentThemeObject;
		if (! $currentThemeObject->parentStylesheet()) {
			echo "Current Theme is <u><b>NOT</b></u> a child theme.";
			return false;
		}
		echo "<p class='pasChildTheme_HDR'>THEME TEMPLATE</p>";
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
		listFolderFiles($folder);
		echo "</div>";
}

function manage_child_themes() {
	if (!current_user_can('manage_options')) { exit; }

	echo "<div class='pas-grid-container'>";
	echo "<div class='pas-grid-item'>"; // Start grid item 1
	
	showActiveChildTheme();

	echo "</div>"; // end grid item 1

	echo "<div class='pas-grid-item'>"; // start grid item 2

	showActiveParentTheme();

	echo "</div>"; // end grid item 2
	echo "</div>"; // end grid container
}

function listFolderFiles($dir){
    $ffs = scandir($dir);
//		echo "<br>Directory: " . $dir . "<br>";
//		echo "<pre>" . print_r($ffs, true) . "</pre>";

    unset($ffs[array_search('.', $ffs, true)]);
    unset($ffs[array_search('..', $ffs, true)]);

    // prevent empty ordered elements
    if (count($ffs) < 1)
        return;

		echo "<div class='clt'>";

    echo '<ul>';
    foreach($ffs as $ff){
			if (is_dir($dir . '/' . $ff)) {
				echo "<li><p class='pasChildThemes_directory'>" . $ff . "</p>";
				if(is_dir($dir.'/'.$ff)) listFolderFiles($dir.'/'.$ff);
			} else {
				echo '<li onclick="javascript:highlight(this);">'.$ff;
			}
			echo "</li>";
    }
    echo '</ul>';

		echo "</div>";
}

