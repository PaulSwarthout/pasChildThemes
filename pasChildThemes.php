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

$pasChildThemes_pluginDirectory	= plugin_dir_path( __FILE__ );
$pasChildThemes_pluginName				= "Child Themes Helper";
$pasChildThemes_pluginFolder			= "pasChildThemes";
require_once(dirname(__FILE__) . '/lib/plugin_constants.php'); // pasChildThemes constants
require_once(dirname(__FILE__) . '/lib/common_functions.php'); // General functions used throughout
require_once(dirname(__FILE__) . '/lib/ajax_functions.php');   // Functions called from Javascript using AJAX
require_once(dirname(__FILE__) . '/lib/helper_functions.php'); // Specific purpose functions.
require_once(dirname(__FILE__) . '/classes/currentTheme.php'); // Class which holds information on the currently active theme and its parent.
require_once(dirname(__FILE__) . '/classes/createScreenShot.php'); // Generates the screenshot.png file.
/* For debugging purposes. Won't write anything unless WP_DEBUG is set to TRUE.
 * require_once(dirname(__FILE__) . '/classes/debug.php');        // A general debug class.
 * $dbg = new pasDebug(['ajax'=>false, 'onDumpExit'=>true, 'onDumpClear'=>true]);
 */
}

register_activation_hook  (__FILE__, 'pasChildThemes_activate' ); // Create default options
register_deactivation_hook(__FILE__, 'pasChildThemes_deactivate' ); // Delete options.


add_action('admin_menu',				 'pasChildThemes_admin' );
add_action('admin_enqueue_scripts',		 'pasChildThemes_styles' );
add_action('admin_enqueue_scripts',		 'pasChildThemes_scripts');
add_action('init',						 'pasChildThemes_add_ob_start');	 // Response Buffering
add_action('wp_footer',					 'pasChildThemes_flush_ob_end'); // Response Buffering

/* AJAX PHP functions may be found in the 'lib/ajax_functions.php' file
 * AJAX Javascript functions are in the 'js/pasChildThemes.js' file
 *
 * The following 5 ajax functions handle the functionality for removing child theme files 
 * and copying template theme files to the child theme. No changes are EVER made to the template
 * theme files.
 * 
 * It all starts with a user clicking on a file in either the left pane (Child Theme) or the 
 * right pane (Template Theme) and triggering the onclick event to call the Javascript selectFile()
 * function. From there the path is different based upon the $themeType, either Child or Template.
 *
 * For removing a child theme file, the next steps, in order, are:
 *   PHP  pasChildThemes_selectFile()            #1
 *	 JS   removeChildFile()                      #2
 *   PHP  pasChildThemes_verifyRemoveFile()      #3
 *   JS   deleteChildFile()                      #4
 *   PHP  pasChildThemes_deleteFile()            #5
 *   File has been deleted. We're done.
 *
 * For copying a template theme file to the child theme, the next steps, in order, are:
 *   PHP  pasChildThemes_selectFile()            #6
 *   JS   copyTemplateFile()                     #7
 *   PHP  pasChildThemes_verifyCopyFile()        #8
 *   JS   overwriteFile()                        #9
 *   PHP  pasChildThemes_copyFile()              #10
 *   File has been copied. We're done.
 */
add_action('wp_ajax_selectFile',			 'pasChildThemes_selectFile');       // #1, #6
add_action('wp_ajax_verifyRemoveFile', 'pasChildThemes_verifyRemoveFile'); // #3
add_action('wp_ajax_deleteFile',			 'pasChildThemes_deleteFile');       // #5
add_action('wp_ajax_verifyCopyFile',	 'pasChildThemes_verifyCopyFile');   // #8
add_action('wp_ajax_copyFile',				 'pasChildThemes_copyFile');         // #10

/* From the Create Child Theme "form", "submit" button triggers the createChildTheme()
 * javascript function.
 * is triggered with an AJAX call from Javascript when the
 * Create Child Theme button is clicked. */
add_action('wp_ajax_createChildTheme', 'pasChildThemes_createChildTheme');

// Save Options for generating a simple, custom, screenshot.png file for a new child theme.
add_action('wp_ajax_saveOptions', 'pasChildThemes_saveOptions');

/* Go get the current theme information.
 * This is a wrapper for the wp_get_theme() function.
 * It loads the information that we'll need for our purposes and tosses everything else that's returned
 *   by the wp_get_theme() function.
 *
 * It will be accessed throughout the pasChildThemes plugin as:
 *   global $currentThemeObject.
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
	add_menu_page( 'ChildThemesHelper',
		             'Child Themes Helper', 
								 'manage_options', 
								 'manage_child_themes', 
								 'manage_child_themes', 
								 "", 
								 61 // appears just below the Appearances menu.
								);
	if ($currentThemeObject->isChildTheme) { // Prevent overwriting the template theme's screenshot.png file.
		add_submenu_page(	'manage_child_themes', 
											'Generate ScreenShot',
											'Generate ScreenShot', 
											'manage_options', 
											'genScreenShot', 
											'generateScreenShot'
										);
	}
	add_submenu_page( 'manage_child_themes', 
										'Options', 
										'Options', 
										'manage_options', 
										'Options', 
										'pasChildThemes_Options');
}
// pctOption() displays an option on the pasChildThemes options page.
function pctOption($args) {
	$label = $args['label'];
	$optionName = $args['optionName'];
	$defaultValue = $args['default'];
	$onblur = $args['onblur'];
	$ifColorPicker = (array_key_exists('colorPicker', $args) ? $args['colorPicker'] : false);
	$dots = DOTS;
	$optionValue = get_option("pasChildThemes_$optionName", $defaultValue);
	if ($ifColorPicker) {
		$colorPicker = "show color picker";
	} else {
		$colorPicker = "";
	}
	$readonly = (array_key_exists('readonly', $args) ? " READONLY " : "");

	if (array_key_exists('type', $args)) {
		switch (strtolower($args['type'])) {
			case "input":
				$formElement = "<input $readonly type='text' name='$optionName' value='$optionValue' onblur='javascript:$onblur;'>";
				break;
			case "select":
				$formElement = "<select $readonly name='$optionName' onblur='javascript:$onblur'>"
								     . "<option value=''>Choose the Font</option>";
				if (array_key_exists('options', $args)) {
					$options = $args['options'];
					foreach ($options as $value) {
						$selected = ($value[1] == $optionValue ? " SELECTED " : "");
						$formElement .= "<option $selected value='" . $value[1] . "'>" . $value[0] . "</option>";
					}
					$formElement .= "</select>";
				} else {
					$formElement = "<input $readonly type='text' name='$optionName' value='$optionValue' onblur='javascript:$onblur;'>";
				}
		}
	} else {
		$formElement = "<input $readonly type='text' name='$optionName' value='$optionValue' onblur='javascript:$onblur;'>";
	}

	$outputString = <<<"OPTION"
	<div class='pct'>
	<span class='pctOptionHeading'>
		<nobr>$label<span class='dots'>$dots</span></nobr>
	</span>
	<span class='pctOptionValue'>
		$formElement
		$colorPicker
	</span>
	</div>
OPTION;

	return ($outputString);
}
// pasChildThemes' Options page.
function pasChildThemes_Options() {
	global $currentThemeObject;
	echo "<h1>Screen Shot Options</h1>";

	echo pctOption(['label'=>'Image Width: ',
									'optionName'=>'imageWidth',
									'default'=>get_option('pasChildThemes_imageWidth', PASCHILDTHEMES_DEFAULT_IMAGE_WIDTH),
									'onblur'=>'pctSetOption(this)',
									'type'=>'input'
								 ]);

	echo pctOption(['label'=>'Image Height: ',
									'optionName'=>'imageHeight',
									'default'=>get_option('pasChildThemes_imageHeight', PASCHILDTHEMES_DEFAULT_IMAGE_HEIGHT),
									'onblur'=>'pctSetOption(this)',
									'type'=>'input'
								 ]);

	echo pctOption(['label'=>'Background Color: ', 
									'optionName'=>'bcColor', 
									'default'=>get_option('pasChildThemes_bcColor', PASCHILDTHEMES_DEFAULT_SCREENSHOT_BCCOLOR),
									'onblur'=>'pctSetOption(this)',
									'colorPicker'=>false,
									'type'=>'input'
								 ]);

	echo pctOption(['label'=>'Text Color: ',
		              'optionName'=>'fcColor',
									'default'=>get_option('pasChildThemes_fcColor', PASCHILDTHEMES_DEFAULT_SCREENSHOT_FCCOLOR),
									'onblur'=>'pctSetOption(this)',
									'colorPicker'=>false,
									'type'=>'input'
								 ]);

	echo pctOption(['label'=>'Font: ',
								  'optionName'=>'font',
									'default'=>'arial',
									'onblur'=>'pctSetOption(this)',
									'type'=>'select', 
									'options'=>[['Arial', 'arial.ttf'], 
															['Courier-New', 'cour.ttf'], 
															['Black Chancery', 'BLKCHCRY.TTF']]
								 ]);

	echo pctOption(['label'=>'String1: ',
								  'optionName'=>'string1',
									'default'=>$currentThemeObject->childThemeName,
									'onblur'=>'pctSetOption(this);',
									'type'=>'input',
									'fontSize'=>50,
									'topPad'=>0
								 ]);

	echo pctOption(['label'=>'String2: ',
								  'optionName'=>'string2',
									'default'=>"...is a child of " . $currentThemeObject->templateThemeName,
									'onblur'=>'pctSetOption(this);',
									'type'=>'input',
									'fontSize'=>50,
									'topPad'=>0
								 ]);

	echo pctOption(['label'=>'String3: ',
								  'optionName'=>'string3',
									'default'=>PASCHILDTHEMES_NAME,
									'onblur'=>'pctSetOption(this);',
									'type'=>'input',
									'readonly'=>true,
									'fontSize'=>50,
									'topPad'=>0
								 ]);

	echo pctOption(['label'=>'String4: ',
								  'optionName'=>'string4',
									'default'=>PAULSWARTHOUT_URL,
									'onblur'=>'pctSetOption(this);',
									'type'=>'input',
									'readonly'=>true,
									'fontSize'=>50,
									'topPad'=>0
								 ]);
}
// Not yet implemented
function showColorPicker() {
}
// Generates the screenshot.png file in the child theme, if one does not yet exist.
// If changes to the options do not show up, clear your browser's stored images, files, fonts, etc.
//   This applies mostly to Chrome. Tested with update #68.
function generateScreenShot() {
	global $currentThemeObject;
	global $pasChildThemes_pluginDirectory;

	$screenShotFile = $currentThemeObject->childThemeRoot . SEPARATOR . $currentThemeObject->childStylesheet . SEPARATOR . "screenshot.png";

	$args = [
		'targetFile'				=> $screenShotFile,
		'childThemeName'		=> $currentThemeObject->childThemeName,
		'templateThemeName' => $currentThemeObject->templateStylesheet,
		'pluginDirectory'		=> $pasChildThemes_pluginDirectory
		];

	// pasChildTheme_ScreenShot() generates screenshot.png and writes it out. $status not needed afterwards
	// Will overwrite an existing file without checking.
	$status = new pasChildTheme_ScreenShot($args);
	unset($status); // ScreenShot.png is created in the class __construct() function.

	// All done. Reload the Dashboard Themes page.
	// Response buffering turned on so we can do this.
	wp_redirect(admin_url("themes.php"));
}

// Used to be a <select><option></option></select> statement. Could move it back to where it's called.
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
/*
 *	manage_child_themes is the main driver function. This function is called from the Dashboard
 *	menu option 'Child Themes Helper'. This function either:
 *	1) Displays the file list for the child theme and the file list for the template theme or
 *	2) If the currently active theme is NOT a child theme, it displays the "form" to create a new
 *	   child theme.
 */
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
		/* Currently active theme is not a child theme. Prompt to create a child theme.
		 * This is set up to look like a typical HTML <form>, but it is not processed as one.
		 * A form submit will refresh the page. We would like to avoid that so we can display 
		 * any output from the wp_ajax_createChildTheme defined function.
		 */
		echo "<div class='createChildThemeBox'>";
		echo "<p class='warningHeading'>Warning</p><br><br>";
		echo "The current theme <u>" . $currentThemeObject->childThemeName . "</u> is <u>not</u> a child theme.<br><br>";
		echo "Do you want to create a child theme?<br><br>";
		echo "<form>";
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
		echo "</form>";
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
/*
 * stripRoot()
 * The listFolderFiles() function takes a full physical path as a parameter.
 * But the full path to the file must be known when the user clicks on a file
 * in the file list. But the full path up to and including the "themes" folder
 * is constant.
 *
 * The stripRoot() function removes everything in the $path up to and not including
 * the theme's stylesheet folder. In other words, stripRoot() strips the theme root
 * from the file path so that listFolderFiles() when writing out a file, doesn't have
 * to include the full path in every file.
 *
 * stripRoot() takes the full $path and the $themeType (CHILDTHEME or TEMPLATETHEME) as 
 * parameters.
 */
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
 * It excludes the ".", "..", and ".git" folders, if they exist.
 * $dir is the full path to the theme's stylesheet.
 * For example: c:\inetpub\mydomain.com\wp-content\themes\twentyseventeen
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
// Plugin Activation.
function pasChildThemes_activate() {
	add_option('pasChildThemes_fcColor', PASCHILDTHEMES_DEFAULT_SCREENSHOT_FCCOLOR);
	add_option('pasChildThemes_bcColor', PASCHILDTHEMES_DEFAULT_SCREENSHOT_BCCOLOR);
	add_option('pasChildThemes_font', PASCHILDTHEMES_DEFAULT_SCREENSHOT_FONT);
	add_option('pasChildThemes_imageWidth', PASCHILDTHEMES_DEFAULT_IMAGE_WIDTH);
	add_option('pasChildThemes_imageHeight', PASCHILDTHEMES_DEFAULT_IMAGE_HEIGHT);

	add_option('pasChildThemes_string3', PASCHILDTHEMES_NAME);
	add_option('pasChildThemes_string4', PAULSWARTHOUT_URL);
}
// Plugin Deactivation
function pasChildThemes_deactivate() {
	delete_option('pasChildThemes_fcColor');
	delete_option('pasChildThemes_bcColor');
	delete_option('pasChildThemes_font');
	delete_option('pasChildThemes_imageWidth');
	delete_option('pasChildThemes_imageHeight');
	delete_option('pasChildThemes_string1');
	delete_option('pasChildThemes_string2');
	delete_option('pasChildThemes_string3');
	delete_option('pasChildThemes_string4');
}