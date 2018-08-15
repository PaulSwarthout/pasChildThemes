<?PHP
/*
 * pas_cth_isWin() Are we running on a Windows server ( true ) or not ( false ).
 * While this function isn't really needed since PHP_OS is available everywhere,
 * from a self-documenting code perspective:
 *     if ( pas_cth_isWin() ) {}
 * is far more readable than:
 *     if ( strtoupper( substr( PHP_OS, 0, 3 ) ) ) {}
 * is.
 * Also, if PHP ever changes the contents of PHP_OS, then we only need to change the plugin
 * in one place.
 */
function pas_cth_isWin() {
	return ( "WIN" === strtoupper( substr( PHP_OS, 0, 3 ) ) ? true : false );
}
/* pas_cth_areFilesIdentical() compares two files: $a and $b. It returns true if they are identical.
 * false otherwise.
 * It is more efficient to load small chunks of files and look for inequality in each, than it
 * is to load a full file and compare. It is also much more efficient to label the files as
 * not identical if they're file sizes differ.
 */
function pas_cth_areFilesIdentical( $a, $b, $blocksize = 512 )
{
	if ( is_dir( $a ) || is_dir( $b ) ) {
		$msg = "Expected 2 files.<br>" .
			   "At least one was a directory.<br><br>" .
			   "File1: $a<br><br>" .
			   "File2: $b<br><br>" .
			   "Aborting....";
		pas_cth_displayError( "FILE ERROR", $msg );
		unset( $msg );
		exit;
	}
	if ( ! file_exists( $a ) ) {
		echo "FILE: $a DOES NOT EXIST";
		return false;
	}
	if ( ! file_exists( $b ) ) {
		echo "FILE: $b DOES NOT EXIST";
		return false;
	}
  // Check if filesize is different If the filesize is different, no more checking necessary.
  if( filesize( $a ) !== filesize( $b ) )
      return false;

  // Check if content is different
  $ah = fopen( $a, 'rb' );
  $bh = fopen( $b, 'rb' );

	if ( $ah === false || $bh === false ) {
		$msg = "File1: " . $a . "<br>File2: " . $b . "<br>" .
			   "Unable to open one or both of the files listed above. <br><br>Aborting....";
		pas_cth_displayError( "FILE ERROR", $msg );
		// Should never be here. Checks for file_exists() above should prevent this.
		unset( $msg );
		exit;
	}

  $result = true;
  while( ! feof( $ah ) )
  {
    if( fread( $ah, $blocksize ) != fread( $bh, $blocksize ) )
    {
      $result = false;
      break;
    }
  }

  fclose( $ah );
  fclose( $bh );

  return $result;
}
/* pas_cth_fileCount() returns the number of items in the specified folder.
 * In Windows, there will always be a '.' and '..' folder listed. This function ignores them,
 * if they exist. Subfolders are counted as items.
 */
function pas_cth_fileCount( $dir ) {
	$files = scandir( $dir );

	/* Consider removing these two lines and just subtracting 2 from the count() result
	 * before returning. This will work for Windows, but what about the other operating systems?
	 * We could do a switch statement here instead, based on PHP_OS. That would eliminate the
	 * array scan times 2, which should improve the performance of this function.
	 */
	unset( $files[array_search( '.', $files, true )] );
	unset( $files[array_search( '..', $files, true )] );

	return count( $files );
}
/*
 * As its name implies, pas_cth_isFolderEmpty() looks at the specified $dir and
 * returns true if the folder is empty or false otherwise.
 */
function pas_cth_isFolderEmpty( $dir ) {
	return ( 0 === pas_cth_fileCount( $dir ) ? true : false );
}
/*
 * pas_cth_killChildFile() removes the specified child theme file from the child theme.
 * Additionally, it reviews each subfolder in the path from the folder the file was in
 * backwards ( leaf to root on the folder tree ). Any folders left as empty folders by
 * the deletion of the file, or subsequent empty folders, will be removed.
*/
function pas_cth_killChildFile( $args ) {
	$activeThemeInfo = $args['activeThemeInfo'];
	$themeStyle			 = $args['stylesheet'];		   // Stylesheet - theme's folder name
	$directory			 = $args['directory'];			 // Path within the theme
	$childFile			 = $args['childFileToRemove'];    // Which file are we deleting.

	$themeRoot = $activeThemeInfo->childThemeRoot; // physical path from system root.

	$fileToDelete = $themeRoot	. PAS_CTH_SEPARATOR .
									$themeStyle . PAS_CTH_SEPARATOR .
									$directory	. PAS_CTH_SEPARATOR .
									$childFile;

	unlink( $fileToDelete );

/* Walk the folder tree backwards, from leaf to root
 * If each successive folder is empty, remove the folder, otherwise break out, we're done.
 * This function leaves no empty folders after deleting a file.
 */
	$folderSegments = explode( PAS_CTH_SEPARATOR, $directory );

	for ( $ndx = count( $folderSegments ) - 1; $ndx >= 0; $ndx-- ) {
		$dir = $themeRoot  . PAS_CTH_SEPARATOR .
					 $themeStyle . PAS_CTH_SEPARATOR .
					 implode( PAS_CTH_SEPARATOR, $folderSegments ); // rebuilds the physical path.

		if ( pas_cth_isFolderEmpty( $dir ) ) {
			// Folder is empty, remove it.
			rmdir( $dir );
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
		unset( $folderSegments[count( $folderSegments )-1] );
	}

}
/*
 * pas_cth_displayError() This function guarantees that all error output has the same look and feel.
 * When called from within a function called via a Javascript AJAX call, the Javascript function
 * that called it, will display the output within the xmlhttp.onreadystatechange script.
 */
function pas_cth_displayError( $heading, $message ) {
	// Dismiss box lures the user to believe that's how you close the error box.
	// But really, the user can click anywhere in the message box and it will close.
	echo "<div name='errorMessageBox' ";
	echo "     class='errorMessageBox' ";
	echo "     onclick='javascript:pas_cth_js_killMe( this );'>";
	echo "<p id='errorMessageHeader'>" . esc_html($heading) . "</p><br><br>";
	echo $message;
	echo "<p id='clickBox'>Dismiss</p>";
	echo "</div>";
}

/* pas_cth_fixFolderSeparators():
 * ...is necessary because of Windows' problems with folder delimiters.
 * PHP is good about handling folder delimiters correctly, whether they are the
 * traditional Windows' folder delimiter of a backslash ( '\' ) or the traditional Linux
 * folder delimiter of a forward slash ( '/' ). PHP scripts running on Windows will correctly
 * handle folder paths using either the forward slash or the backslash. On the other hand, PHP
 * scripts on Linux won't work with the Windows backslash. So the simple solution is to always use
 * a forward slash.
 *
 * Unfortunately, unlike its Linux counterpart, Windows barfs ( technical term ) on folder paths
 * that mix and match the folder delimiters. For example, a folder path with mixed delimiters
 * as the following:
 *      'd:\inetpub\wp-content\themes/mytheme/template-parts/'
 * works flawlessly in Linux, but dies miserably in Windows.
 *
 * This function changes all folder delimiters, regardless of the operating system, to forward slashes.
 *
 * An alternate function would use a single regular expression search and replace to handle both
 * forward and backward slashes in a single preg_replace() statement.
 * Unfortunately, the search pattern along with the search delimiter got preg_replace
 * all confused.
 *
 * For preg_replace() the search parameter would have to be something like this:
 *      "/[\\/]+/"
 * But PHP would interpret the '/' character inside the square brackets as the end
 * of the search string, and PHP would barf on the rest of the search string
 * and throw a fatal error. Adding a '\' ahead of the '/' character ( as in '\/' )
 * didn't help. PHP wouldn't throw an error, but it wouldn't find and replace the folder
 * delimiters either.
 */
function pas_cth_fixFolderSeparators( $path ) {
	$path = str_replace( "\\", "|+|", $path );
	$path = str_replace( "/", "|+|", $path );
	$path = str_replace( "|+|", PAS_CTH_SEPARATOR, $path );

	return $path;
}