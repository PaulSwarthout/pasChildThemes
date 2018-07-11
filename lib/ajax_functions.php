<?PHP
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
