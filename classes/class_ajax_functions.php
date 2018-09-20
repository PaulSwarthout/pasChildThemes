<?PHP
/*
 * pas_cth_selectFile() is called as an AJAX call from the Javascript function selectFile().
 * The Javascript function selectFile() is activated by an onclick event from the themes' filelists
 * when the user clicks on a file name.
 */
if ( ! class_exists( 'pas_cth_AJAXFunctions' ) ) {
	class pas_cth_AJAXFunctions {
		private $pluginDirectory;
		private $pluginName;
		private $pluginFolder;
		public  $activeThemeInfo;
		private $colorPicker;
		private $libraryFunctions;

		function __construct( $args ) {
			$this->pluginDirectory	= $args['pluginDirectory'];
			$this->pluginName		= $args['pluginName'];
			$this->pluginFolder		= $args['pluginFolder'];
			$this->activeThemeInfo	= $args['activeThemeInfo'];
			$this->colorPicker		= $args['colorPicker'];
			$this->libraryFunctions = $args['libraryFunctions'];
		}
		// To aid with debugging, when WP_DEBUG is true, this function displays a message code
		// on the message box in the lower right corner.
		function displayMessageID( $msgID ) {
			if ( constant( 'WP_DEBUG' ) ) {
				echo "<p class='mID'>" . $msgID . "</p>";
			}
		}
		function selectFile() {
			if ( ! current_user_can( 'manage_options' ) )  {
				wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
			}

			// Posted from Javascript AJAX call
			$inputs =	[
							'directory' => sanitize_text_field( $_POST['directory'] ),
							'file'		=> sanitize_file_name ( $_POST['fileName'] ),
							'themeType' => sanitize_text_field( $_POST['themeType'] )
						];

			/* Removes the stylesheet folder
			 * For example:
			 *   /mytheme/assets/images/boxo.png
			 * would become:
			 *   /assets/images/boxo.png
			 */
			$folderSegments = explode( PAS_CTH_SEPARATOR, $inputs['directory'] );
			unset( $folderSegments[0] ); // Strip theme stylesheet folder from the beginning.
			$directory = implode( PAS_CTH_SEPARATOR, $folderSegments );

			$lowerCaseFile = strtolower( $inputs['file'] );
			if ( ( "style.css"		=== $lowerCaseFile && 0 === strlen( $directory ) ) ||
				( "functions.php"	=== $lowerCaseFile && 0 === strlen( $directory ) ) ) {
				$msg = "You cannot delete or overwrite the current theme's "
					. "'style.css' or 'functions.php' files. "
					. "Your theme is defined, as a minimum, by these two files. "
					. "Overwriting or deleting either one could cause your WordPress website to "
					. "crash."
					. "<br><br>"
					. "If you must delete or overwrite the '"
					. esc_html( $inputs['file'] ) . "' file:"
					. "<ol class='errorList'>"
					. "<li>Change your active theme to something else.</li>"
					. "<li>Use your favorite FTP client to delete or overwrite the '"
					. esc_html( $inputs['file'] ) . "' file.</li>"
					. "<li>Attempt to change your theme back to the original theme.</li>"
					. "</ol>"
					. "You cannot use this plugin to forcibly crash your theme.";
				$this->libraryFunctions->displayError( "Cannot Delete or Overwrite File", $msg );
				unset( $msg );
				return false;
			}
			unset( $lowerCaseFile );

			$jsdata =	[
							'directory'			=> $directory,
							'file'				=> $inputs['file'],
							'themeType'			=> $inputs['themeType'],
							'childStylesheet'	=> $this->activeThemeInfo->childStylesheet,
							'templateStylesheet'=> $this->activeThemeInfo->templateStylesheet,
							'delete_action'		=> 'verifyRemoveFile',
							'copy_action'		=> 'verifyCopyFile'
						];
			$jsdata = json_encode( $jsdata );
			/* MENU:{ specifies to the javascript routine that will be processing the
			 * xmlhttp.responseText, what kind of output to use.
			 */
			echo "MENU:{";
			echo "<p class='warningHeading'>What do you want to do?</p><br>";

			echo "<p id='fileLabel'>Selected File:";
			echo "<span id='fileDisplay'>";
			echo esc_html( $inputs['directory'] );
			echo PAS_CTH_SEPARATOR . esc_html( $inputs['file'] );
			echo "</span></p><br>";

			switch ( $inputs['themeType'] ) {
				case PAS_CTH_CHILDTHEME:
					echo "<input data-jsdata='" . esc_attr( $jsdata ) . "' ";
					echo "       type='button' ";
					echo "       value='Remove File from Child' ";
					echo "       class='wideBlueButton' ";
					echo "       onclick='javascript:pas_cth_js_removeChildFile( this );'>";
					echo "<br><br>";
					break;
				case PAS_CTH_TEMPLATETHEME:
					echo "<input data-jsdata='" . esc_attr( $jsdata ) . "' ";
					echo "       type='button' ";
					echo "       value='Copy File to Child' ";
					echo "       class='wideBlueButton' ";
					echo "       onclick='javascript:pas_cth_js_copyTemplateFile( this );'>";
					echo "<br><br>";
					break;
				default:
					echo esc_html( "Unknown \$themeType: [ " . $inputs['themeType'] . " ]<br>" );
					break;
			}
			echo "<input data-jsdata='" . esc_attr( $jsdata ) . "' ";
			echo "       type='button' ";
			echo "       value='Edit File' ";
			echo "       class='wideBlueButton' ";
			echo "       onclick='javascript:pas_cth_js_editFile( this );'>";
			echo "<p id='clickBox'>Dismiss</p>";
			echo $this->displayMessageID( "sf" ); // only displayed when WP_DEBUG = true
			echo "}";
		}
		/*
		 * pas_cth_verifyRemoveFile()
		 *   is called from the Javascript function removeChildFile() in 'js/pasChildThemes.js'
		 */
		function verifyRemoveFile() {
			// Posted from Javascript AJAX call
			$inputs = [
						'childStylesheet'	=> sanitize_text_field( $_POST['childStylesheet'] ),
						'templateStylesheet'=> sanitize_text_field( $_POST['templateStylesheet'] ),
						'directory'			=> sanitize_text_field( $_POST['directory'] ),
						'childFileToRemove'	=> sanitize_file_name( $_POST['childFileToRemove'] )
					];

			$childThemeFile	= $this->activeThemeInfo->childThemeRoot . PAS_CTH_SEPARATOR
							. $inputs['childStylesheet'] . PAS_CTH_SEPARATOR
							. $inputs['directory'] . PAS_CTH_SEPARATOR
							. $inputs['childFileToRemove'];

			$templateThemeFile	= $this->activeThemeInfo->templateThemeRoot . PAS_CTH_SEPARATOR
								. $inputs['templateStylesheet'] . PAS_CTH_SEPARATOR
								. $inputs['directory'] . PAS_CTH_SEPARATOR
								. $inputs['childFileToRemove'];

			if ( $this->libraryFunctions->areFilesIdentical( $childThemeFile, $templateThemeFile ) ) {
				/* deletes the specified file and removes any folders that are now empty because
				 * the file was deleted or an empty subfolder was deleted.
				 */
				$args = [
							'stylesheet'		=> $inputs['childStylesheet'],
							'directory'			=> $inputs['directory'],
							'childFileToRemove' => $inputs['childFileToRemove'],
							'activeThemeInfo'	=> $this->activeThemeInfo
						];
				$this->libraryFunctions->killChildFile( $args );
			} else {
				// Files are not identical. Child file is different than the original template file.
				// This might be because the user modified the file, but it could also be,
				// that the template file was changed due to an update.

				$childStylesheet = $this->activeThemeInfo->childStylesheet;
				$templateStylesheet = $this->activeThemeInfo->templateStylesheet;

				$JSData = json_encode( ['childStylesheet'	=>$inputs['childStylesheet'],
										'directory'			=>$inputs['directory'],
										'childFileToRemove'	=>$inputs['childFileToRemove'],
										'action'			=>'deleteFile'] );

				echo "<p class='warningHeading'>Files are Different</p><br><br>";
				echo "Child&nbsp;&nbsp;&nbsp;: <u>"
					 . esc_html( $inputs['childStylesheet'] ) . PAS_CTH_SEPARATOR
					 . esc_html( $inputs['directory'] ) . PAS_CTH_SEPARATOR
					 . esc_html( $inputs['childFileToRemove'] ) . "</u><br>";

				echo "Template: <u>" . esc_html( $inputs['templateStylesheet'] ) . PAS_CTH_SEPARATOR
					 . esc_html( $inputs['directory'] ) . PAS_CTH_SEPARATOR
					 . esc_html( $inputs['childFileToRemove'] ) . "</u><br><br>";

				echo "There are 2 possible reasons for this:<br>";
				echo "<ol type='1'>";
				echo "<li>";
				echo "You have modified the child theme since you copied it to the child theme.";
				echo "</li>";
				echo "<li>";
				echo "You have updated the template theme since you copied the file to the ";
				echo "child theme.";
				echo "</li>";
				echo "</ol>";

				echo "<span class='emphasize'>";
				echo "If you proceed, you will LOSE any changes or differences.</span><br><br>";
				echo "Do you want to proceed and <u>DELETE</u> the file from your child theme?";
				echo "<br><br>";
				echo "<div class='questionPrompt'>";
				echo "<INPUT data-jsdata='" . esc_html( $JSData ) . "' " .
					   " type='button' value='DELETE FILE' class='blueButton' " .
					   " onclick='javascript:pas_cth_js_deleteChildFile( this );'>";
				echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
				echo "<INPUT type='button' "
				   . "       value='Cancel' "
				   . "       class='blueButton' "
				   . "       onclick='javascript:pas_cth_js_cancelDeleteChild( this );'>";

				echo $this->displayMessageID( "vrf" );
				echo "</div>";
			}
		}
		/*
		 * pas_cth_verifyCopyFile()
		 *   is called from the Javascript function copyTemplateFile() in 'js/pasChildThemes.js'
		 */
		function verifyCopyFile() {
			$inputs =[
						'childStylesheet'	=> sanitize_text_field( $_POST['childStylesheet'] ),
						'directory'			=> sanitize_text_field( $_POST['directory'] ),
						'templateFileToCopy'=> sanitize_file_name( $_POST['templateFileToCopy'] ),
						'templateStylesheet'=> sanitize_text_field( $_POST['templateStylesheet'] )
					 ];

			$childThemeFile	=	$this->activeThemeInfo->childThemeRoot .
								PAS_CTH_SEPARATOR .
								$inputs['childStylesheet'] .
								PAS_CTH_SEPARATOR .
								$inputs['directory'] .
								PAS_CTH_SEPARATOR .
								$inputs['templateFileToCopy'];

			$templateThemeFile	=	$this->activeThemeInfo->templateThemeRoot .
									PAS_CTH_SEPARATOR .
									$inputs['templateStylesheet'] .
									PAS_CTH_SEPARATOR .
									$inputs['directory'] .
									PAS_CTH_SEPARATOR .
									$inputs['templateFileToCopy'];
		/* Tricky code:
		 * We want to pass the same arguments that were passed in, almost.
		 * 'action' gets modified and
		 * 'childThemeRoot', and 'templateThemeRoot' get added.
		 */
			$args = [
				'childThemeRoot'	=> $this->activeThemeInfo->childThemeRoot,
				'templateThemeRoot' => $this->activeThemeInfo->templateThemeRoot,
				'action'			=> 'copyFile'
							];
			// We set $args['action'] above. If we do not unset( $_POST['action'] ) then the foreach
			// loop below will overwrite it.
			unset( $_POST['action'] );

			/* At first glance, there is an error in the following loop.
			 * We loop on $_POST, but use the $inputs array.
			 * This was intentional. This is not an error.
			 * Ordinarily, we would expect the 2nd line to be:
			 *    $args[key] = $value;
			 * But $value is unsanitized. $inputs[$key] is the sanitized version of $value.
			 */
			foreach ( $_POST as $key => $value ) {
				$args[$key] = $inputs[$key];
			}

			/* If file doesn't exist. Copy it. We're done.
			 * If the file does exist, and the child theme file and the template theme file
			 *   are already identical. We're done. No need to copy it.
			 * If the file does exist, and the files are not identical,
			 * prompt the user to overwrite.
			 */
			if ( !  file_exists( $childThemeFile ) ) {
				$this->copyFile( $args );
			} elseif ( $this->libraryFunctions->areFilesIdentical( $childThemeFile, $templateThemeFile ) ) {
//				$this->copyFile( $args ); // No need to actually copy it.
										// File exists and files are identical
			} else {
				// File exists and the files are not identical, prompt before overwriting.
				$JSData = json_encode( $args );

				echo "<p class='warningHeading'>Files are Different</p><br><br>";

				echo "Child Theme File: <u>" .
					 esc_html( $inputs['childStylesheet'] )	. PAS_CTH_SEPARATOR .
					 esc_html( $inputs['directory'] )			. PAS_CTH_SEPARATOR .
					 esc_html( $inputs['templateFileToCopy'] ). "</u><br>";

				echo "Template Theme File: <u>" .
					 esc_html( $inputs['templateStylesheet'] ) . PAS_CTH_SEPARATOR .
					 esc_html( $inputs['directory'] )			 . PAS_CTH_SEPARATOR .
					 esc_html( $inputs['templateFileToCopy'] ) . "</u><br><br>";

				echo "There are 2 possible reasons for this:<br>";
				echo "<ol type='1'>";
				echo "<li>";
				echo "You have modified the child theme since you copied it ";
				echo "to the child theme.";
				echo "</li>";
				echo "<li>";
				echo "You have updated the template theme since you copied the file ";
				echo "to the child theme.";
				echo "</li>";
				echo "</ol>";

				echo "<span class='emphasize'>";
				echo "If you proceed, you will LOSE any differences between the two files.";
				echo "</span><br><br>";
				echo "Do you want to proceed and <u>OVERWRITE</u> the file from your child theme?";
				echo "<br><br>";
				echo "<div class='questionPrompt'>";
				echo "<INPUT data-jsdata='" . esc_html( $JSData ) . "' " .
					 " type='button' value='OVERWRITE FILE' class='blueButton' " .
					 " onclick='javascript:pas_cth_js_overwriteFile( this );'>";
				echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
				echo "<INPUT type='button' "
				   . "       value='Cancel' "
				   . "       class='blueButton' "
				   . "       onclick='javascript:pas_cth_js_cancelDeleteChild( this );'>";
				echo $this->displayMessageID( "vcf" );
				echo "</div>";
			}
		}
		/*
		 * pas_cth_copyFile()
		 *   is called from the Javascript function overwriteFile() in 'js/pasChildThemes.js' AND
		 *   from pas_cth_verifyCopyFile() when the child theme file does not exist.
		 * If the child theme file does not exist, $args are passed in, instead of
		 * coming as a AJAX POST.
		 * If the folders to the new child theme file do not exist: create them.
		 */
		function copyFile( $args = null ) {
			if ( null != $args ) {
				$childThemeRoot		= $args['childThemeRoot'];
				$childStylesheet	= $args['childStylesheet'];
				$templateThemeRoot	= $args['templateThemeRoot'];
				$directory			= $args['directory'];
				$templateStylesheet = $args['templateStylesheet'];
				$fileToCopy			= $args['templateFileToCopy'];
			} else {
				$childThemeRoot		= sanitize_text_field( $_POST['childThemeRoot'] );
				$childStylesheet	= sanitize_text_field( $_POST['childStylesheet'] );
				$templateThemeRoot	= sanitize_text_field( $_POST['templateThemeRoot'] );
				$directory			= sanitize_text_field( $_POST['directory'] );
				$templateStylesheet = sanitize_text_field( $_POST['templateStylesheet'] );
				$fileToCopy			= sanitize_file_name( $_POST['templateFileToCopy'] );
			}

			$dir = $childThemeRoot . PAS_CTH_SEPARATOR . $childStylesheet . PAS_CTH_SEPARATOR;
			$folderSegments  = explode( PAS_CTH_SEPARATOR, $directory );

			for ( $ndx = 0; $ndx < count( $folderSegments ); $ndx++ ) {
				$dir .= PAS_CTH_SEPARATOR . $folderSegments[$ndx];
				if ( !  file_exists( $dir ) ) {
					mkdir( $dir );
				}
			}

			$sourceFile =	$templateThemeRoot	. PAS_CTH_SEPARATOR .
							$templateStylesheet . PAS_CTH_SEPARATOR .
							$directory			. PAS_CTH_SEPARATOR .
							$fileToCopy;

			$targetFile =	$childThemeRoot		. PAS_CTH_SEPARATOR .
							$childStylesheet	. PAS_CTH_SEPARATOR .
							$directory			. PAS_CTH_SEPARATOR .
							$fileToCopy;

			$result = copy( $sourceFile, $targetFile );
			if ( !  $result ) {
				echo "Failed to copy<br>$sourceFile<hr>to<hr>$targetFile<br>";
			}
		}

		/*
		 * pas_cth_deleteFile()
		 *   is called from the Javascript function deleteChildFile() in 'js/pasChildThemes.js'
		 * Delete the file and any empty folders made empty by the deletion of the file
		 * or subsequent subfolders.
		 */
		function deleteFile() {
			$args = [
						'stylesheet'		=> sanitize_text_field( $_POST['stylesheet'] ),
						'directory'			=> sanitize_text_field( $_POST['directory'] ),
						'childFileToRemove'	=> sanitize_file_name( $_POST['childFileToRemove'] ),
						'activeThemeInfo'   => $this->activeThemeInfo
					];
			$this->libraryFunctions->killChildFile( $args );
		}

		/* createChildTheme() is called from the Javascript function
		 * pas_cth_js_createChildTheme() in 'js/pasChildThemes.js'
		 */
		function createChildTheme() {
			$err = 0;
			$inputs =	[
							'childThemeName'=> sanitize_text_field( $_POST['childThemeName'] ),
							'templateTheme' => sanitize_text_field( $_POST['templateTheme'] ),
							'description'   => sanitize_textarea_field( $_POST['description'] ),
							'authorName'		=> sanitize_text_field( $_POST['authorName'] ),
							'authorURI'			=> sanitize_text_field( $_POST['authorURI'] ),
							'version'		=> sanitize_text_field( $_POST['version'] )
						];

			if ( 0 === strlen( trim( $inputs['childThemeName'] ) ) ) {
				$this->libraryFunctions->displayError( "Notice",
									 "Child Theme Name cannot be blank." );
				$err++;
			}

			if ( 0 === strlen( trim( $inputs['templateTheme'] ) ) ) {
				$this->libraryFunctions->displayError( "Notice",
									 "Template Theme is required." );
				$err++;
			}

			if ( 0 === strlen( trim( $inputs['description'] ) ) ) {
				$inputs['description'] = $inputs['childThemeName'] .
										 " is a child theme of " .
										 $inputs['templateTheme'];
			}

			if ( 0 === strlen( trim( $inputs['authorName'] ) ) ) {
				$inputs['authorName'] = PAS_CTH_MYNAME;
			}

			if ( 0 === strlen( trim( $inputs['authorURI'] ) ) ) {
				$inputs['authorURI'] = PAS_CTH_MYURL;
			}

			if (0 !== $err) {
				return;
			}

			// Create the stylesheet folder
			$themeRoot = $this->libraryFunctions->fixFolderSeparators( get_theme_root() );
			$childThemeName = $inputs['childThemeName'];
			// New child theme folder will be the specified name with no whitespace, in lower case.
			$childThemeStylesheet =
				strtolower( preg_replace( "/\s/", "", $inputs['childThemeName'] ) );

			// Remove any characters that are not letters or numbers.
			$childThemeStylesheet = preg_replace( "/[^a-z0-9]/", "", $childThemeStylesheet );
			$childThemePath = $themeRoot . PAS_CTH_SEPARATOR . $childThemeStylesheet;

			if ( file_exists( $childThemePath ) ) {
				$this->libraryFunctions->displayError(
					"ERROR",
					"Child theme: <span style='text-decoration:double underline;'>"
					. esc_html( $inputs['childThemeName'] )
					. "</span> already exists" );
				return;
			}

			mkdir( $childThemePath );

			// Create the style.css file for the child theme.
			$styleFile = fopen( $childThemePath . PAS_CTH_SEPARATOR . "style.css", "w" );
			$newlineChar = "\n";
/*
			$inputs =	[
							'themeURI'		=> sanitize_text_field( $_POST['themeURI'] ),
							'description'	=> sanitize_textarea_field( $_POST['description'] ),
							'authorName'	=> sanitize_text_field( $_POST['authorName'] ),
							'authorURI'		=> sanitize_text_field( $_POST['authorURI'] ),
							'templateTheme' => sanitize_text_field( $_POST['templateTheme'] ),
							'version'		=> sanitize_text_field( $_POST['version'] )
						];
*/
			fwrite( $styleFile, "/*" . $newlineChar );
			fwrite( $styleFile, " Theme Name:  " . $childThemeName		. $newlineChar );
			fwrite( $styleFile, " Theme URI:   " . $inputs['themeURI']	. $newlineChar );
			fwrite( $styleFile, " Description: " . $inputs['description']. $newlineChar );
			fwrite( $styleFile, " Author:      " . $inputs['authorName']	. $newlineChar );
			fwrite( $styleFile, " Author URI:  " . $inputs['authorURI']	. $newlineChar );
			fwrite( $styleFile, " Template:    " . $inputs['templateTheme']. $newlineChar );
			fwrite( $styleFile, " Version:     " . $inputs['version']	. $newlineChar );
			fwrite( $styleFile, "*/" . $newlineChar );
			fclose( $styleFile );

			// Create the functions.php file for the child theme. Use the wp_enqueue_style() function
			// to correctly set up the stylesheets for the child theme.
			$functionsFile = fopen( $childThemePath . PAS_CTH_SEPARATOR . "functions.php", "w" );
			fwrite( $functionsFile, "<" . "?" . "PHP" . $newlineChar );
			fwrite( $functionsFile, "add_action( 'wp_enqueue_scripts', '" . $childThemeStylesheet . "_theme_styles' );" . $newlineChar );
			fwrite( $functionsFile, "function " .
									$childThemeStylesheet .
									"_theme_styles() {" .
									$newlineChar );
			fwrite( $functionsFile, "\twp_enqueue_style( 'parent-style', " .
				                    "                    get_template_directory_uri() . " .
				                    "                    '/style.css' );" . $newlineChar );
			fwrite( $functionsFile, "\twp_enqueue_style( '" . $childThemeStylesheet . "-style', " .
				                    "dirname( __FILE__ ) . '/style.css' );" . $newlineChar );
			fwrite( $functionsFile, "}" . $newlineChar );
			fwrite( $functionsFile, "?>" );
			fclose( $functionsFile );

			// Handshake with the Javascript AJAX call that got us here.
			// When "SUCCESS:url" is returned, Javascript will redirect to the url.
			echo "SUCCESS:" . esc_url_raw( $_POST['href'] );
		}
		// Save options.
		function saveOptions() {
			$inputs =	[
							'optionName'  => sanitize_text_field( $_POST['optionName'] ),
							'optionValue' => sanitize_text_field( $_POST['optionValue'] )
						];

			update_option( "pas_cth_" . $inputs['optionName'], $inputs['optionValue'] );
		}
		function chooseColor() {
			$initialColor		= sanitize_text_field( $_POST['initialColor'] );
			$originalColorField = sanitize_text_field( $_POST['callingFieldName'] );
			$args = [
						'initialColor'		=> $initialColor,
						'callingFieldName'	=> $originalColorField
					];
			echo $this->colorPicker->getNewColor($args);
		}

		function saveFont() {
			$fontFile = trim(sanitize_text_field($_POST['fontFile-base']));
			$fontName = sanitize_text_field($_POST['fontName']);

			update_option( 'pas_cth_font', [ 'fontName'=>$fontName, 'fontFile-base'=>$fontFile ] );
		}
	}
}
