<?PHP
function pasChildThemes_verifyRemoveFile() {
	// Posted from Javascript AJAX call
	
	$directory				= $_POST['directory'];
	$delimiter				= $_POST['delimiter'];
	$parentThemeRoot	= $_POST['parentThemeRoot'];
	$childThemeRoot		= $_POST['childThemeRoot'];
	$childFileToRemove= $_POST['childFileToRemove'];

	$childThemeFile = $childThemeRoot . $delimiter . $directory . $delimiter . $file;
	$templateThemeFile = $parentThemeRoot . $delimiter . $directory . $delimiter . $file;

	if (files_are_identical($childThemeFile, $templateThemeFile)) {
		// kills the child file and any empty folders made that way because the child file was deleted.
		killChildFile(Array('childFileToRemove'=>$childFileToRemove,
												'directory'				 =>$directory,
												'childThemeRoot'				 =>$childThemeRoot,
												'delimiter'				 =>$delimiter ) );
	} else {
		// Files are not identical. Child file is different than the original template file.
		// This might be because the user modified the file, but it could also be,
		// that the template file was changed due to an update.

		$folderSegments = explode($delimiter, $childThemeRoot);
		$childStylesheet = $folderSegments[count($folderSegments)-1];
		unset($folderSegments);

		$folderSegments = explode($delimiter, $parentThemeRoot);
		$parentStylesheet = $folderSegments[count($folderSegments)-1];
		unset($folderSegments);

		$JSData = json_encode(Array('childFileToRemove'=>$childFileToRemove,
																'delimiter'=>$delimiter,
																'directory'=>$directory,
																'action'=>'deleteFile',
																'childThemeRoot'=>$childThemeRoot ) );

		echo "<p class='warningHeading'>Files are Different</p><br><br>";
		echo "Child Theme File: <u>" . $childStylesheet . $delimiter . $directory . $delimiter . $file . "</u><br>";
		echo "Template Theme File: <u>" . $parentStylesheet . $delimiter . $directory . $delimiter . $file . "</u><br><br>";

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
	
	$lowerFile = strtolower($file);
	if (($lowerFile == "style.css" && strlen($directory) == 0) ||
		  ($lowerFile == "functions.php" && strlen($directory) == 0)) {
		$msg = "Your child theme is the active theme. Allowing you to overwrite or delete the '$file' file <span class='emphasize'>could cause your website to crash.</span>"
				 . "You cannot use this plugin to destroy your active theme."
				 . "<br><br>"
				 . "If you must delete or overwrite the '$file' file:"
				 . "<ol class='errorList'>"
				 . "<li>Change your active theme</li>" 
				 . "<li>Use your favorite FTP client to delete or overwrite the '$file' file.</li>"
				 . "</ol>";
		displayError("Cannot Delete or Overwrite File", $msg);
		return false;
		unset($msg);
	}
	unset($lowerFile);

	$jsdata = json_encode(Array(
							'directory' => $directory,
							'childFileToRemove' => $file,
							'themeType' => $themeType,
							'delimiter' => $delimiter,
							'childThemeRoot' => $currentThemeObject->themeRoot(),
							'parentThemeRoot' => $currentThemeObject->parentThemeRoot(),
							'action' => 'verifyRemoveFile'
						));
	echo "MENU:{";
	echo "<p class='warningHeading'>What do you want to do?</p><br>";

	echo "<p id='fileLabel'>Selected File:<span id='fileDisplay'>" . $directory . $delimiter . $file . "</span></p><br>";

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

/*
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
*/
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
	killChildFile(Array('file'=>$_POST['childFileToDelete'],
										  'directory'=>$_POST['directory'],
											'themeRoot'=>$_POST['childThemeRoot'],
											'delimiter'=>$_POST['delimiter']));
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
	fwrite($functionsFile, "\twp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );" . NEWLINE);
	fwrite($functionsFile, "\twp_enqueue_style( '" . $childThemeStylesheet . "-style', dirname(__FILE__) . '/style.css');" . NEWLINE);
	fwrite($functionsFile, "}" . NEWLINE);
	fwrite($functionsFile, "?>");
	fclose($functionsFile);

	echo "SUCCESS:" . $_POST['href'];
}