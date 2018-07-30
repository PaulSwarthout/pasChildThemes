<?PHP
function isWin() {
	return ("WIN" === strtoupper(substr(PHP_OS, 0, 3)) ? true : false);
}
function files_are_identical($a, $b, $blocksize = 512)
{
	if (is_dir($a) || is_dir($b)) {
		$msg = "Expected 2 files.<br>At least one was a directory.<br><br>File1: $a<br><br>File2: $b<br><br>Aborting....";
		displayError("FILE ERROR", $msg);
		unset($msg);
		exit;
	}
	if (! file_exists($a)) {
		echo "FILE: $a DOES NOT EXIST";
		return false;
	}
	if (! file_exists($b)) {
		echo "FILE: $b DOES NOT EXIST";
		return false;
	}
  // Check if filesize is different
  if(filesize($a) !== filesize($b))
      return false;

  // Check if content is different
  $ah = fopen($a, 'rb');
  $bh = fopen($b, 'rb');

	if ($ah === false || $bh === false) {
		$msg = "File1: " . $a . "<br>File2: " . $b . "<br>Unable to open one or both of the files listed above. <br><br>Aborting....";
		displayError("FILE ERROR", $msg);
		unset($msg);
		exit;
	}

  $result = true;
  while(!feof($ah))
  {
    if(fread($ah, $blocksize) != fread($bh, $blocksize))
    {
      $result = false;
      break;
    }
  }

  fclose($ah);
  fclose($bh);

  return $result;
}
function file_count($dir) {
	$files = scandir($dir);

	/* Consider removing these two lines and just subtracting 2 from the count() result
	 * before returning. This will work for Windows, but what about the other operating systems?
	 * We could do a switch statement here instead, based on PHP_OS. That would eliminate the
	 * array scan times 2, which should improve the performance of this function.
	 */
	unset($files[array_search('.', $files, true)]);
	unset($files[array_search('..', $files, true)]);

	return count($files);
}

function is_folder_empty($dir) {
	return (0 === file_count($dir) ? true : false);
}
function killChildFile($args) {
	global $currentThemeObject;
	$themeRoot = $currentThemeObject->childThemeRoot; // physical path from system root.
	$themeStyle = $args['stylesheet'];		            // Stylesheet - theme's folder name
	$directory = $args['directory'];			            // Path within the theme
	$childFile = $args['fileToDelete'];               // Which file are we deleting.

	$fileToDelete = $themeRoot	. SEPARATOR .
									$themeStyle . SEPARATOR .
									$directory	. SEPARATOR .
									$childFile;

	unlink($fileToDelete);

/* Walk the folder tree backwards, from leaf to root
 * If each successive folder is empty, remove the folder, otherwise break out, we're done.
 * This function leaves no empty folders after deleting a file.
 */
	$folderSegments = explode(SEPARATOR, $directory);

	for ($ndx = count($folderSegments) - 1; $ndx >= 0; $ndx--) {
		$dir = $themeRoot  . SEPARATOR .
					 $themeStyle . SEPARATOR .
					 implode(SEPARATOR, $folderSegments); // rebuilds the physical path.

		if (is_folder_empty($dir)) {
			// Folder is empty, remove it.
			rmdir($dir);
		} else {
			// Folder is not empty. Break out, we're done.
			break;
		}
		/* The following line shortens $dir by one directory level.
		 *
		 * For example: Assume the following:
		 *   $themeRoot  = "d:/inetpub/wp-content/themes"
		 *   $themeStyle = "mytheme"
		 *   $folderSegments = ['template-parts', 'header'];
		 * $dir is created as:
		 *   d:/inetpub/wp-content/themes/mytheme/template-parts/header
		 *
		 * Removing the last element of the $folderSegments array, removes one directory level.
		 * So the next time through the loop, $dir is created as:
		 *   d:/inetpub/wp-content/themes/mytheme/template-parts
		 *
		 */
		unset($folderSegments[count($folderSegments)-1]);
	}

}
function displayError($heading, $message) {
	// Dismiss box lures the user to believe that's how you close the error box. But really, the user
	// can click anywhere in the message box and it will close.
	echo "<div name='errorMessageBox' class='errorMessageBox' onclick='javascript:killMe(this);'>";
	echo "<p id='errorMessageHeader'>$heading</p><br><br>";
	echo $message;
	echo "<p id='clickBox'>Dismiss</p>";
	echo "</div>";
}

/* fixFolderSeparators():
 * ...is necessary because of Windows' problems with folder delimiters.
 * PHP is good about handling folder delimiters correctly, whether they are the
 * traditional Windows' folder delimiter of a backslash ('\') or the traditional Linux
 * folder delimiter of a forward slash ('/'). PHP scripts running on Windows will correctly
 * handle folder paths using either the forward slash or the backslash. On the other hand, PHP
 * scripts on Linux won't work with the Windows backslash. So the simple solution is to always use
 * a forward slash.
 *
 * Unfortunately, unlike its Linux counterpart, Windows barfs (technical term) on folder paths
 * that mix and match the folder delimiters. For example, a folder path with mixed delimiters
 * as the following: "d:\inetpub\wp-content\themes/mytheme/template-parts/"
 * works flawlessly in Linux, but dies miserably in Windows.
 *
 * This function changes all folder delimiters, regardless of the operating system, to forward slashes.
 * An alternate function would use a single regular expression search and replace to handle both
 * forward and backward slashes in the same *_replace statement.
 */
function fixFolderSeparators($path) {
	$path = str_replace("\\", "|+|", $path);
	$path = str_replace("/", "|+|", $path);
	$path = str_replace("|+|", SEPARATOR, $path);

	return $path;
}