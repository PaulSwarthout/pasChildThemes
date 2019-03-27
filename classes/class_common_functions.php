<?PHP
if ( ! class_exists( 'pas_cth_library_functions' ) ) {
	class pas_cth_library_functions {
		private $pluginDirectory;

		function __construct( $args ) {
			$this->pluginDirectory = ( array_key_exists( 'pluginDirectory', $args ) ? $args['pluginDirectory'] : null );
		}
		
		/*
		 * Use this function for encapsulation of "current_user_can" so that we can centralize the handling of permissions.
		 */
		function VerifyAuthorization($src = null) {
			if (current_user_can('manage_options')) {
				return;
			} else {
				if ($src != null) {
					error_log($src);
				}
				$current_user = wp_get_current_user();
				if ( $current_user->exists() ) {
					$user = [
						'username'	=>	$current_user->user_login,
						'name'		=>	$current_user->user_firstname . " " . $current_user->user_lastname,
						'email'		=>	$current_user->user_email,
						'display_name' => $current_user->display_name,
						'user_id'	=>	$current_user->ID,
							];
					error_log("Invalid access attempt by user:\n" . print_r($user, true));
					error_log("******************************");
				} else {
					error_log("....by an unknown user");
				}

				wp_die("You are not authorized to access this page");
			}
		}

		function isDefined($v, $default = false) {
			if (defined($v)) {
				return ['status' => true, 'result' => constant($v)];
			} else {
				return ['status' => false, 'result' => $default];
			}
		}

		function DemoMode() {
			$userlogin = wp_get_current_user()->user_login;
			if (! defined("DEMO_MODE") ) {
				return "manage_options";
			} else {
				if (defined("DEMO_MODE") && strtolower($userlogin) == "demo") {
					return constant("DEMO_MODE");
				} else {
					return "manage_options";
				}
			}
		}
		function demo_mode_no_profile_access() {
			if (wp_get_current_user()->user_login !== "demo") return '';

			if (strpos ($_SERVER ['REQUEST_URI'] , 'wp-admin/profile.php' )){
				wp_redirect(get_option('siteurl') . "/wp-admin");
				exit;
			}
		}

		/*
		 * pas_cth_isWin( ) Are we running on a Windows server ( true ) or not ( false ).
		 * While this function isn't really needed since PHP_OS is available everywhere,
		 * from a self-documenting code perspective:
		 * if ( pas_cth_isWin( ) ) {}
		 * is far more readable than:
		 * if ( strtoupper( substr( PHP_OS, 0, 3 ) ) ) {}
		 * is.
		 * Also, if PHP ever changes the contents of PHP_OS, then we only need to change the plugin
		 * in one place.
		 */
		function isWin( ) {
			return ( "WIN" === strtoupper( substr( PHP_OS, 0, 3 ) ) ? true : false );
		}

		/* pas_cth_areFilesIdentical( ) compares two files: $a and $b. It returns true if they are identical.
		 * false otherwise.
		 * It is more efficient to load small chunks of files and look for inequality in each, than it
		 * is to load a full file and compare. It is also much more efficient to label the files as
		 * not identical if their file sizes differ.
		 */
		function areFilesIdentical( $a, $b, $blocksize = 512 ) {
			if ( is_dir( $a ) || is_dir( $b ) ) {
				$msg = "Expected 2 files.<br>" .
					 "At least one was a directory.<br><br>" .
					 "File1: $a<br><br>" .
					 "File2: $b<br><br>" .
					 "Aborting....";
				$this->displayError( "FILE ERROR", $msg );
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
			if( filesize( $a ) !== filesize( $b ) ) {
				return false;
			}

			// Check if content is different
			$ah = fopen( $a, 'rb' );
			$bh = fopen( $b, 'rb' );

			if ( $ah === false || $bh === false ) {
				$msg = "File1: " . esc_html( $a ) . "<br>File2: " . esc_html( $b ) . "<br>" .
					 "Unable to open one or both of the files listed above. <br><br>Aborting....";
				$this->displayError( "FILE ERROR", $msg );
				// Should never be here. Checks for file_exists( ) above should prevent this.
				unset( $msg );
				exit;
			}

			$result = true;
			while( ! feof( $ah ) ) {
				if( fread( $ah, $blocksize ) != fread( $bh, $blocksize ) ) {
					$result = false;
					break;
				}
			}

			fclose( $ah );
			fclose( $bh );

			return $result;
		}
		/* pas_cth_fileCount( ) returns the number of items in the specified folder.
		 * In Windows, there will always be a '.' and '..' folder listed. This function ignores them,
		 * if they exist. Subfolders are counted as items.
		 */
		function fileCount( $dir ) {
			$files = scandir( $dir );

			/* Consider removing these two lines and just subtracting 2 from the count( ) result
			 * before returning. This will work for Windows, but what about the other operating systems?
			 * We could do a switch statement here instead, based on PHP_OS. That would eliminate the
			 * array scan times 2, which should improve the performance of this function.
			 */
			unset( $files[array_search( '.', $files, true )] );
			unset( $files[array_search( '..', $files, true )] );

			return count( $files );
		}
		/*
		 * As its name implies, pas_cth_isFolderEmpty( ) looks at the specified $dir and
		 * returns true if the folder is empty or false otherwise.
		 */
		function isFolderEmpty( $dir ) {
			return ( 0 === $this->fileCount( $dir ) ? true : false );
		}
		/*
		 * pas_cth_killChildFile( ) removes the specified child theme file from the child theme.
		 * Additionally, it reviews each subfolder in the path from the folder the file was in
		 * backwards ( leaf to root on the folder tree ). Any folders left as empty folders by
		 * the deletion of the file, or subsequent empty folders, will be removed.
		*/
		function killChildFile( $args ) {
			$activeThemeInfo = $args['activeThemeInfo'];
			$directory		 = $args['directory'];			 // Path within the theme
			$childFile		 = $args['file']; // Which file are we deleting.

			$themeRoot = $activeThemeInfo->childThemeRoot; // physical path from system root.
			$themeStyle = $activeThemeInfo->childStylesheet;

			$fileToDelete = $activeThemeInfo->childThemeRoot	. PAS_CTH_SEPARATOR .
							$activeThemeInfo->childStylesheet	. PAS_CTH_SEPARATOR .
							$directory							. PAS_CTH_SEPARATOR .
							$childFile;

			unlink( $fileToDelete );

		/* Walk the folder tree backwards, from leaf to root
		 * If each successive folder is empty, remove the folder, otherwise break out, we're done.
		 * This function leaves no empty folders after deleting a file.
		 */
			$folderSegments = explode( PAS_CTH_SEPARATOR, $directory );

			for ( $ndx = count( $folderSegments ) - 1; $ndx >= 0; $ndx-- ) {
				$dir =	$activeThemeInfo->childThemeRoot  . PAS_CTH_SEPARATOR .
						$activeThemeInfo->childStylesheet . PAS_CTH_SEPARATOR .
						implode( PAS_CTH_SEPARATOR, $folderSegments ); // rebuilds the physical path.

				if ( $this->isFolderEmpty( $dir ) ) {
					// Folder is empty, remove it.
					rmdir( $dir );
				} else {
					// Folder is not empty. Break out, we're done.
					break;
				}
				/* The following line shortens $dir by one directory level.
				 *
				 * For example: Assume the following:
				 * $themeRoot = "d:/inetpub/wp-content/themes"
				 * $themeStyle = "mytheme"
				 * $folderSegments = ['template-parts', 'header'];
				 * $dir is created as:
				 * d:/inetpub/wp-content/themes/mytheme/template-parts/header
				 *
				 * Removing the last element of the $folderSegments array, removes one directory level.
				 * So the next time through the loop, $dir is created as:
				 * d:/inetpub/wp-content/themes/mytheme/template-parts
				 *
				 */
				unset( $folderSegments[count( $folderSegments )-1] );
			}

		}
		/*
		 * pas_cth_displayError( ) This function guarantees that all error output has the same look and feel.
		 * When called from within a function called via a Javascript AJAX call, the Javascript function
		 * that called it, will display the output within the xmlhttp.onreadystatechange script.
		 */
		function displayError( $heading, $message ) {
			// Dismiss box lures the user to believe that's how you close the error box.
			// But really, the user can click anywhere in the message box and it will close.
			echo "<div name='errorMessageBox' ";
			echo " class='errorMessageBox' >";
//			echo " onclick='javascript:pas_cth_js_killMe( this );'>";
			echo "<p id='errorMessageHeader'>" . esc_html( $heading ) . "</p><br><br>";
			echo $message;
			echo "<p id='dismissBox' onclick='javascript:pas_cth_js_killMe( document.getElementsByName(\"errorMessageBox\")[0] );'>Dismiss</p>";
			echo "</div>";
		}

		/* pas_cth_fixFolderSeparators( ):
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
		 * 'd:\inetpub\wp-content\themes/mytheme/template-parts/'
		 * works flawlessly in Linux, but dies miserably in Windows.
		 *
		 * This function changes all folder delimiters, regardless of the operating system, to forward slashes.
		 *
		 * An alternate function would use a single regular expression search and replace to handle both
		 * forward and backward slashes in a single preg_replace( ) statement.
		 * Unfortunately, the search pattern along with the search delimiter got preg_replace
		 * all confused.
		 *
		 * For preg_replace( ) the search parameter would have to be something like this:
		 * "/[\\/]+/"
		 * But PHP would interpret the '/' character inside the square brackets as the end
		 * of the search string, and PHP would barf on the rest of the search string
		 * and throw a fatal error. Adding a '\' ahead of the '/' character ( as in '\/' )
		 * didn't help. PHP wouldn't throw an error, but it wouldn't find and replace the folder
		 * delimiters either.
		 */
		function fixFolderSeparators( $path ) {
			$path = str_replace( "\\", "|+|", $path );
			$path = str_replace( "/", "|+|", $path );
			$path = str_replace( "|+|", PAS_CTH_SEPARATOR, $path );

			return $path;
		}
		/* Converts a hex string of colors such as: #AABBCC to an array of decimal values.
		 * For example: an input of: #2AC4D2 would return:
		 * ['red'=>42, 'green'=>196, 'blue'=> 210]
		 */
		function getColors( $hexCode ) {
			// Strip '#' from the front, if it exists
			// This way, the function works, whether or not the '#' is prepended to the input.
			$hexCode	= ( substr( $hexCode, 0, 1 ) === "#" ? substr( $hexCode, 1 ) : $hexCode );
			$redHex		= substr( $hexCode, 0, 2 );
			$greenHex	= substr( $hexCode, 2, 2 );
			$blueHex	= substr( $hexCode, 4, 2 );


			return	[
						'red'			=> hexdec( $redHex ),
						'green'			=> hexdec( $greenHex ),
						'blue'			=> hexdec( $blueHex ),
						'redColor'		=> '#' . $redHex . "0000",
						'greenColor'	=> '#00' . $greenHex . "00",
						'blueColor'		=> '#0000' . $blueHex,
						'color'			=> "rgba( " . hexdec( $redHex ) . ", " . hexdec( $greenHex ) . ", " . hexdec( $blueHex ) . ", 1 )",
					];
		}
		function getSize( $item ) {
			/* imagettfbox( ) returns an array of indices representing the x and y coordinates for
			 * each of the 4 corners of an imaginary box that bounds the $item. The definition of
			 * what each indice of $boundingBox represents may be found here:
			 * http://php.net/manual/en/function.imagettfbbox.php
			 */
			if ( ! file_exists( $item['fontName'] ) ) {
				$font = unserialize( PAS_CTH_DEFAULT_FONT );
				$item['fontName'] = $this->pluginDirectory['path'] . "assets/fonts/" . $font['fontFile-base'] . ".ttf";
				update_option( 'pas_cth_font', $font );
			}
			$boundingBox = imagettfbbox( $item['fontSize'], 0, $item['fontName'], $item['string'] );
			$width = abs( $boundingBox[2] - $boundingBox[0] );
			$height = abs( $boundingBox[1] - $boundingBox[7] );

			return ['width'=>$width, 'height'=>$height];
		}
		function sampleIsTooBig( $args ) {
			$imageSize = $args['imageSize'];
			$sampleSize = $args['sampleSize'];
			$result = false;

			if ( ( $imageSize['width'] < $sampleSize['width'] )	||
				( $imageSize['height'] < $sampleSize['height'] ) )		{
				$result = true;
			}

			return $result;

		}
		function sampleIsTooSmall( $args ) {
			$imageSize = $args['imageSize'];
			$sampleSize = $args['sampleSize'];
			$result = false;
			if ( ( $sampleSize['width'] < $imageSize['width'] )	 &&
				( $sampleSize['height'] < $imageSize['height'] ) )		{
				$result = true;
			}

			return $result;
		}
		/*
		 * Calculate the largest font size that will fit in the $imgWidth x $imgHeight space.
		 */
		function getMaxFontSize( $args ) {
			$font			= $args['font'];
			$imageSize		= $args['imageSize'];
			$sampleText		= $args['sampleText'];
			$maxNumbLines	= ( array_key_exists( 'totalLines', $args ) ? $args['totalLines'] : 1 );
			$padding		= ( array_key_exists( 'pad', $args ) ? $args['pad'] : 0 );

			// Presumably this font size is bigger than what will fit in the space.
			// Gotta start somewhere.
			// could check it and increase if not. Then we could use a smaller starting point for less
			// iterations.
			$fontSize = 90;
			if ( $maxNumbLines > 1 ) {
				$imageSize['height'] = ( $imageSize['height'] / $maxNumbLines ) - $padding;
			}
			$sampleArgs =
				[
					'font'=>$args['font'],
					'fontSize'=>$fontSize,
					'imageSize'=>$imageSize,
					'sampleSize'=>$fontSize
				];
			// reduce the font size until it's smaller than the space.
			do {
				$fontSize -= 10;
				$size = $this->getSize( ['fontSize'=>$fontSize, 'fontName'=>$font, 'string'=>$sampleText] );
				$sampleArgs['fontSize']		= $fontSize;
				$sampleArgs['sampleSize']	= $size;
			} while ( $this->sampleIsTooBig	( $sampleArgs ) );
			unset( $sampleArgs );

			// increase the font size until it's bigger than the space
			$sampleArgs =
				[
					'font'=>$args['font'],
					'fontSize'=>$fontSize,
					'imageSize'=>$imageSize,
					'sampleSize'=>$size
				];
			while ( $this->sampleIsTooSmall( $sampleArgs ) ) {
				$fontSize += 2;
				$size = $this->getSize( ['fontSize'=>$fontSize, 'fontName'=>$font, 'string'=>$sampleText] );
				$sampleArgs['fontSize'] = $fontSize;
				$sampleArgs['sampleSize'] = $size;
			}
			// At this point, we are 1 iteration of the above loop too big. Subtract 2 point sizes
			// And we have the largest font size that will fit in the 300 x 50 sample font space.
			$fontSize -= 2;
			$size = $this->getSize( ['fontSize'=>$fontSize, 'fontName'=>$font, 'string'=>$sampleText] );
			$rtn =	[
						'maxFontSize'	=>	$fontSize,
						'sampleWidth'	=>	$size['width'],
						'sampleHeight'	=>	$size['height'],
						'imageSize'		=>	$imageSize,
						'font'			=>	$font
					];
			return $rtn;
		}

		function create_sample_fonts( ) {
			$this->fontList = $this->loadAvailableFonts( );
		}
		function loadFonts( ) {
			return get_option( 'pas_cth_fontList', [] );
		}

		function setDelimiters($path, $delimiter = PAS_CTH_SEPARATOR) {
			$path = str_replace( "\\", "|+|", $path );
			$path = str_replace( "/", "|+|", $path );
			$path = str_replace( "|+|", $delimiter, $path );
			return $path;
		}
		function dirUp($path, $levels = 1) {
			$delimiter = PAS_CTH_SEPARATOR;
			$folders = explode($delimiter, $path);
			for ($ndx = 0; $ndx < $levels; $ndx++) {
				unset($folders[count($folders) - 1] );
			}
			return implode($delimiter, $folders);
		}
		/*
		 * Sometimes it's helpful to be able to see the HTML code in a 'view-source' not all crammed together.
		 * This function appends a (windows) carriage return / line-feed or (Linux) line-feed
		 * to the end of (php) echo commands such that when debugging, the view-source html code is more readable.
		 * It is preferrable to only call this function once, so store it in a local variable.
		 */
		function crlf() {
			if (constant('WP_DEBUG')) {
				if ($this->isWin()) {
					return "\r\n";
				} else {
					return "\n";
				}
			} else {
				return "";
			}
		}
		function hexDump($str) {
			$blockArray = str_split($str, 4);
			$loopCount = 0;
			echo "<div style='font-family:monospace ! important;font-size:12pt ! important;'>";

			while (count($blockArray) > 0) {
				$loopCount++;
				$arrayOfLines = array_splice($blockArray, 0, 8);
				$char = '';

				$outputLine = "<div id='hexDump' style='border:solid 1pt red;'>";

				foreach ($arrayOfLines as $line) {
					$arrayOfCharacters = str_split($line, 1);
					$out = '';
					for ($ndx = 0; $ndx < count($arrayOfCharacters); $ndx = $ndx + 4) {
						$out .= $this->digits((string) dechex(ord($arrayOfCharacters[$ndx])), 2);
						$char .= $arrayOfCharacters[$ndx];

						$out .= $this->digits((string) dechex(ord($arrayOfCharacters[$ndx+1])), 2);
						$char .= $arrayOfCharacters[$ndx+1];

						$out .= $this->digits((string) dechex(ord($arrayOfCharacters[$ndx+2])), 2);
						$char .= $arrayOfCharacters[$ndx+2];

						$out .= $this->digits((string) dechex(ord($arrayOfCharacters[$ndx+3])), 2);
						$char .= $arrayOfCharacters[$ndx+3];

						$outputLine .= strtoupper($out) . "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
					}
				}
				$char = preg_replace("[^\x01-\x19\x80-\xFF. ]", ".", $char);
				$outputLine .= "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;" . $char . "</div>";
				echo $outputLine;
				$outputLine = "";
				$char = '';
			}
			echo "</div>";
		}

		function digits($v, $n) {
			while (strlen($v) < $n) {
				$v = "0" . $v;
			}
			return $v;
		}
	}
}
