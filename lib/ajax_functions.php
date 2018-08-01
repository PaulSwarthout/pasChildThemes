<?PHP
/*
 * pasChildThemes_selectFile() is activated with an AJAX call
 * from the javascript function selectFile() in file 'js\pasChildThemes.js'
 */
function pasChildThemes_selectFile() {
	global $currentThemeObject;

	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

	// Posted from Javascript AJAX call
	$directory	= $_POST['directory'];
	$file				= $_POST['fileName'];
	$themeType	= $_POST['themeType'];

// No longer used. When everything is working again, I will delete this.
//	$directory = getRelativePathBeyondRoot(Array('directory'=>$directory, 'themeType'=>$themeType ) );

	$folderSegments = explode(SEPARATOR, $directory);
	unset($folderSegments[0]); // Strip theme stylesheet folder from the beginning.
	$directory = implode(SEPARATOR, $folderSegments);

	$lowerCaseFile = strtolower($file);
	if (("style.css" === $lowerCaseFile && 0 === strlen($directory)) ||
		  ("functions.php" === $lowerCaseFile && 0 === strlen($directory))) {
		$msg = "You cannot delete or overwrite the current theme\'s 'style.css' or 'functions.php' files. "
				 . "Your theme is defined, as a minimum, by these two files. "
				 . "Overwriting or deleting either one could cause your WordPress website to crash."
				 . "<br><br>"
				 . "If you must delete or overwrite the '$file' file:"
				 . "<ol class='errorList'>"
				 . "<li>Change your active theme to something else.</li>"
				 . "<li>Use your favorite FTP client to delete or overwrite the '$file' file.</li>"
				 . "<li>Attempt to change your theme back to the original theme.</li>"
				 . "</ol>"
				 . "You cannot use this plugin to forcibly crash your theme.";
		displayError("Cannot Delete or Overwrite File", $msg);
		return false;
		unset($msg);
	}
	unset($lowerCaseFile);

	$jsdata = (Array(
							'directory'						=> $directory,
							'file'								=> $file,
							'themeType'						=> $themeType,
							'childStylesheet'			=> $currentThemeObject->childStylesheet,
							'templateStylesheet'	=> $currentThemeObject->templateStylesheet,
							'delete_action'				=> 'verifyRemoveFile', // attempt to remove child theme file
							'copy_action'					=> 'verifyCopyFile'    // attempt to copy template theme file
						));
	$jsdata = json_encode($jsdata);
	echo "MENU:{";
	echo "<p class='warningHeading'>What do you want to do?</p><br>";

	echo "<p id='fileLabel'>Selected File:<span id='fileDisplay'>" . $directory . SEPARATOR . $file . "</span></p><br>";

	switch ($themeType) {
		case CHILDTHEME:
			echo "<input data-jsdata='$jsdata' type='button' value='Remove File from Child' class='wideBlueButton' onclick='javascript:removeChildFile(this);'><br><br>";
			break;
		case TEMPLATETHEME:
			echo "<input data-jsdata='$jsdata' type='button' value='Copy File to Child' class='wideBlueButton' onclick='javascript:copyTemplateFile(this);'><br><br>";
			break;
	}
	echo "<input data-jsdata='$jsdata' type='button' value='Edit File' class='wideBlueButton' onclick='javascript:editFile(this);'>";
	echo "<p id='clickBox'>Dismiss</p>";
	echo "}";
}
function pasChildThemes_verifyRemoveFile() {
	global $currentThemeObject;
	// Posted from Javascript AJAX call

	$childStylesheet		= $_POST['childStylesheet'];
	$templateStylesheet = $_POST['templateStylesheet'];
	$directory					= $_POST['directory'];
	$childFileToRemove	= $_POST['childFileToRemove'];

	$childThemeFile			= $currentThemeObject->childThemeRoot . SEPARATOR . $_POST['childStylesheet'] . SEPARATOR . $_POST['directory'] . SEPARATOR . $_POST['childFileToRemove'];
	$templateThemeFile	= $currentThemeObject->templateThemeRoot . SEPARATOR . $_POST['templateStylesheet'] . SEPARATOR . $_POST['directory'] . SEPARATOR . $_POST['childFileToRemove'];

	if (files_are_identical($childThemeFile, $templateThemeFile)) {
		// kills the child file and any empty folders made that way because the child file was deleted.
		killChildFile( Array (
				'stylesheet'				=>$_POST['childStylesheet'],
				'directory'					=>$_POST['directory'],
				'fileToDelete'			=>$_POST['childFileToRemove'] ) );
	} else {
		// Files are not identical. Child file is different than the original template file.
		// This might be because the user modified the file, but it could also be,
		// that the template file was changed due to an update.

		$childStylesheet = $currentThemeObject->childStylesheet;
		$templateStylesheet = $currentThemeObject->templateStylesheet;

		$JSData = json_encode(Array('childStylesheet'		=>$childStylesheet,
																'directory'					=>$directory,
																'childFileToRemove'	=>$childFileToRemove,
																'action'						=>'deleteFile' ) );

		echo "<p class='warningHeading'>Files are Different</p><br><br>";
		echo "Child&nbsp;&nbsp;&nbsp;: <u>" . $childStylesheet . SEPARATOR . $directory . SEPARATOR . $childFileToRemove . "</u><br>";
		echo "Template: <u>" . $templateStylesheet . SEPARATOR . $directory . SEPARATOR . $childFileToRemove . "</u><br><br>";

		echo "There are 2 possible reasons for this:<br>";
		echo "<ol type='1'>";
		echo "<li>You have modified the child theme since you copied it to the child theme.</li>";
		echo "<li>You have updated the template theme since you copied the file to the child theme.</li>";
		echo "</ol>";

		echo "<span class='emphasize'>If you proceed, you will LOSE any changes or differences.</span><br><br>";
		echo "Do you want to proceed and <u>DELETE</u> the file from your child theme?<br><br>";
		echo "<div class='questionPrompt'>";
		echo "<INPUT data-jsdata='$JSData' type='button' value='DELETE FILE' class='blueButton' onclick='javascript:deleteChildFile(this);'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		echo "<INPUT type='button' value='Cancel' class='blueButton' onclick='javascript:cancelDeleteChild(this);'>";
		echo "</div>";
	}
}
function pasChildThemes_verifyCopyFile() {
	global $currentThemeObject;

	$childThemeFile			= $currentThemeObject->childThemeRoot .
												SEPARATOR .
												$_POST['childStylesheet'] .
												SEPARATOR .
												$_POST['directory'] .
												SEPARATOR .
												$_POST['templateFileToCopy'];

	$templateThemeFile	= $currentThemeObject->templateThemeRoot .
												SEPARATOR .
												$_POST['templateStylesheet'] .
												SEPARATOR .
												$_POST['directory'] .
												SEPARATOR .
												$_POST['templateFileToCopy'];
	$args = [
		'childThemeRoot'		=> $currentThemeObject->childThemeRoot,
		'templateThemeRoot' => $currentThemeObject->templateThemeRoot,
		'action'						=>'copyFile',
					];
	unset($_POST['action']);

	foreach ($_POST as $key => $value) {
		$args[$key] = $value;
	}

	if (! file_exists($childThemeFile) ) {
		pasChildThemes_copyFile($args);
	} else if (files_are_identical($childThemeFile, $templateThemeFile)) {
		pasChildThemes_copyFile($args);
	} else {
		// File exists and the files are not identical, prompt before overwriting.
		$JSData = json_encode($args);

		echo "<p class='warningHeading'>Files are Different</p><br><br>";
		echo "Child Theme File: <u>" . $_POST['childStylesheet'] . SEPARATOR . $_POST['directory'] . SEPARATOR . $_POST['templateFileToCopy'] . "</u><br>";
		echo "Template Theme File: <u>" . $_POST['templateStylesheet'] . SEPARATOR . $_POST['directory'] . SEPARATOR . $_POST['templateFileToCopy'] . "</u><br><br>";

		echo "There are 2 possible reasons for this:<br>";
		echo "<ol type='1'>";
		echo "<li>You have modified the child theme since you copied it to the child theme.</li>";
		echo "<li>You have updated the template theme since you copied the file to the child theme.</li>";
		echo "</ol>";

		echo "<span class='emphasize'>If you proceed, you will LOSE any differences between the two files.</span><br><br>";
		echo "Do you want to proceed and <u>OVERWRITE</u> the file from your child theme?<br><br>";
		echo "<div class='questionPrompt'>";
		echo "<INPUT data-jsdata='$JSData' type='button' value='OVERWRITE FILE' class='blueButton' onclick='javascript:overwriteFile(this);'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
		echo "<INPUT type='button' value='Cancel' class='blueButton' onclick='javascript:cancelDeleteChild(this);'>";
		echo "</div>";
	}
}
/* copyFile() copies a template theme file to the child theme.
 * If the folders do not already exist, copyFile() creates them.
 */
function pasChildThemes_copyFile($args = null) {
	global $currentThemeObject;

	if ($args != null) {
		$childThemeRoot			= $args['childThemeRoot'];
		$childStylesheet		= $args['childStylesheet'];
		$templateThemeRoot	= $args['templateThemeRoot'];
		$directory					= $args['directory'];
		$templateStylesheet = $args['templateStylesheet'];
		$fileToCopy					= $args['templateFileToCopy'];
	} else {
		$childThemeRoot			= $_POST['childThemeRoot'];
		$childStylesheet		= $_POST['childStylesheet'];
		$templateThemeRoot	= $_POST['templateThemeRoot'];
		$directory					= $_POST['directory'];
		$templateStylesheet = $_POST['templateStylesheet'];
		$fileToCopy					= $_POST['templateFileToCopy'];
	}
//	$dbg = new pasDebug(['ajax'=>true, 'dump'=>['heading'=>'$_POST', 'data'=>$_POST] ]);

	$dir = $childThemeRoot . SEPARATOR . $childStylesheet . SEPARATOR;
	$folderSegments  = explode(SEPARATOR, $directory);

	for ($ndx = 0; $ndx < count($folderSegments); $ndx++) {
		$dir .= SEPARATOR . $folderSegments[$ndx];
		if (! file_exists($dir)) {
			mkdir($dir);
		}
	}

	$sourceFile = $templateThemeRoot	. SEPARATOR . 
								$templateStylesheet . SEPARATOR . 
								$directory					. SEPARATOR . 
								$fileToCopy;

	$targetFile = $childThemeRoot			. SEPARATOR . 
								$childStylesheet		. SEPARATOR .
								$directory					. SEPARATOR .
								$fileToCopy;

	$result = copy($sourceFile, $targetFile);
	if (! $result) {
		echo "Failed to copy<br>$sourceFile<br>to<br>$targetFile<br>";
	}
}

function pasChildThemes_deleteFile() {
	killChildFile( Array (
			'stylesheet'				=>$_POST['stylesheet'],
			'directory'					=>$_POST['directory'],
			'fileToDelete'			=>$_POST['fileToDelete'] ) );
}

function pasChildThemes_createChildTheme() {
	global $currentThemeObject;

	if (0 === strlen(trim($_POST['childThemeName']))) {
		displayError("Notice", "Child Theme Name cannot be blank.");
	}

	if (0 === strlen(trim($_POST['templateTheme']))) {
		displayError("Notice", "Template Theme is required.");
	}

	if (0 === strlen(trim($_POST['description']))) {
		displayError("Notice", "Please write a meaningful description for your theme.");
	}

	if (0 === strlen(trim($_POST['authorName']))) {
		displayError("Notice", "You didn't specify your name as the author name. That's okay, if this is your only error, we'll use my name by default.");
	}

	if (0 === strlen(trim($_POST['authorURI']))) {
		displayError("Notice", "If you do not specify your URL, we'll use mine: http://www.PaulSwarthout.com/wordpress");
	}

	$themeRoot = fixFolderSeparators(get_theme_root());
	$childThemeName = $_POST['childThemeName'];
	// New child theme folder will be the specified name with no whitespace, in lower case.
	$childThemeStylesheet = strtolower(preg_replace("/\s/", "", $_POST['childThemeName']));
	$childThemeStylesheet = preg_replace("/[^a-z0-9]/g", "", $childThemeStylesheet);
	$childThemePath = $themeRoot . SEPARATOR . $childThemeStylesheet;

	if (file_exists($childThemePath)) {
		displayError("ERROR", "Child theme: <span style='text-decoration:double underline;'>" . $_POST['childThemeName'] . "</span> already exists");
		return;
	}

	mkdir($childThemePath);

	$styleFile = fopen($childThemePath . SEPARATOR . "style.css", "w");
	fwrite($styleFile, "/*" . NEWLINE);
	fwrite($styleFile, " Theme Name:    " . $childThemeName					. NEWLINE);
	fwrite($styleFile, " Theme URI:     " . $_POST['themeURI']			. NEWLINE);
	fwrite($styleFile, " Description:   " . $_POST['description']		. NEWLINE);
	fwrite($styleFile, " Author:        " . $_POST['authorName']		. NEWLINE);
	fwrite($styleFile, " Author URI:    " . $_POST['authorURI']			. NEWLINE);
	fwrite($styleFile, " Template:      " . $_POST['templateTheme'] . NEWLINE);
	fwrite($styleFile, " Version:       " . $_POST['version']				. NEWLINE);
	fwrite($styleFile, "*/" . NEWLINE);
	fclose($styleFile);

	$functionsFile = fopen($childThemePath . SEPARATOR . "functions.php", "w");
	fwrite($functionsFile, "<" . "?" . "PHP" . NEWLINE);
	fwrite($functionsFile, "add_action( 'wp_enqueue_scripts', '" . $childThemeStylesheet . "_theme_styles');" . NEWLINE);
	fwrite($functionsFile, "function " . $childThemeStylesheet . "_theme_styles() {" . NEWLINE);
	fwrite($functionsFile, "\t wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );" . NEWLINE);
	fwrite($functionsFile, "\t wp_enqueue_style( '" . $childThemeStylesheet . "-style', dirname(__FILE__) . '/style.css');" . NEWLINE);
	fwrite($functionsFile, "}" . NEWLINE);
	fwrite($functionsFile, "?>");
	fclose($functionsFile);

	$args = [
		'targetFile' => $childThemePath . SEPARATOR . "screenshot.png",
		'childThemeName' => $childThemeName,
		'templateThemeName' => $_POST['templateTheme']
		];

	$status = new pasChildTheme_ScreenShot($args);
	unset($status);

	echo "SUCCESS:" . $_POST['href']; // Returns a message back to Javascript. We have success. Redirect to HREF
}

/*
 * getRelativePathBeyondRoot()
 *   This plugin "sees" the file path as having 4 parts. For this explanation, assume that we are
 *   looking at the following file:
 *   c:\inetpub\mydomain.com\wp-content\themes\my-theme\assets\css\IE8.css
 *
 *   1) The themes root. This is the physical, OS-dependent, server-dependent, path starting at the
 *      system's root folder and ending with the WordPress themes folder.
 *      On a typical Windows system and typical WordPress installation, the themes root path
 *      might look like this:
 *      c:\inetpub\mydomain.com\wp-content\themes
 *
 *   2) The stylesheet. This is the physical folder name for this theme. Typically this would be just:
 *      my-theme
 *
 *   3) The theme's subfolders to the file. For example:
 *      assets\css
 *
 *   4) The filename. This would be the file that we are operating on. For example:
 *      IE8.css
 *
 * This function takes an array of named parameters as it's $inputs.
 *   'directory' = The full physical path from the system root to the file that we want to manipulate.
 *      This is the combination of parts #1, #2, #3, and #4 above.
 *
 *   'themeType' = The theme type may be either CHILDTHEME or TEMPLATETHEME.
 *      CHILDTHEME and TEMPLATETHEME are constants defined in '/lib/plugin_constants.php' and
 *      represent that file clicked on was in the left pane (child) or the right pane (template).
 *
 * This function strips parts #1, #2, and #4 from the input 'directory' and returns # 3.
 *
 * The getRelativePathBeyondRoot() function is called from the function pasChildThemes_selectFile()
 * located in the '/lib/ajax_functions.php' file ONLY.
 */
function getRelativePathBeyondRoot($inputs) {
	global $currentThemeObject;
	$directory					= $inputs['directory'];
	$themeType					= $inputs['themeType'];

	switch ($themeType) {
		case CHILDTHEME:
			$needle = $currentThemeObject->childStylesheet;
			break;
		case TEMPLATETHEME:
			$needle = $currentThemeObject->templateStylesheet;
			break;
	}
	$folderSegments = explode(SEPARATOR, $directory);

	$indexOffset = array_search($needle, $folderSegments);
	if (! $indexOffset) {
		wp_die("ERROR: Cannot find the needle in the haystack. This should never happen. This is a bug.", "Unrecoverable Error", 500);
		return false;
	}
	// Remove everything in $path from the system root, up to and including the stylesheet.
	// Do it backwards to avoid having to deal with a changing index while trying to go forward.
	for ($ndx = $indexOffset; $ndx >= 0; $ndx--) {
		unset($folderSegments[$ndx]);
	}
	// Returns the relative subfolder path beyond the themeRoot . Stylesheet.

	return (implode(SEPARATOR, $folderSegments));
}
function pasChildThemes_saveOptions() {
	update_option("pasChildThemes_" . $_POST['optionName'], $_POST['optionValue']);
}