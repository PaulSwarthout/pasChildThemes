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

register_deactivation_hook(__FILE__, 'pas_version_deactivate' );

add_action('admin_menu', 'pasChildTheme_admin' );
add_action('admin_enqueue_scripts', 'pasChildThemes_styles' );
add_action('admin_enqueue_scripts', 'pasChildThemes_scripts');
add_action('wp_ajax_selectFile', 'pasChildThemes_selectFile');
add_action('wp_ajax_copyFile', 'pasChildThemes_copyFile');
add_action('wp_ajax_deleteFile', 'pasChildThemes_deleteFile');

function isWin() {
	return (substr(PHP_OS, 0, 3) == "WIN" ? true : false);
}

$currentThemeObject = new pasChildTheme_currentTheme();
$allThemes = enumerateThemes();

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
function killChildFile($args) {
	$childFile = $args['file'];
	$directory = $args['directory'];
	$delimiter = $args['delimiter'];
	$themeRoot = $args['themeRoot'];

	unlink($childFile);

	// Walk the folder tree backwards, from depth to root
	// If each folder successive is empty, remove the folder, otherwise break out, we're done.
	$folderSegments = explode($delimiter, $directory);
	for ($ndx = count($folderSegments) - 1; $ndx >= 0; $ndx--) {
		$dir = $themeRoot . $delimiter . implode($delimiter, $folderSegments);
		if (is_folder_empty($dir)) {
			// Folder is empty, remove it.
			rmdir($dir);
		} else {
			// Folder is not empty. Break out, we're done.
			break;
		}
		unset($folderSegments[count($folderSegments)-1]);
	}
}
// AJAX target:
function pasChildThemes_selectFile() {
	global $currentThemeObject;

	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	// Posted from Javascript AJAX call
	$directory	= $_POST['directory'];
	$file				= $_POST['file'];
	$themeType	= $_POST['type'];
	$delimiter	= $_POST['delimiter'];

	// Strip folder path beyond the theme root. Remember, theme root ends with the stylesheet folder.
	$directory = getRelativePathBeyondRoot(Array('directory'=>$directory, 
																							 'currentThemeObject'=>$currentThemeObject, 
																							 'themeType'=>$themeType, 
																							 'delimiter'=>$delimiter) );


	switch ($themeType) {
		case "child": // Child Selected, attempting to REMOVE child object.
			$childFile = $currentThemeObject->themeRoot() . $delimiter . $directory . $delimiter . $file;
			$templateFile = $currentThemeObject->parentThemeRoot() . $delimiter . $directory . $delimiter . $file;

			if (files_are_identical($childFile, $templateFile)) {
				// kills the child file and any empty folders made that way because the child file was deleted.
				killChildFile(Array('file'=>$childFile,
					                  'directory'=>$directory,
														'themeRoot'=>$currentThemeObject->themeRoot(),
														'delimiter'=>$delimiter ) );
			} else {
				$JSData = json_encode(Array('childFileToRemove'=>$childFile,
					                          'delimiter'=>$delimiter,
																		'directory'=>$directory,
																		'action'=>'deleteFile',
																		'childThemeRoot'=>$currentThemeObject->themeRoot() ) );
				echo "<p class='warningHeading'>File has been Modified</p><br><br>";
				echo "The child theme file: <u>" . $directory . $delimiter . $file . "</u> has been modified since it was copied from the <i>" . $currentThemeObject->parentStylesheet() . "</i> theme template.<br><br>";
				echo "<span class='emphasize'>If you proceed, you will LOSE your changes.</span><br><br>";
				echo "Do you want to proceed and <u>DELETE</u> the file from your child theme?<br><br>";
				echo "<div class='questionPrompt'>";
				echo "<INPUT data-jsdata='$JSData' type='button' value='DELETE FILE' class='blueButton' onclick='javascript:deleteChildFile(this);'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
				echo "<INPUT type='button' value='Cancel' class='blueButton' onclick='javascript:cancelDeleteChild(this);'>";
				echo "</div>";
			}
			break;
		case "parent": // Parent Selected, attempting to COPY parent theme file to child theme file.
			$childFile = $currentThemeObject->themeRoot() . $delimiter . $directory . $delimiter . $file;
			$templateFile = $currentThemeObject->parentThemeRoot() . $delimiter . $directory . $delimiter . $file;

			if (!file_exists($childFile)) {
				$folderSegments = explode($delimiter, $directory);
				$dir = $currentThemeObject->themeRoot() . $delimiter;
				// Copy will fail, if folder path doesn't exist.
				// Walk the folder path, create folders as necessary.
				for ($ndx = 0; $ndx < count($folderSegments); $ndx++) {
					$dir .= $delimiter . $folderSegments[$ndx];
					if (! file_exists($dir)) {
						mkdir($dir);
					}
				}
				if (copy($templateFile, $childFile) === false) {
					echo "Failed to copy:<br>Source: $templateFile<br>TO<br>Destination: $childFile<br>";
				}
			} else {
				if (! files_are_identical($childFile, $templateFile) ) {
					$JSData = json_encode(Array('sourceFile' => $templateFile, 'destinationFile' => $childFile, 'action' => 'copyFile'));
					echo "<p class='warningHeading'>File Exists and is Different</p><br><br>";
					echo "The file: <u>" . $directory . $delimiter . $file . "</u> already exists in the child theme and has been modified.<br><br>";
					echo "<span class='emphasize'>If you proceed to <i>overwrite</i> the file, any changes that you have made, will be <b>LOST</b></span>.";
					echo "<br><br>";
					echo "Do you want to overwrite the file and lose the changes that you have made?<br><br>";
					echo "<div class='questionPrompt'>";
					echo "<INPUT data-jsdata='$JSData' type='button' value='OVERWRITE' class='blueButton' onclick='javascript:overwriteFile(this);'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
					echo "<INPUT type='button' value='Cancel' class='blueButton' onclick='javascript:cancelOverwrite(this);'>";
					echo "</div>";
				}

			}
			break;
	}
}
function pasChildThemes_copyFile() {
	$sourceFile = $_POST['sourceFile'];
	$destinationFile = $_POST['destinationFile'];
	$result = copy($sourceFile, $destinationFile);
	if ($result === false) {
		echo "Failed to copy $sourceFile to $destinationFile<br>";
	}
	exit;
}
function pasChildThemes_deleteFile() {
	$fileToDelete = $_POST['fileToDelete'];
	$directory = $_POST['directory'];
	$themeRoot = $_POST['themeRoot'];
	$delimiter = $_POST['delimiter'];
	$childStylesheet = $_POST['childStyleSheet'];

	killChildFile(Array('file'=>$_POST['fileToDelete'],
										  'directory'=>$_POST['directory'],
											'themeRoot'=>$_POST['themeRoot'],
											'delimiter'=>$_POST['delimiter'],
											'stylesheet'=>$_POST['childStylesheet']));
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
	if (! $currentThemeObject->status) {
		echo "<div id='actionBox'>";
		echo "<p class='warningHeading'>Error</p><br><br>";
		echo "The current theme is <u><b>NOT</b></u> a child theme. ";
		echo "This plugin is designed to help you, the developer, work with child themes. ";
		echo "It is useful for moving files between the parent theme template and the child theme. ";
		echo "When your current theme is not a child theme, this plugin is useless.";
		echo "</div>";
		return false;
	}

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

