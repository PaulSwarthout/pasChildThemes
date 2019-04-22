<?PHP
/*
 * pas_cth_selectFile( ) is called as an AJAX call from the Javascript function selectFile( ).
 * The Javascript function selectFile( ) is activated by an onclick event from the themes' filelists
 * when the user clicks on a file name.
 */
if ( ! class_exists( 'pas_cth_AJAXFunctions' ) ) {
	class pas_cth_AJAXFunctions {
		public $activeThemeInfo;
		private $pluginDirectory;
		private $pluginName;
		private $pluginFolder;
		private $colorPicker;
		private $libraryFunctions;
		private $Themes;

		function __construct( $args ) {
			$this->pluginDirectory	= $args['pluginDirectory'];
			$this->pluginName		= $args['pluginName'];
			$this->pluginFolder		= $args['pluginFolder'];
			$this->activeThemeInfo	= (array_key_exists('activeThemeInfo', $args) ? $args['activeThemeInfo'] : null);
			$this->colorPicker		= $args['colorPicker'];
			$this->libraryFunctions = $args['libraryFunctions'];
			$this->Themes			= (array_key_exists('Themes', $args) ? $args['Themes']	: null);
		}
		// To aid with debugging, when WP_DEBUG is true, this function displays a message code
		// on the message box in the lower right corner.
		function displayMessageID( $msgID ) {
			if ( constant( 'WP_DEBUG' ) ) {
				echo <<< "MESSAGEID"
<p class='mID'
   onmouseover='javascript:debugTip("show", "{$msgID}");'
   onmouseout='javascript:debugTip("hide");'
>{$msgID}</p>
MESSAGEID;
			}
		}

		/*
		 * pas_cth_verifyRemoveFile( )
		 * is called from the Javascript function removeChildFile( ) in 'js/pasChildThemes.js'
		 */
		function verifyRemoveFile( ) {
			$this->libraryFunctions->VerifyAuthorization(PAS_CTH_NOT_AUTHORIZED_REMOVE_FILE);
			// Posted from Javascript AJAX call
			$inputs = [
						'directory'	=> sanitize_text_field( $_POST['directory'] ),
						'file'		=> $this->MyFilenameSanitize( $_POST['file'] )
					];

			$childThemeFile	= $this->activeThemeInfo->childThemeRoot	. PAS_CTH_SEPARATOR
							. $this->activeThemeInfo->childStylesheet	. PAS_CTH_SEPARATOR
							. $inputs['directory']						. PAS_CTH_SEPARATOR
							. $inputs['file'];

			$templateThemeFile	= $this->activeThemeInfo->templateThemeRoot		. PAS_CTH_SEPARATOR
								. $this->activeThemeInfo->templateStylesheet	. PAS_CTH_SEPARATOR
								. $inputs['directory']							. PAS_CTH_SEPARATOR
								. $inputs['file'];

			/*
			 * If the files are identical, then just delete it.
			 * If the files are NOT identical, prompt the user before deleting.
			 */
			if ( $this->libraryFunctions->areFilesIdentical( $childThemeFile, $templateThemeFile ) ) {
				/* deletes the specified file and removes any folders that are now empty because
				 * the file was deleted or an empty subfolder was deleted.
				 */
				$args = [
							'activeThemeInfo'	=> $this->activeThemeInfo,
							'directory'			=> $inputs['directory'],
							'file'				=> $inputs['file'],
						];
				$this->libraryFunctions->killChildFile( $args );
			} else {
				// Files are not identical. Child file is different than the original template file.
				// This might be because the user modified the file, but it could also be,
				// that the template file was changed due to an update.

				$childStylesheet = $this->activeThemeInfo->childStylesheet;
				$templateStylesheet = $this->activeThemeInfo->templateStylesheet;

				$JSData = json_encode(
					[
						'directory'	=>	$inputs['directory'],
						'file'		=>	$inputs['file'],
						'action'	=>	'deleteFile'
					] );

				echo "<p class='warningHeading'>File has been modified</p><br><br>";

				echo "<div class='fileHighlight'>fldr:&nbsp;" . esc_html( $inputs['directory'] ) . "</div>";
				echo "<div class='fileHighlight'>file:&nbsp;" . esc_html( $inputs['file'] ) . "</div>";

				echo "<div class='emphasize'>If you proceed, you will LOSE your modifications.</div>";
				echo "<div class='buttonRow'>";
				echo "<INPUT data-jsdata='" . esc_html( $JSData ) . "' " .
					 " type='button' value='DELETE FILE' class='blueButton' " .
					 " onclick='javascript:pas_cth_js_deleteChildFile( this );'>";
				echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
				echo "<INPUT type='button' "
				   . " value='Cancel' "
				   . " class='blueButton' "
				   . " onclick='javascript:pas_cth_js_cancelDeleteChild( this );'>";
				echo "</div>"; // end buttonRow

				echo $this->displayMessageID( "vrf" );
//				echo "</div>";
			}
		}
		/*
		 * pas_cth_verifyCopyFile( )
		 * is called from the Javascript function copyTemplateFile( ) in 'js/pasChildThemes.js'
		 */
		function verifyCopyFile( ) {
			$this->libraryFunctions->VerifyAuthorization(PAS_CTH_NOT_AUTHORIZED_COPY_FILE);
			$inputs =[
						'directory'	=> sanitize_text_field( $_POST['directory'] ),
						'file'		=> $this->MyFilenameSanitize( $_POST['file'] ),
						'action'	=> 'copyFile',
					 ];

			$childThemeFile	=	$this->activeThemeInfo->childThemeRoot .
								PAS_CTH_SEPARATOR .
								$this->activeThemeInfo->childStylesheet .
								PAS_CTH_SEPARATOR .
								$inputs['directory'] .
								PAS_CTH_SEPARATOR .
								$inputs['file'];

			$templateThemeFile	=	$this->activeThemeInfo->templateThemeRoot .
									PAS_CTH_SEPARATOR .
									$this->activeThemeInfo->templateStylesheet .
									PAS_CTH_SEPARATOR .
									$inputs['directory'] .
									PAS_CTH_SEPARATOR .
									$inputs['file'];

			foreach ( $_POST as $key => $value ) {
				$args[$key] = $inputs[$key];
			}
//			$args['action'] = 'copyFile';

			/*
			 * File does not exist in the child theme.			Copy it.
			 * File EXISTS in both child and parent themes.
			 *		Files are identical.						Nothing to do.
			 *		Files are not identical.					Prompt the user before overwriting the existing child file.
			 */
			if ( ! file_exists( $childThemeFile ) ) {
				$this->copyFile( $args );
			} elseif ( $this->libraryFunctions->areFilesIdentical( $childThemeFile, $templateThemeFile ) ) {
				/*
				 * Files are identical. No need to actually perform the copy.
				 *
				 * $this->copyFile( $args );
				 */
			} else {
				/*
				 * The file already exists in the child theme and the files are NOT identical.
				 * Prompt the user to allow overwrite.
				 * Return the prompt to the AJAX call that got us here.
				 */
				$JSData = json_encode( $args );

				echo "<p class='warningHeading'>File Already Exists in Child Theme</p><br><br>";
				echo "The file '" . esc_html( $inputs['file'] ) . "' already exists in the child theme and has been modified.<br><br>";
				echo "Do you want to overwrite the file? Any changes that you have made will be lost.<br><br>";

				echo "<div class='questionPrompt'>";
				echo "<INPUT data-jsdata='" . esc_html( $JSData ) . "' " .
					 " type='button' value='OVERWRITE FILE' class='blueButton' " .
					 " onclick='javascript:pas_cth_js_overwriteFile( this );'>";
				echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
				echo "<INPUT type='button' "
				 . " value='Cancel' "
				 . " class='blueButton' "
				 . " onclick='javascript:pas_cth_js_cancelDeleteChild( this );'>";
				echo $this->displayMessageID( "vcf" );
				echo "</div>";
			}
		}
		/*
		 * The MyFilenameSanitize() is identical to sanitize_file_name except for a single change.
		 * The WordPress core function 'sanitize_file_name' strips leading underscores in file names.
		 * FILE NAMES ARE ALLOWED TO HAVE LEADING UNDERSCORES.
		 * The TwentyNineteen theme, released with WordPress 5.0 demonstrates this.
		 * Because the Child Themes Helper plugin copies files from the template or parent theme to the child theme,
		 * and themes like TwentyNineteen have files with leading underscores, the Child Themes Helper plugin
		 * cannot use a sanitize function that strips leading underscores from file names.
		 *
		 * The line below:
		 *    $filename = trim( $filename, '.-_' );
		 * was changed to:
		 *    $filename = trim( $filename, '.-' );
		 * Otherwise the MyFilenameSanitize() function is line-for-line identical to the sanitize_file_name() function
		 * as it appears in the /wp-includes/formatting.php file, as of WordPress 5.0.3.
		 */
		function MyFilenameSanitize($filename) {
			$filename_raw = $filename;
			$special_chars = array("?", "[", "]", "/", "\\", "=", "<", ">", ":", ";", ",", "'", "\"", "&", "$", "#", "*", "(", ")", "|", "~", "`", "!", "{", "}", "%", "+", chr(0));
			/**
			 * Filters the list of characters to remove from a filename.
			 *
			 * @since 2.8.0
			 *
			 * @param array  $special_chars Characters to remove.
			 * @param string $filename_raw  Filename as it was passed into sanitize_file_name().
			 */
			$special_chars = apply_filters( 'sanitize_file_name_chars', $special_chars, $filename_raw );
			$filename = preg_replace( "#\x{00a0}#siu", ' ', $filename );
			$filename = str_replace( $special_chars, '', $filename );
			$filename = str_replace( array( '%20', '+' ), '-', $filename );
			$filename = preg_replace( '/[\r\n\t -]+/', '-', $filename );
			$filename = trim( $filename, '.-' );

			if ( false === strpos( $filename, '.' ) ) {
				$mime_types = wp_get_mime_types();
				$filetype = wp_check_filetype( 'test.' . $filename, $mime_types );
				if ( $filetype['ext'] === $filename ) {
					$filename = 'unnamed-file.' . $filetype['ext'];
				}
			}

			// Split the filename into a base and extension[s]
			$parts = explode('.', $filename);

			// Return if only one extension
			if ( count( $parts ) <= 2 ) {
				/**
				 * Filters a sanitized filename string.
				 *
				 * @since 2.8.0
				 *
				 * @param string $filename     Sanitized filename.
				 * @param string $filename_raw The filename prior to sanitization.
				 */
				return apply_filters( 'sanitize_file_name', $filename, $filename_raw );
			}

			// Process multiple extensions
			$filename = array_shift($parts);
			$extension = array_pop($parts);
			$mimes = get_allowed_mime_types();

			/*
			 * Loop over any intermediate extensions. Postfix them with a trailing underscore
			 * if they are a 2 - 5 character long alpha string not in the extension whitelist.
			 */
			foreach ( (array) $parts as $part) {
				$filename .= '.' . $part;

				if ( preg_match("/^[a-zA-Z]{2,5}\d?$/", $part) ) {
					$allowed = false;
					foreach ( $mimes as $ext_preg => $mime_match ) {
						$ext_preg = '!^(' . $ext_preg . ')$!i';
						if ( preg_match( $ext_preg, $part ) ) {
							$allowed = true;
							break;
						}
					}
					if ( !$allowed )
						$filename .= '_';
				}
			}
			$filename .= '.' . $extension;
			/** This filter is documented in wp-includes/formatting.php */
			return apply_filters('sanitize_file_name', $filename, $filename_raw);
		}
		/*
		 * pas_cth_copyFile( )
		 * is called from the Javascript function overwriteFile( ) in 'js/pasChildThemes.js' AND
		 * from pas_cth_verifyCopyFile( ) when the child theme file does not exist.
		 * If the child theme file does not exist, $args are passed in, instead of
		 * coming as a AJAX POST.
		 * If the folders to the new child theme file do not exist: create them.
		 */
		function copyFile( $args = null ) {
			$this->libraryFunctions->VerifyAuthorization(PAS_CTH_NOT_AUTHORIZED_COPY_FILE);
			if ( null != $args ) {
				$childThemeRoot		= $this->activeThemeInfo->childThemeRoot;
				$childStylesheet	= $this->activeThemeInfo->childStylesheet;
				$templateThemeRoot	= $this->activeThemeInfo->templateThemeRoot;
				$templateStylesheet = $this->activeThemeInfo->templateStylesheet;
				$directory			= sanitize_text_field($args['directory']);
				$fileToCopy			= $this->MyFilenameSanitize($args['file']);
			} else {
				$childThemeRoot		= $this->activeThemeInfo->childThemeRoot;
				$childStylesheet	= $this->activeThemeInfo->childStylesheet;
				$templateThemeRoot	= $this->activeThemeInfo->templateThemeRoot;
				$templateStylesheet = $this->activeThemeInfo->templateStylesheet;
				$directory			= sanitize_text_field( $_POST['directory'] );
				$fileToCopy			= $this->MyFilenameSanitize( $_POST['file'] );
			}

			$dir = $childThemeRoot . PAS_CTH_SEPARATOR . $childStylesheet . PAS_CTH_SEPARATOR;

			$folderSegments = explode( PAS_CTH_SEPARATOR, $directory );

			// Create any folder that doesn't already exist.
			for ( $ndx = 0; $ndx < count( $folderSegments ); $ndx++ ) {
				$dir .= PAS_CTH_SEPARATOR . $folderSegments[$ndx];
				if ( ! file_exists( $dir ) ) {
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
			if ( ! $result ) {
				echo "Failed to copy<br>$sourceFile<hr>to<hr>$targetFile<br>";
			}
		}

		/*
		 * pas_cth_deleteFile( )
		 * is called from the Javascript function deleteChildFile( ) in 'js/pasChildThemes.js'
		 * Delete the file and any empty folders made empty by the deletion of the file
		 * or subsequent subfolders.
		 */
		function deleteFile() {
			$this->libraryFunctions->VerifyAuthorization(PAS_CTH_NOT_AUTHORIZED_REMOVE_FILE);
			$args = [
						'directory'			=> sanitize_text_field( $_POST['directory'] ),
						'file'				=> sanitize_file_name( $_POST['file'] ),
						'activeThemeInfo'	=> $this->activeThemeInfo
					];
			$this->libraryFunctions->killChildFile( $args );
		}

		/* createChildTheme( ) is called from the Javascript function
		 * pas_cth_js_createChildTheme( ) in 'js/pasChildThemes.js'
		 */
		function createChildTheme() {
			$this->libraryFunctions->VerifyAuthorization(PAS_CTH_NOT_AUTHORIZED_CREATE_THEME);
			$err = 0;
			$inputs =	[
							'childThemeName'=> sanitize_text_field( $_POST['childThemeName'] ),
							'templateTheme' => sanitize_text_field( $_POST['templateTheme'] ),
							'description'	=> sanitize_textarea_field( $_POST['description'] ),
							'authorName'	=> sanitize_text_field( $_POST['authorName'] ),
							'authorURI'		=> sanitize_text_field( $_POST['authorURI'] ),
							'version'		=> sanitize_text_field( $_POST['version'] ),
							'themeURI'		=> sanitize_text_field( $_POST['themeURI'] )
						];
			if ( 0 === strlen( trim( $inputs['childThemeName'] ) ) ) {
				$this->libraryFunctions->displayError( "Notice",
									 "Child Theme Name cannot be blank." );
				$err++;
			}

			if ( 0 === strlen( trim( $inputs['templateTheme'] ) ) ) {
				$this->libraryFunctions->displayError( "Notice",
									 "Parent Theme is required." );
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

			if ( 0 !== $err ) {
				return;
			}

			// Create the stylesheet folder
			$themeRoot = $this->libraryFunctions->fixFolderSeparators( get_theme_root( ) );
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

			fwrite( $styleFile, "/*" . $newlineChar );
			fwrite( $styleFile, " Theme Name: " . $childThemeName		. $newlineChar );
			fwrite( $styleFile, " Theme URI: " . $inputs['themeURI']	. $newlineChar );
			fwrite( $styleFile, " Description: " . $inputs['description']. $newlineChar );
			fwrite( $styleFile, " Author: " . $inputs['authorName']	. $newlineChar );
			fwrite( $styleFile, " Author URI: " . $inputs['authorURI']	. $newlineChar );
			fwrite( $styleFile, " Template: " . $inputs['templateTheme']. $newlineChar );
			fwrite( $styleFile, " Version: " . $inputs['version']	. $newlineChar );
			fwrite( $styleFile, "*/" . $newlineChar );
			fclose( $styleFile );

			// Create the functions.php file for the child theme. Use the wp_enqueue_style( ) function
			// to correctly set up the stylesheets for the child theme.

			$stylesheetURL = dirname(get_stylesheet_uri());
			$stylesheetURL = $this->libraryFunctions->setDelimiters($stylesheetURL);
			$stylesheetURL = $this->libraryFunctions->dirUp($stylesheetURL, 1);
			$stylesheetURL .= PAS_CTH_SEPARATOR . $childThemeStylesheet . PAS_CTH_SEPARATOR . "style.css";

			$functionsFile = fopen( $childThemePath . PAS_CTH_SEPARATOR . "functions.php", "w" );

			$functionsFileOutput = '<?PHP ' . $newlineChar;
			$functionsFileOutput .= <<< "FUNCTIONSFILEOUTPUT"
add_action('wp_enqueue_scripts', '{$childThemeStylesheet}_theme_styles' );

function {$childThemeStylesheet}_theme_styles() {
	wp_enqueue_style( 'parent-style', get_template_directory_uri() . '/style.css' );
	wp_enqueue_style( '{$childThemeStylesheet}-style', WP_CONTENT_URL . '/themes/{$childThemeStylesheet}/style.css' );
}

FUNCTIONSFILEOUTPUT;
			fwrite( $functionsFile, $functionsFileOutput);
			fclose( $functionsFile );
			// Handshake with the Javascript AJAX call that got us here.
			// When "SUCCESS:url" is returned, Javascript will redirect to the url.
			echo "SUCCESS:" . esc_url_raw( $_POST['href'] );
		}
		// Save options.
		function saveOptions() {
			$this->libraryFunctions->VerifyAuthorization(PAS_CTH_NOT_AUTHORIZED_CHANGE_OPTIONS);
			$inputs =
				[
					'abbreviation'	=> sanitize_text_field( $_POST['abbreviation'] ),
					'hexColorCode'	=> sanitize_text_field( $_POST['hexColorCode'] )
				];

			update_option( "pas_cth_" . $inputs['abbreviation'], $inputs['hexColorCode'] );
			echo "ABBREVIATION:{" . $inputs['abbreviation'] . "}";
		}
		function chooseColor() {
			$this->libraryFunctions->VerifyAuthorization(PAS_CTH_NOT_AUTHORIZED_CHANGE_OPTIONS);
			$initialColor		= sanitize_text_field( $_POST['initialColor'] );
			$originalColorField = sanitize_text_field( $_POST['callingFieldName'] );
			$args = [
						'initialColor'		=> $initialColor,
						'callingFieldName'	=> $originalColorField
					];
			echo $this->colorPicker->getNewColor( $args );
		}

		function saveFont() {
			$this->libraryFunctions->VerifyAuthorization(PAS_CTH_NOT_AUTHORIZED_CHANGE_OPTIONS);
			$fontFile = trim( sanitize_text_field( $_POST['fontFile-base'] ) );
			$fontName = sanitize_text_field( $_POST['fontName'] );

			update_option( 'pas_cth_font', [ 'fontName'=>$fontName, 'fontFile-base'=>$fontFile ] );
		}

		function editFile() {
			$this->libraryFunctions->VerifyAuthorization(PAS_CTH_NOT_AUTHORIZED_EDIT_FILE);
			$inputs =
				[
					'directory'	=> sanitize_text_field( $_POST['directory'] ),
					'file'		=> sanitize_file_name( $_POST['file'] ),
					'themeType' => sanitize_text_field( $_POST['themeType'] ),
				];
			switch (strtolower($inputs['themeType'])) {
				case PAS_CTH_CHILDTHEME:
					$file = $this->activeThemeInfo->childThemeRoot . PAS_CTH_SEPARATOR . $this->activeThemeInfo->childStylesheet . PAS_CTH_SEPARATOR . $inputs['directory'] . PAS_CTH_SEPARATOR . $inputs['file'];
					$readOnly = 'false';
					break;
				case PAS_CTH_TEMPLATETHEME:
					$file = $this->activeThemeInfo->templateThemeRoot . PAS_CTH_SEPARATOR . $this->activeThemeInfo->templateStylesheet . PAS_CTH_SEPARATOR . $inputs['directory'] . PAS_CTH_SEPARATOR . $inputs['file'];
					$readOnly = 'true';
					break;
			}
			$inputs['readOnlyFlag'] = $readOnly;

			$fileContents = stripslashes(str_replace(">", "&gt;", str_replace("<", "&lt;", file_get_contents($file))));
			echo "EDITFILEOUTPUT:{";
			echo "ARGS<:>" . json_encode($inputs);
			echo '+|++|+';
			echo "EDITBOX<:>{$fileContents}";
			echo "}";
		}
		function saveFile() {
			$this->libraryFunctions->VerifyAuthorization(PAS_CTH_NOT_AUTHORIZED_EDIT_FILE);
			$inputs =
				[
					'fileContents'	=> $_POST['fileContents'],
					'directory'		=> sanitize_text_field( $_POST['directory'] ),
					'file'			=> sanitize_file_name( $_POST['file'] ),
					'themeType'		=> sanitize_text_field( $_POST['themeType'] ),
				];

			switch ($inputs['themeType']) {
				case PAS_CTH_CHILDTHEME:
					$file = $this->activeThemeInfo->childThemeRoot . PAS_CTH_SEPARATOR . $this->activeThemeInfo->childStylesheet . PAS_CTH_SEPARATOR . $inputs['directory'] . PAS_CTH_SEPARATOR . $inputs['file'];
					break;
				case PAS_CTH_TEMPLATETHEME:
					$file = $this->activeThemeInfo->templateThemeRoot . PAS_CTH_SEPARATOR . $this->activeThemeInfo->templateStylesheet . PAS_CTH_SEPARATOR . $inputs['directory'] . PAS_CTH_SEPARATOR . $inputs['file'];
					break;
			}
			$result = file_put_contents($file, stripslashes($_POST['fileContents']));
			if ($result === false) {
				echo "Failed to write file:<br>";
				echo "FILE: $file<br>";
				echo "Length of file: " . strlen($inputs['fileContents']);
			} else {
			}
		}
		function ajax_set_expert_mode() {
			$expertMode = sanitize_text_field( $_POST['enabled'] );
			update_option("pas_cth_expert_mode", $expertMode);
		}
		function ajax_set_child_theme() {
			$this->libraryFunctions->VerifyAuthorization();
			$childTheme = sanitize_text_field( $_POST['childTheme'] );
			update_option("pas_cth_active_theme", $childTheme);
		}
		function ajax_generate_screen_shot() {
			$this->libraryFunctions->VerifyAuthorization(PAS_CTH_NOT_AUTHORIZED_CHANGE_OPTIONS);
			$this->generateScreenShot();
		}
				/* Generates the screenshot.png file in the child theme, if one does not yet exist.
		 * If changes to the options do not show up, clear your browser's stored images,
		 * files, fonts, etc.
		 */
		function generateScreenShot() {
			$this->libraryFunctions->VerifyAuthorization(PAS_CTH_NOT_AUTHORIZED_CHANGE_OPTIONS);
			$screenShotFile = $this->activeThemeInfo->childThemeRoot . PAS_CTH_SEPARATOR
							. $this->activeThemeInfo->childStylesheet
							. PAS_CTH_SEPARATOR
							. "screenshot.png";

			$args = [
				'targetFile'		=> $screenShotFile,
				'childThemeName'	=> $this->activeThemeInfo->childThemeName,
				'templateThemeName' => $this->activeThemeInfo->templateStylesheet,
				'pluginDirectory'	=> $this->pluginDirectory,
				'activeThemeInfo' => $this->activeThemeInfo,
				'libraryFunctions'	=> $this->libraryFunctions
					];

			// pas_cth_ScreenShot( )::__construct( ) creates the screenshot.png file.
			// $status not needed afterwards
			// Will overwrite an existing screenshot.png without checking. // Need to fix this.
			$status = new pas_cth_ScreenShot( $args );
			unset( $status ); // ScreenShot.png is created in the class' __construct( ) function.

			$outputParameters = [];
			array_push($outputParameters, 
				[
					'stylesheet'	=>	get_option("pas_cth_active_theme"),
					'siteURL'		=>	get_site_url(),
					'filename'		=>	"screenshot.png",
				]);


			echo $this->convertToXML($outputParameters);
		}
		/*
		 * convertToXML takes a numerically indexed array of associative arrays and converts it into XML.
		 * The XML data will be returned to the Javascript function that made the AJAX call.
		 */
		function convertToXML($data) {
			$crlf = ( "WIN" === strtoupper( substr( PHP_OS, 0, 3 ) ) ? "\r\n" : "\n" );
			$xmlOutput = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>' . $crlf;
			$xmlOutput .= "<output>{$crlf}";
			foreach ($data as $row) {
				$xmlOutput .= "<record>{$crlf}";
				foreach ($row as $key => $value) {
					$xmlOutput .= "<{$key}>{$value}</{$key}>{$crlf}";
				}
				$xmlOutput .= "</record>{$crlf}";
			}
			$xmlOutput	.= "</output>";
			return $xmlOutput;
		}
	}
}