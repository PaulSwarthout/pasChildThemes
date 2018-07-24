<?PHP
function pasChildThemes_selectFile() {
	global $currentThemeObject;

	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

	// Posted from Javascript AJAX call
	$directory	= $_POST['directory'];
	$file				= $_POST['fileName'];
	$themeType	= $_POST['themeType'];

	// Strip folder path beyond the theme root. Remember, theme root ends with the stylesheet folder.

	$directory = getRelativePathBeyondRoot(Array('directory'=>$directory, 'themeType'=>$themeType ) );

	$lowerCaseFile = strtolower($file);
	if (($lowerCaseFile == "style.css" && strlen($directory) == 0) ||
		  ($lowerCaseFile == "functions.php" && strlen($directory) == 0)) {
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
							'childThemeRoot'			=> $currentThemeObject->childThemeRoot,
							'childStylesheet'			=> $currentThemeObject->childStylesheet,
							'templateThemeRoot'		=> $currentThemeObject->templateThemeRoot,
							'templateStylesheet'	=> $currentThemeObject->templateStylesheet,
							'delete_action'				=> 'verifyRemoveFile',
							'copy_action'					=> 'verifyCopyFile'
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
	
	$childThemeRoot			= $_POST['childThemeRoot'];
	$childStylesheet		= $_POST['childStylesheet'];
	$templateThemeRoot	= $_POST['templateThemeRoot'];
	$templateStylesheet = $_POST['templateStylesheet'];
	$directory					= $_POST['directory'];
	$childFileToRemove	= $_POST['childFileToRemove'];

	$childThemeFile			= $_POST['childThemeRoot'] . SEPARATOR . $_POST['childStylesheet'] . SEPARATOR . $_POST['directory'] . SEPARATOR . $_POST['childFileToRemove'];
	$templateThemeFile	= $_POST['templateThemeRoot'] . SEPARATOR . $_POST['templateStylesheet'] . SEPARATOR . $_POST['directory'] . SEPARATOR . $_POST['childFileToRemove'];

	if (files_are_identical($childThemeFile, $templateThemeFile)) {
		// kills the child file and any empty folders made that way because the child file was deleted.
		killChildFile( Array (
				'themeRoot'					=>$_POST['childThemeRoot'],
				'stylesheet'				=>$_POST['childStylesheet'],
				'directory'					=>$_POST['directory'],
				'fileToDelete'			=>$_POST['childFileToRemove'] ) );
	} else {
		// Files are not identical. Child file is different than the original template file.
		// This might be because the user modified the file, but it could also be,
		// that the template file was changed due to an update.

		$childStylesheet = $currentThemeObject->childStylesheet;
		$templateStylesheet = $currentThemeObject->templateStylesheet;

		$JSData = json_encode(Array('childThemeRoot'		=>$childThemeRoot,
																'childStylesheet'		=>$childStylesheet,
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
	// Posted from Javascript AJAX call
	
	$childThemeFile			= $_POST['childThemeRoot'] . SEPARATOR . $_POST['childStylesheet'] . SEPARATOR . $_POST['directory'] . SEPARATOR . $_POST['templateFileToCopy'];
	$templateThemeFile	= $_POST['templateThemeRoot'] . SEPARATOR . $_POST['templateStylesheet'] . SEPARATOR . $_POST['directory'] . SEPARATOR . $_POST['templateFileToCopy'];

	if (! file_exists($childThemeFile) ) {
		pasChildThemes_copyFile($_POST);
	} else if (files_are_identical($childThemeFile, $templateThemeFile)) {
		pasChildThemes_copyFile($_POST);
	} else {
		// File exists and the files are not identical
		$JSData = Array('childThemeRoot'			=>$_POST['childThemeRoot'],
										'childStylesheet'			=>$_POST['childStylesheet'],
										'templateThemeRoot'		=>$_POST['templateThemeRoot'],
										'templateStylesheet'  =>$_POST['templateStylesheet'],
										'directory'						=>$_POST['directory'],
										'templateFileToCopy'	=>$_POST['templateFileToCopy'],
										'action'							=>'copyFile' );
		$JSData = json_encode($JSData);

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
function pasChildThemes_copyFile($post) {
	$directory = $post['directory'];
	$childThemeRoot = $post['childThemeRoot'];
	$childStylesheet = $post['childStylesheet'];
	$templateThemeRoot = $post['templateThemeRoot'];
	$templateStylesheet = $post['templateStylesheet'];
	$fileToCopy = $post['templateFileToCopy'];

	$destinationRoot = $childThemeRoot . SEPARATOR . $childStylesheet . SEPARATOR;
	$folderSegments = explode(SEPARATOR, $directory);
	$dir = $destinationRoot;

	for ($ndx = 0; $ndx < count($folderSegments); $ndx++) {
		$dir .= SEPARATOR . $folderSegments[$ndx];
		if (! file_exists($dir)) {
			mkdir($dir);
		}
	}

	$sourceFile = $post['templateThemeRoot'] . SEPARATOR . $post['templateStylesheet'] . SEPARATOR . $post['directory'] . SEPARATOR . $post['templateFileToCopy'];
	$destinationFile = $post['childThemeRoot'] . SEPARATOR . $post['childStylesheet'] . SEPARATOR . $post['directory'] . SEPARATOR . $post['templateFileToCopy'];
	$result = copy($sourceFile, $destinationFile);
	if ($result === false) {
		echo "Failed to copy<br>$sourceFile<br>to<br>$destinationFile<br>";
	}
	exit;
}

function pasChildThemes_deleteFile() {
	killChildFile( Array (
			'themeRoot'					=>$_POST['themeRoot'],
			'stylesheet'				=>$_POST['stylesheet'],
			'directory'					=>$_POST['directory'],
			'fileToDelete'			=>$_POST['fileToDelete'] ) );
}

function pasChildThemes_createChildTheme() {
	global $currentThemeObject;

	if (strlen(trim($_POST['childThemeName'])) == 0) {
		displayError("Notice", "Child Theme Name cannot be blank.");
	}

	if (strlen(trim($_POST['templateTheme'])) == 0) {
		displayError("Notice", "Template Theme is required.");
	}

	if (strlen(trim($_POST['description'])) == 0) {
		displayError("Notice", "Please write a meaningful description for your theme.");
	}

	if (strlen(trim($_POST['authorName'])) == 0) {
		displayError("Notice", "You didn't specify your name as the author name. That's okay, if this is your only error, we'll use my name by default.");
	}

	if (strlen(trim($_POST['authorURI'])) == 0) {
		displayError("Notice", "If you do not specify your URL, we'll use mine: http://www.PaulSwarthout.com/wordpress");
	}

	$themeRoot = fixFolderSeparators(get_theme_root());
	$childThemeName = $_POST['childThemeName'];
	$childThemeStylesheet = strtolower(preg_replace("/\s/", "", $_POST['childThemeName']));
	$childThemePath = $themeRoot . SEPARATOR . $childThemeStylesheet;

	if (file_exists($childThemePath)) {
		displayError("ERROR", "Child theme: <span style='text-decoration:double underline;'>" . $_POST['childThemeName'] . "</span> already exists");
		return;
	}

	mkdir($childThemePath);

	$styleFile = fopen($childThemePath . SEPARATOR . "style.css", "w");
	fwrite($styleFile, "/*" . NEWLINE);
	fwrite($styleFile, " Theme Name:    " . $childThemeName . NEWLINE);
	fwrite($styleFile, " Theme URI:     " . $_POST['themeURI'] . NEWLINE);
	fwrite($styleFile, " Description:   " . $_POST['description'] . NEWLINE);
	fwrite($styleFile, " Author:        " . $_POST['authorName'] . NEWLINE);
	fwrite($styleFile, " Author URI:    " . $_POST['authorURI'] . NEWLINE);
	fwrite($styleFile, " Template:      " . $_POST['templateTheme'] . NEWLINE);
	fwrite($styleFile, " Version:       " . $_POST['version'] . NEWLINE);
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

	echo "SUCCESS:" . $_POST['href'];
}

// $inputs is an associative array with the following values:
// --- directory - folder path to the file clicked on.
// --- currentThemeObject - an class object containing information on the current active theme
// --- folder path delimiter - is different depending upon Windows vs Linux.
// --- theme type = CHILDTHEME or TEMPLATETHEME. Shows whether click was in the left pane or right pane.
// Function strips all subfolders from the system root to the stylesheet, leaving only the relative path
// beyond the stylesheet.
// So if the theme is MyTheme, and the folder path is: 
//      d:/inetpub/mysite/wp-content/themes/mytheme/template-parts/header 
// and the file clicked on is: header-image.php
// Then this function will return:
//      template-parts/header
//
//This is called by pasChildThemes_selectFile() only.
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
	if ($indexOffset === false) {
		wp_die("ERROR: Cannot find the needle in the haystack. This should never happen. This is a bug.", "Unrecoverable Error: $pluginFolder", 500);
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
