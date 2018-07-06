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

if (! class_exists('pasChildThemes_activeTheme') ) {
	class pasChildTheme_currentTheme {
		private $currentActiveTheme; // WP_Theme Object for the currently active theme
		private $name;
		private $themeRoot;
		private $folder;
		private $parentName;
		private $parentFolder;

		function __construct() {
			$currentActiveTheme = wp_get_theme();
			$name = $currentActiveTheme->get("Name");
			$folder = $currentActiveTheme->get_stylesheet();
			$themeRoot = $currentActiveTheme->get_theme_root();
			$parent = $currentActiveTheme->parent();

			$parentName = $parent->get("Name");
			$parentFolder = $parent->get_stylesheet();
			$parentThemeRoot = $parent->get_theme_root();

			$x = ['WPTHEME' => $currentActiveTheme, 'name' => $name, 'folder' => $folder, 'themeRoot' => $themeRoot, 
				    'parent' => $parentName, 'parentFolder' => $parentFolder, 'parentThemeRoot' => $parentThemeRoot,
						'themes folder' => WP_CONTENT_DIR . "/themes"];
			echo "<pre>" . print_r($x, true) . "</pre>";

			exit;
		}
		function name() {
			return $currentActiveTheme->get('Name');
		}
		function themeFolder() {
			$folder = $currentActiveTheme->get('textDomain');
			if (!$folder) { $folder = strtolower($currentActiveTheme->get('name')); }
			return $folder;
		}
		function parent() {
			return ($currentActiveTheme->get('Template'));
		}

	}
}
$x = new pasChildTheme_currentTheme();
register_deactivation_hook(__FILE__, 'pas_version_deactivate' );

add_action('admin_menu', 'pasChildTheme_admin' );
add_action('admin_enqueue_scripts', 'pasChildThemes_styles' );

function isWin() {
	return (substr(PHP_OS, 0, 3) == "WIN" ? true : false);
}

function pasChildThemes_styles() {
	$pluginDirectory = plugin_dir_url( __FILE__ );
	wp_enqueue_style('pasChildThemes', $pluginDirectory . "css/style.css", false);
}

$currentThemeObject = wp_get_theme(); // Get current WP_THEME object.
$currentThemeInformation = [ 'name' => $currentThemeObject->Name, 'parent' => $currentThemeObject->get('Template') ];

function enumerateThemes() {
	global $currentThemeInformation;
	$themes = array();

	// Loads all theme data
	$all_themes = wp_get_themes();

	// Loads theme names into themes array
	foreach ($all_themes as $theme) {
		$name = $theme->get('Name');
		$domain = $theme->get('TextDomain');
		$parent = $theme->get('Template');

		$domain = ($domain == "" ? strtolower($name) : $domain);

		if ($currentThemeInformation['name'] == $name) { $currentThemeInformation['name'] = $domain; }

		$themes[$domain] = Array ('themeName' => $name, 'themeDomain' => $domain, 'themeParent' => $parent);
	}
	return Array( 'allThemesList' => $themes, 'currentTheme' => $currentThemeInformation);
}
function pasChildTheme_admin() {
	add_menu_page( 'ChildThemes', 'Child Theme Tools', 'manage_options', 'manage_child_themes', 'manage_child_themes');
}
function getThemeSelect($type = "parent") {
	global $currentThemeInformation;

	$themesInformation = enumerateThemes();
	$listOfThemes = $themesInformation['allThemesList'];
	$currentThemeInfo = $themesInformation['currentTheme'];

	echo "List all themes:<br>";
	echo "<pre>" . print_r($listOfThemes, true) . "</pre>";
	echo "End All Themes List<br><br><br>";

	if (count($listOfThemes) > 0) {
		$htmlSelect = "<select><option value=''>Choose Target Theme</option>";
		if ($type == "current") {
			$selectedTheme = $currentThemeInfo['name'];
		} else {
			$selectedTheme = $currentThemeInfo['parent'];
		}

		echo "List of Themes: <br>";
		echo "<pre>" . print_r($listOfThemes, true) . "</pre>";
		echo "END List of Themes.<br><br><br>";
		foreach ($listOfThemes as $key => $theme) {
			echo "<br>Selected: $selectedTheme, Current Theme Name: " . $theme['themeName'] . "<br>";
			$selected = ($selectedTheme == $theme['themeName'] ? " SELECTED " : "");
			$htmlSelect .= "<option value='$key' $selected >" . $theme['themeName'] . "</option>";
		}
		$htmlSelect .= "</select>";

		return $htmlSelect;
	} else {
		return "";
	}
}
function showActiveChildTheme() {
		echo getThemeSelect("current");

		$delimiter = (isWin() ? "\\" : "/");
		$folderSegments = explode($delimiter, dirname(__FILE__));
		unset($folderSegments[count($folderSegments) - 1]);
		unset($folderSegments[count($folderSegments) - 1]);
		$folderSegments[count($folderSegments)] = "themes";
		$folderSegments[count($folderSegments)] = get_current_theme();
		$folder = implode($delimiter, $folderSegments);

		echo "<div class='innerCellLeft'>";
		listFolderFiles($folder);
		echo "</div>";
}

function showActiveParentTheme() {
		echo getThemeSelect("parent");
		$parentTheme = get_template();

		$delimiter = (isWin() ? "\\" : "/");
		$folderSegments = explode($delimiter, dirname(__FILE__));
		unset($folderSegments[count($folderSegments) - 1]);
		unset($folderSegments[count($folderSegments) - 1]);
		$folderSegments[count($folderSegments)] = "themes";
		$folderSegments[count($folderSegments)] = $parentTheme;
		$folder = implode($delimiter, $folderSegments);

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

    unset($ffs[array_search('.', $ffs, true)]);
    unset($ffs[array_search('..', $ffs, true)]);

    // prevent empty ordered elements
    if (count($ffs) < 1)
        return;

		echo "<div class='clt'>";

    echo '<ul>';
    foreach($ffs as $ff){
      echo '<li>'.$ff;

			if(is_dir($dir.'/'.$ff)) listFolderFiles($dir.'/'.$ff);
			echo "</li>";
    }
    echo '</ul>';

		echo "</div>";
}

