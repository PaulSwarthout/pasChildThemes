<?PHP
/*
 * pasChildThemes_selectFile() is called as an AJAX call from the Javascript function selectFile().
 * The Javascript function selectFile() is activated by an onclick event from the themes' filelists
 * when the user clicks on a file name.
 */
function pasChildThemes_selectFile() {
	global $currentThemeObject;

	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

	// Posted from Javascript AJAX call
	$directory	= $_POST['directory'];
	$file				= $_POST['fileName'];
	// $themeType is either CHILDTHEME (left pane) or TEMPLATETHEME (right pane).
	$themeType	= $_POST['themeType'];

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
/*
 * pasChildThemes_verifyRemoveFile() 
 *   is called from the Javascript function removeChildFile() in 'js/pasChildThemes.js'
 */
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
/*
 * pasChildThemes_verifyCopyFile() 
 *   is called from the Javascript function copyTemplateFile() in 'js/pasChildThemes.js'
 */
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
/* Tricky code:
 * We want to pass the same arguments that were passed in, almost.
 * 'action' gets modified and
 * 'childThemeRoot', and 'templateThemeRoot' get added.
 */
	$args = [
		'childThemeRoot'		=> $currentThemeObject->childThemeRoot,
		'templateThemeRoot' => $currentThemeObject->templateThemeRoot,
		'action'						=>'copyFile',
					];
	// We set $args['action'] above. If we do not unset($_POST['action']) then the foreach
	// loop below will overwrite it.
	unset($_POST['action']);

	foreach ($_POST as $key => $value) {
		$args[$key] = $value;
	}

	/* If file doesn't exist. Copy it. We're done.
   * If the file does exist, and the child theme file and the template theme file
	 *   are already identical. We're done. No need to copy it.
	 * If the file does exist, and the files are not identical, prompt the user to overwrite.
	 */
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
/*
 * pasChildThemes_copyFile() 
 *   is called from the Javascript function overwriteFile() in 'js/pasChildThemes.js' AND
 *   from pasChildThemes_verifyCopyFile() when the child theme file does not exist.
 * If the child theme file does not exist, $args are passed in, instead of coming as a AJAX POST.
 * If the folders to the new child theme file do not exist: create them.
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

/*
 * pasChildThemes_deleteFile() 
 *   is called from the Javascript function deleteChildFile() in 'js/pasChildThemes.js'
 * Delete the file and any empty folders made empty by the deletion of the file or subsequent folders.
 */
function pasChildThemes_deleteFile() {
	killChildFile( Array (
			'stylesheet'				=>$_POST['stylesheet'],
			'directory'					=>$_POST['directory'],
			'fileToDelete'			=>$_POST['fileToDelete'] ) );
}

/* pasChildThemes_createChildTheme()
 *   is called from the Javascript function createChildTheme() in 'js/pasChildThemes.js'
 */
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

	// Remove any characters that are not letters or numbers.
	$childThemeStylesheet = preg_replace("/[^a-z0-9]/g", "", $childThemeStylesheet);
	$childThemePath = $themeRoot . SEPARATOR . $childThemeStylesheet;

	if (file_exists($childThemePath)) {
		displayError("ERROR", "Child theme: <span style='text-decoration:double underline;'>" . $_POST['childThemeName'] . "</span> already exists");
		return;
	}

	mkdir($childThemePath);// theme root + stylesheet

	// Create the style.css file for the child theme.
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

	// Create the functions.php file for the child theme. Use the wp_enqueue_style() function
	// to correctly set up the stylesheets for the child theme.
	$functionsFile = fopen($childThemePath . SEPARATOR . "functions.php", "w");
	fwrite($functionsFile, "<" . "?" . "PHP" . NEWLINE);
	fwrite($functionsFile, "add_action( 'wp_enqueue_scripts', '" . $childThemeStylesheet . "_theme_styles');" . NEWLINE);
	fwrite($functionsFile, "function " . $childThemeStylesheet . "_theme_styles() {" . NEWLINE);
	fwrite($functionsFile, "\t wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );" . NEWLINE);
	fwrite($functionsFile, "\t wp_enqueue_style( '" . $childThemeStylesheet . "-style', dirname(__FILE__) . '/style.css');" . NEWLINE);
	fwrite($functionsFile, "}" . NEWLINE);
	fwrite($functionsFile, "?>");
	fclose($functionsFile);

	// Create a default screenshot.png file.
	$status = new pasChildTheme_ScreenShot(
			['targetFile'					=> $childThemePath . SEPARATOR . "screenshot.png",
			 'childThemeName'			=> $childThemeName,
			 'templateThemeName'	=> $_POST['templateTheme']
			]
		);
	unset($status);

	// Handshake with the Javascript AJAX call that got us here.
	// When "SUCCESS:url" is returned, Javascript will redirect to the url.
	echo "SUCCESS:" . $_POST['href']; 
}
// Save options.
function pasChildThemes_saveOptions() {
	update_option("pasChildThemes_" . $_POST['optionName'], $_POST['optionValue']);
}
