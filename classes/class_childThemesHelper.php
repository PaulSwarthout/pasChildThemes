<?PHP
if ( ! class_exists( 'pas_cth_ChildThemesHelper' ) ) {
	class pas_cth_ChildThemesHelper {
		public $pluginDirectory;
		public $pluginName;
		public $pluginFolder;
		public $activeThemeInfo;
		public $allThemes;

		function __construct( $args ) {
			$this->pluginDirectory	= $args['pluginDirectory'];
			$this->pluginName		= $args['pluginName'];
			$this->pluginFolder		= $args['pluginFolder'];
			$this->activeThemeInfo	= $args['activeThemeInfo'];
			$this->allThemes		= $this->enumerateThemes();
		}

		// Load the pasChildThemes CSS style.css file
		function dashboard_styles() {
			// Prevents browser from caching the stylesheet during development
			$uniqStr = (constant( 'WP_DEBUG' ) ? "?u=" . rand(0, 999999) . "&" : "");
			wp_enqueue_style(	'pasChildThemes',
								$this->pluginDirectory['url'] . "css/style.css" . $uniqStr,
								false );
		}

		// Load the pasChildThemes Javascript script file
		function dashboard_scripts() {
			// Prevents browser from caching the stylesheet during development
			$uniqStr = (constant( 'WP_DEBUG' ) ? "?u=" . rand(0, 999999) . "&" : "");
			wp_enqueue_script( 'pas_cth_Script',
								$this->pluginDirectory['url'] . "js/pasChildThemes.js" . $uniqStr,
								false );
			wp_enqueue_script( 'pas_cth_Script2',
								$this->pluginDirectory['url'] . "js/js_common_fn.js" . $uniqStr,
								false );
		}

		// pasChildThemes Dashboard Menu
		function dashboard_menu() {
			add_menu_page(	'ChildThemesHelper',
							'Child Themes Helper',
							'manage_options',
							'manage_child_themes',
							Array( $this, 'manage_child_themes' ),
							"",
							61 // appears just below the Appearances menu.
						);
			// Prevent overwriting the template theme's screenshot.png file.
			if ( $this->activeThemeInfo->isChildTheme ) {
				add_submenu_page( 	'manage_child_themes',
									'Generate ScreenShot',
									'Generate ScreenShot',
									'manage_options',
									'genScreenShot',
									Array( $this, 'generateScreenShot' )
								 );
			}
			add_submenu_page(   'manage_child_themes',
								'Options',
								'Options',
								'manage_options',
								'Options',
								Array( $this, 'pas_cth_Options' )
							);
		}
		// WriteOption() displays an option on the pasChildThemes options page.
		function WriteOption( $args ) {
			$label			= $args['label'];
			$optionName		= $args['optionName'];
			$defaultValue	= $args['default'];

/* NOT YET IMPLEMENTED.
 *		$ifColorPicker =
 *			( array_key_exists( 'colorPicker', $args ) ? $args['colorPicker'] : false );
 *			if ( $ifColorPicker ) {
 *				$colorPicker = "show color picker";
 *			} else {
 *				$colorPicker = "";
 *			}
 */

			$dots = DOTS; // string of periods. Will overflow the div.
			$optionValue = get_option( "pas_cth_$optionName", $defaultValue );
			$readonly = ( array_key_exists( 'readonly', $args ) ? " READONLY " : "" );

			if ( array_key_exists( 'type', $args ) ) {
				switch ( strtolower( $args['type'] ) ) {
					case "input":
						$formElement = "<input type='text' "
									 . "       name='$optionName' "
									 . "       value='$optionValue' "
									 . "       onblur='javascript:pas_cth_js_SetOption(this);' "
									 . $readonly . " >";
						if (array_key_exists( 'stringFontFamily', $args )) {
							$bShowFontSelection = $args['stringFontFamily'];
						} else {
							$bShowFontSelection = false;
						}
						break;
					case "select":
						$formElement =
							"<select name='" . esc_attr($optionName) . "' " .
							"        onblur='javascript:pas_cth_js_SetOption(this);' " .
							"        $readonly >" .
							"<option value=''>Choose the Font</option>";
						if ( array_key_exists( 'options', $args ) ) {
							$options = $args['options'];
							foreach ( $options as $value ) {
								$selected = ( $value[1] == $optionValue ? " SELECTED " : "" );
								$formElement .= "<option value='" . $value[1] . "' $selected >" .
												$value[0] . "</option>";
							}
							$formElement .= "</select>";
						} else {
							$formElement =
								"<input type='text' " .
								"       name='$optionName' " .
							    "       value='$optionValue' " .
								"       onblur='javascript:pas_cth_js_SetOption( this );' " .
								" $readonly >";
						}
				}
			} else {
				$formElement = "<input type='text' " .
					           "       name='" . esc_attr($optionName) . "' " .
					           "       value='" . esc_attr($optionValue) . "' " .
					           "       onblur='javascript:pas_cth_js_SetOption( this );' " .
					           "       $readonly >";
			}

			$outputString = <<<"OPTION"
			<div class='pct'>
			<span class='pctOptionHeading'>
				<nobr>$label<span class='dots'>$dots</span></nobr>
			</span>
			<span class='pctOptionValue'>
				$formElement
			</span>
			</div>
OPTION;

			return ( $outputString );
		}

		function enumerateThemes() {
			$themes = array();

			// Loads all theme data
			$all_themes = wp_get_themes();

			// Loads theme names into themes array
			foreach ( $all_themes as $theme ) {
				$name = $theme->get( 'Name' );
				$stylesheet = $theme->get_stylesheet();

				if ( $theme->parent() ) {
					$status = true;
				} else {
					$status = false;
				}
				$parent = $theme->get( 'Template' );
				$parentStylesheet = $theme->get_stylesheet();

				$themes[$stylesheet] = [
						'themeName'			=> $name,
						'themeStylesheet'	=> $stylesheet,
						'themeParent'		=> $parent,
						'parentStylesheet'	=> $parentStylesheet,
						'childTheme'		=> $status
										];
			}

			return $themes;
		}

		// pasChildThemes' Options page.
		function pas_cth_Options() {
			echo "<h1>Screen Shot Options</h1>";
			echo "<p id='notice'>";
			echo "If you make changes here and your screenshot.png doesn't change when you ";
			echo "generate it, clear your browser's image cache.";
			echo "</p>";

			echo $this->WriteOption(
				[
					'label'		=> 'Image Width: ',
					'optionName'=> 'imageWidth',
					'default'	=> get_option( 'pas_cth_imageWidth', PAS_CTH_DEFAULT_IMAGE_WIDTH ),
					'onblur'	=> 'pas_cth_js_pctSetOption( this )',
					'type'		=> 'input'
				 ] );

			echo $this->WriteOption(
				[
					'label'		=> 'Image Height: ',
					'optionName'=> 'imageHeight',
					'default' => get_option( 'pas_cth_imageHeight', PAS_CTH_DEFAULT_IMAGE_HEIGHT ),
					'onblur'	=> 'pas_cth_js_pctSetOption( this )',
					'type'		=> 'input'
				] );

			echo $this->WriteOption(
				[
					'label'		=> 'Background Color: ',
					'optionName'=> 'bcColor',
					'default'=> get_option( 'pas_cth_bcColor', PAS_CTH_DEFAULT_SCREENSHOT_BCCOLOR ),
					'onblur'	=> 'pas_cth_js_pctSetOption( this )',
					'colorPicker'=> false,
					'type'		=> 'input'
				] );

			echo $this->WriteOption(
				[
					'label'		=> 'Text Color: ',
					'optionName'=> 'fcColor',
					'default'=> get_option( 'pas_cth_fcColor', PAS_CTH_DEFAULT_SCREENSHOT_FCCOLOR ),
					'onblur'	=> 'pas_cth_js_pctSetOption( this )',
					'colorPicker'=> false,
					'type'		=> 'input'
				] );

			echo $this->WriteOption(
				[
					'label'		=> 'Font: ',
					'optionName'=> 'font',
					'default'	=> get_option( 'pas_cth_font', 'Arial'),
					'type'		=> 'select',
					'options'	=> [ ['Arial', 'arial.ttf'],
									 ['Black Chancery', 'BLKCHCRY.TTF'] ]
				] );
/* These options caused problems when switching themes. Suddenly, the ScreenShot Generation would
 * create screenshots with the wrong child theme name. For now, remove these options, and
 * fix the problem when enhancing the screenshot generation in a later release.
			echo $this->WriteOption(
				[
					'label'				=> 'String1: ',
					'optionName'		=> 'string1',
					'default'			=> $this->activeThemeInfo->childThemeName,
					'type'				=> 'input',
					'fontSize'			=> 50,
					'topPad'			=> 0,
					'maxFontSize'		=> true,
					'fontSizePrompt'	=> true,
					'stringFontFamily'	=> true
				] );

			echo $this->WriteOption(
				[
					'label'				=> 'String2: ',
					'optionName'		=> 'string2',
					'default'			=> "...is a child of " .
											$this->activeThemeInfo->templateThemeName,
					'type'				=> 'input',
					'fontSize'			=> 50,
					'topPad'			=> 0,
					'maxFontSize'		=> true,
					'fontSizePrompt'	=> true,
					'stringFontFamily'	=> true
				] );

			echo $this->WriteOption(
				[
					'label'				=> 'String3: ',
					'optionName'		=> 'string3',
					'default'			=> PAS_CTH_PLUGINNAME,
					'type'				=> 'input',
					'readonly'			=> true,
					'fontSize'			=> 46,
					'topPad'			=> 0,
					'maxFontSize'		=> true,
					'fontSizePrompt'	=> true,
					'stringFontFamily'	=> true
				] );

			echo $this->WriteOption(
				[
					'label'				=> 'String4: ',
					'optionName'		=> 'string4',
					'default'			=> PAS_CTH_MYURL,
					'type'				=> 'input',
					'readonly'			=> true,
					'fontSize'			=> 46,
					'topPad'			=> 0,
					'maxFontSize'		=> true,
					'fontSizePrompt'	=> true,
					'stringFontFamily'	=> true
				] );
*/
			// Dummy button. Options are saved onblur event for each option. This button simply
			// forces an onblur event to be fired from the last option that had focus.
			echo "<input type='button' class='blueButton' value='Save Options'>";
		}
		// Not yet implemented
		function showColorPicker() {
		}
		/* Generates the screenshot.png file in the child theme, if one does not yet exist.
		 * If changes to the options do not show up, clear your browser's stored images,
		 * files, fonts, etc.
		 *   This applies mostly to Chrome. Tested with update #68.
		 */
		function generateScreenShot() {
			$screenShotFile = $this->activeThemeInfo->childThemeRoot . PAS_CTH_SEPARATOR
							. $this->activeThemeInfo->childStylesheet
							. PAS_CTH_SEPARATOR
							. "screenshot.png";

			$args = [
				'targetFile'		=> $screenShotFile,
				'childThemeName'	=> $this->activeThemeInfo->childThemeName,
				'templateThemeName' => $this->activeThemeInfo->templateStylesheet,
				'pluginDirectory'	=> $this->pluginDirectory,
				'activeThemeInfo'   => $this->activeThemeInfo
					];

			// pas_cth_ScreenShot()::__construct() creates the screenshot.png file.
			// $status not needed afterwards
			// Will overwrite an existing screenshot.png without checking. // Need to fix this.
			$status = new pas_cth_ScreenShot( $args );
			unset( $status ); // ScreenShot.png is created in the class' __construct() function.


			// All done. Reload the Dashboard Themes page.
			// Response buffering turned on so we can do this.
			wp_redirect( admin_url( "themes.php" ) );
		}

		// showActiveChildTheme() will display the list of files for the child theme
		// in the left-hand pane.
		function showActiveChildTheme() {
			$currentThemeInfo = $this->activeThemeInfo; // this is an object.
			if ( $this->activeThemeInfo->templateStylesheet ) {
				echo "<p class='pasChildTheme_HDR'>CHILD THEME</p>";
				echo "<p class='actionReminder'>";
				echo "Click a file to <u>REMOVE</u> it from the child theme";
				echo "</p>";
			}
			echo "<p class='themeName'>" . $this->activeThemeInfo->childThemeName . "</p>";

			$childThemeFolder = $this->activeThemeInfo->getChildFolder();

			echo "<div class='innerCellLeft'>";
			$this->listFolderFiles( $childThemeFolder, PAS_CTH_CHILDTHEME );
			echo "</div>";
		}

		// showActiveParentTheme() will display the list of files for the template theme
		// in the right-hand pane.
		function showActiveParentTheme() {
			echo "<p class='pasChildTheme_HDR'>THEME TEMPLATE</p>";
			echo "<p class='actionReminder'>Click a file to <u>COPY</u> it to the child theme</p>";
			echo "<p class='themeName'>" . $this->activeThemeInfo->templateThemeName . "</p>";

			$parentFolder = $this->activeThemeInfo->getTemplateFolder();

			echo "<div class='innerCellLeft'>";
			$this->listFolderFiles( $parentFolder, PAS_CTH_TEMPLATETHEME );
			echo "</div>";
		}
		/*
		 *	manage_child_themes is the main driver function. This function is called from
		 *  the Dashboard menu option 'Child Themes Helper'. This function either:
		 *	1 ) Displays the file list for the child theme and the file list for the template theme or
		 *	2 ) If the currently active theme is NOT a child theme, it displays the "form" to create a new
		 *	   child theme.
		 */
		function manage_child_themes() {
			if ( ! current_user_can( 'manage_options' ) ) { exit; }

			$select = "<label for='templateTheme'>"
			        . "Template Theme ( defaults to currently active theme )"
							. "<br><select name='templateTheme' id='templateTheme'>";
			foreach ( $this->allThemes as $key => $value ) {
				if ( ! $value['childTheme'] ) { // do not list any theme that is a child theme.
					if ( strtoupper( $this->activeThemeInfo->childThemeName ) ==
						strtoupper( $value['themeName'] ) ) {
						$selected = " SELECTED ";
					} else {
						$selected = "";
					}
					$select .= "<option value='"
							.			esc_attr( $key )
							.			"' $selected>"
							.			esc_html( $value['themeName'] )
							. "</option>";
				}
			}
			$select .= "</select>";

			if ( ! $this->activeThemeInfo->isChildTheme ) {
				/* Current active theme is not a child theme.
				 * Prompt to create a child theme.
				 * This is set up to look like a typical HTML <form>,
				 * but it is not processed as one.
				 * We want to avoid refreshing the page so the
				 * output from the wp_ajax_createChildTheme function will be displayed.
				 */
				echo "<div class='createChildThemeBox'>";
				echo "<p class='warningHeading'>Warning</p><br><br>";
				echo "The current theme <u>" . $this->activeThemeInfo->childThemeName . "</u>";
				echo "is <u>not</u> a child theme.";
				echo "<br><br>"; // replace with CSS in future release;
				echo "Do you want to create a child theme?";
				echo "<br><br>"; // replace with CSS in future release;
				echo "<form>";
				echo "<input type='hidden' name='action' value='createChildTheme'>";
				echo "<input type='hidden' name='href' value='" . admin_url( "themes.php" ) . "'>";
				echo "<label for='childThemeName'>";
				echo "Child Theme Name:";
				echo "<br>";
				echo "<input type='text' name='childThemeName' id='childThemeName' value=''>";
				echo "</label>";
				echo "<br>";
				echo $select . "<br>"; // displays a list of installed, active, non-child, themes
				echo "<label for='ThemeURI'>";
				echo "Theme URI<br>";
				echo "<input type='text' name='themeURI' id='themeURI' value=''>";
				echo "</label><br>";
				echo "<label for='Description'>";
				echo "Theme Description<br>";
				echo "<textarea id='description' name='description'></textarea>";
				echo "</label><br>";
				echo "<label for='authorName'>";
				echo "Author Name:<br>";
				echo "<input type='text' id='authorName' name='authorName' value=''>";
				echo "</label><br>";
				echo "<label for='authorURI'>";
				echo "Author URI:<br>";
				echo "<input type='text' id='authorURI' name='authorURI' value=''>";
				echo "</label><br>";
				echo "<label for='version'>";
				echo "Version:<br>";
				echo "<input type='text' id='version' name='version' value='0.0.1' readonly>";
				echo "</label><br>";

				echo "<br>";
				echo "<div class='questionPrompt'>";
				echo "<input type='button' ";
				echo "       value='Create Child Theme' ";
				echo "       class='blueButton' ";
				echo "       onclick='javascript:pas_cth_js_createChildTheme( this );'>";
				echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
				echo "<input type='button' ";
				echo "       value='Reset' ";
				echo "       class='blueButton' ";
				echo "       onclick='javascript:pas_cth_js_resetForm( this.form );'>";
				echo "</div>";

				echo "</div>";
				echo "</form>";
				return false;
			}

			echo "<div class='pas-grid-container'>";
			echo "<div class='pas-grid-item'>"; // Start grid item 1

			$this->showActiveChildTheme();

			echo "</div>"; // end grid item 1

			echo "<div class='pas-grid-item'>"; // start grid item 2

			$this->showActiveParentTheme();

			echo "</div>"; // end grid item 2
			echo "</div>"; // end grid container
		}
		/*
		 * stripRoot()
		 * The listFolderFiles() function takes a full physical path as a parameter.
		 * But the full path to the file must be known when the user clicks on a file
		 * in the file list. But the full path up to and including the "themes" folder
		 * is constant.
		 *
		 * The stripRoot() function removes everything in the $path up to and not including
		 * the theme's stylesheet folder. In other words, stripRoot() strips the theme root
		 * from the file path so that listFolderFiles() when writing out a file, doesn't have
		 * to include the full path in every file.
		 *
		 * stripRoot() takes the full $path and the $themeType as
		 * parameters.
		 */
		function stripRoot( $path, $themeType ) {
			$sliceStart = $this->activeThemeInfo->getFolderCount( $themeType );

			$folderSegments = explode( PAS_CTH_SEPARATOR, $path );
			$folderSegments = array_slice( $folderSegments, $sliceStart );
			$path = implode( PAS_CTH_SEPARATOR, $folderSegments );

			return $path;
		}
		/* The listFolderFiles() function is the heart of the child theme and template theme
		 * file listings.
		 * It is called recursively until all of the themes' files are found.
		 * It excludes the ".", "..", and ".git" folders, if they exist.
		 * $dir is the full path to the theme's stylesheet.
		 * For example: c:\inetpub\mydomain.com\wp-content\themes\twentyseventeen
		 * $themeType is either PAS_CTH_CHILDTHEME or PAS_CTH_TEMPLATETHEME.
		 * All CONSTANTS are defined in 'lib/plugin_constants.php'.
		 */
		function listFolderFiles( $dir, $themeType ){
			$ffs = scandir( $dir );

			unset( $ffs[array_search( '.', $ffs, true )] );
			unset( $ffs[array_search( '..', $ffs, true )] );
			unset( $ffs[array_search( '.git', $ffs, true )] );

			// prevent empty ordered elements
			if ( 1 > count( $ffs ) ) {
				return; // Bail out.
			}

			echo "<div class='clt'>";

			echo '<ul>';
			foreach( $ffs as $ff ){
				if ( is_dir( $dir . PAS_CTH_SEPARATOR . $ff ) ) {
					echo "<li><p class='pas_cth_directory'>" . $ff . "</p>";
					if( is_dir( $dir.PAS_CTH_SEPARATOR.$ff ) ) {
						$this->listFolderFiles( $dir.PAS_CTH_SEPARATOR.$ff, $themeType );
					}
				} else {
					// strips theme root, leaving stylesheet and sub folders and file.
					$shortDir = $this->stripRoot( $dir, $themeType );
					$jsdata = json_encode(
											['directory'=>$shortDir,
											 'fileName'=>$ff,
											 'themeType'=>$themeType
											]
										);
					echo "<li>"
						 . "<p class='file' "
						 . "   data-jsdata='" . esc_attr($jsdata) . "' "
						 . "   onclick='javascript:pas_cth_js_selectFile( this );'>";
					echo "<nobr>$ff</nobr>";
					echo "</p>";
				}
				echo "</li>";
			}
			echo '</ul>';

			echo "</div>";
		}
	}
}
