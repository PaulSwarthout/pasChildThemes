<?PHP
if ( ! class_exists( 'pas_cth_ChildThemesHelper' ) ) {
	class pas_cth_ChildThemesHelper {
		public $pluginDirectory;
		public $pluginName;
		public $pluginFolder;
		public $activeThemeInfo;
		public $allThemes;
		public $colorPicker;
		public $fontSamples; // Array of sample font images, to be used in pas_cth_Options( );
		public $fontList;
		public $dataBlock;
		public $libraryFunctions;
		private $crlf;
		private $demo_mode;

		function __construct( $args ) {
			$this->pluginDirectory	= $args['pluginDirectory'];
			$this->pluginName		= $args['pluginName'];
			$this->pluginFolder		= $args['pluginFolder'];
			$this->activeThemeInfo	= $args['activeThemeInfo'];
			$this->allThemes		= $this->enumerateThemes( );
			$this->colorPicker		= $args['colorPicker'];
			$this->fontSampleImages	= [];
//			$this->fontList			= $this->loadFonts( );
			$this->libraryFunctions = $args['libraryFunctions'];
			$this->crlf				= $this->libraryFunctions->crlf();
			$this->demo_mode		= (array_key_exists('demo_args', $args) ? $args['demo_args'] : null);
		}
		function __destruct( ) {
			foreach ( $this->fontSampleImages as $img ) {
				imagedestroy( $img );
			}
			unset( $this->fontSampleImages );
		}

		// Load the pasChildThemes CSS style.css file
		function dashboard_styles( ) {
			// Prevents browser from caching the stylesheet during development
			$uniqStr = ( constant( 'WP_DEBUG' ) ? "?u=" . rand( 0, 999999 ) . "&" : "" );
			wp_enqueue_style( 	'pasChildThemes',
								$this->pluginDirectory['url'] . "css/style.css" . $uniqStr,
								false );
			if (defined('WP_DEBUG') && constant('WP_DEBUG') && defined('PLUGIN_DEVELOPMENT') && constant('PLUGIN_DEVELOPMENT') == "YES") {
				wp_enqueue_style(	'pasChildThemes3',
									$this->pluginDirectory['url'] . 'css/hexdump.css' . $uniqStr,
									false );
			}
		}

		// Load the pasChildThemes Javascript script file
		function dashboard_scripts( ) {
			// Prevents browser from caching the stylesheet during development
			$uniqStr = ( constant( 'WP_DEBUG' ) ? "?u=" . rand( 0, 999999 ) . "&" : "" );
			wp_enqueue_script( 'pas_cth_Script',
								$this->pluginDirectory['url'] . "js/pasChildThemes.js" . $uniqStr,
								false );
			wp_enqueue_script( 'pas_cth_Script2',
								$this->pluginDirectory['url'] . "js/js_common_fn.js" . $uniqStr,
								false );
			wp_enqueue_script( 'pas_cth_Script3',
							   $this->pluginDirectory['url'] . "js/edit_file.js" . $uniqStr,
							   false );
			if (defined('WP_DEBUG') && constant('WP_DEBUG') && defined('PLUGIN_DEVELOPMENT') && constant('PLUGIN_DEVELOPMENT') == "YES") {
				wp_enqueue_script( 'pas_cth_Script4',
								   $this->pluginDirectory['url'] . 'js/hexdump.js' . $uniqStr,
								   false );
				if (defined('WP_DEBUG') && constant('WP_DEBUG')) {
					$obj = ['WP_DEBUG' => "ENABLED"];
					wp_localize_script( 'pas_cth_Script4', 'pas_cth_debugMode', $obj );
				}
			}
		}

		// pasChildThemes Dashboard Menu
		function dashboard_menu( ) {
			$userlogin = "";
			if (defined("DEMO_USER")) {
				$userlogin = strtolower(constant("DEMO_USER"));
			}
			if ($userlogin == strtolower(wp_get_current_user()->user_login) && defined("DEMO_CAPABILITY")) {
				$capability = constant("DEMO_CAPABILITY");
			} else {
				$capability = "manage_options";
			}
			add_menu_page( 	'ChildThemesHelper',
							'Child Themes Helper',
							$capability,
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
									$capability,
									'genScreenShot',
									Array( $this, 'generateScreenShot' )
								 );
			}
			add_submenu_page( 'manage_child_themes',
								'Screenshot Options',
								'Screenshot Options',
								$capability,
								'ScreenshotOptions',
								Array( $this, 'pas_cth_Options' )
							 );
		}
		// WriteOption( ) displays an option on the pasChildThemes options page.
		function loadAvailableFonts( ) {
			$fonts = [];
			$fonts_folder = $this->pluginDirectory['path'] . "assets/fonts";
			$folder_files = scandir( $fonts_folder );
			foreach ( $folder_files as $file ) {
				if ( strtoupper( pathInfo( $fonts_folder . '/' . $file, PATHINFO_EXTENSION ) ) === "TTF" ) {
					$meta = new pas_cth_FontMeta( $fonts_folder . '/' . $file );
					$fontName = $meta->getFontName( );
					$sampleImage = $this->getFontSample( $fonts_folder . '/' . $file, $fontName );
					$fontArgs = [
									'fontFile-base'=>basename( $file, ".ttf" ).PHP_EOL,
									'fontName'=>$fontName
								];
					array_push( $fonts, $fontArgs );
					unset ( $fontArgs );
					unset( $meta );
				}
			}
			delete_option( 'pas_cth_fontList' );
			add_option( 'pas_cth_fontList', $fonts );
			return $fonts;
		}

		function WriteOption( $args ) {
			$label			= ( array_key_exists( 'label', $args ) ? $args['label'] : "" );
			$optionName		= ( array_key_exists( 'optionName', $args ) ? $args['optionName'] : "" );
			$defaultValue	= ( array_key_exists( 'default', $args ) ? $args['default'] : "" );
			$defaultFont	= ( array_key_exists( 'defaultFont', $args ) ? $args['defaultFont'] : "['fontName'=>'Roboto Medium', 'fontFile-base'=>'Roboto-Medium']" );
			$selectOptions	= ( array_key_exists( 'selectOptions', $args ) ? $args['selectOptions'] : "" );
			$readonly		= ( array_key_exists( 'readonly', $args ) ? " READONLY " : "" );
			$skipwrite		= ( array_key_exists( 'skipwrite', $args ) ? $args['skipwrite'] : false );
			$ifColorPicker =
				( array_key_exists( 'colorPicker', $args ) ? $args['colorPicker'] : false );

			$dots = DOTS; // string of periods. Will overflow the div.
			$optionValue = get_option( "pas_cth_$optionName", $defaultValue );
			$color_picker_parameters = ( array_key_exists( 'cp_parameters', $args ) ? $args['cp_parameters'] : [] );


			// {$crlf} is a carriage return, line feed. When WP_DEBUG == true, the HTML output will
			// be made to be readable in the view source window. It helped with debugging.
			$crlf = $this->libraryFunctions->crlf();
			if ( array_key_exists( 'type', $args ) ) {
				switch ( strtolower( $args['type'] ) ) {
					case "input":
						$formElement =
							 "<input type='text' "
							. " name='$optionName' "
							. " value='$optionValue' "
							. " onfocus='javascript:pas_cth_js_showColorPicker( this );' "
							. ( array_key_exists( 'showColor', $args ) ?
								( $args['showColor'] ?
									" style='background-color:$optionValue;color:" . $this->colorPicker->invertColor( $optionValue, true ) . ";' " :
									"" ) :
								"" )
							. $readonly . " >";
						break;
					case "colorpicker":
						$abbrev = $color_picker_parameters['abbreviation'];
						$initial_color = $color_picker_parameters['initial_color'];
						$heading = $color_picker_parameters['heading'];
						$rgb = $this->libraryFunctions->getColors( $initial_color );

						$formElement = <<< "COLORPICKER"
							<input type='hidden' id='{$abbrev}_initial_color' value='{$initial_color}'>
							<input type='hidden' id='{$abbrev}_heading' value='$heading'>
							<div class='colorPickerHeader'>$heading</div>
							<div class='colorPickerContainer'>

								<div class='grid-item item1' id='{$abbrev}_rval_cell' style='background-color:{$rgb["redColor"]};'>
									<span id='{$abbrev}_redName' class='colorName'>R</span>
									<br>
									<input id='{$abbrev}_rval' type='text' class='rval' value="{$rgb['red']}" onfocus='javascript:this.select();' onblur='javascript:setRed(this);'>
								</div>

								<div class='grid-item item2' id='{$abbrev}_gval_cell' style='background-color:{$rgb["greenColor"]};'>
									<span id='{$abbrev}_greenName' class='colorName'>G</span>
									<br>
									<input id='{$abbrev}_gval' type='text' class='gval' value="{$rgb['green']}" onfocus='javascript:this.select();' onblur='javascript:setGreen(this);'>
								</div>

								<div class='grid-item item3' id='{$abbrev}_bval_cell' style='background-color:{$rgb["blueColor"]};'>
									<span id='{$abbrev}_blueName' class='colorName'>B</span>
									<br>
									<input id='{$abbrev}_bval' type='text' class='bval' value="{$rgb['blue']}" onfocus='javascript:this.select();' onblur='javascript:setBlue(this);'>
								</div>

								<div class='grid-item item4' id='{$abbrev}_hexval_cell' style='background-color:{$initial_color};'>
									<span id='{$abbrev}_hexName' class='colorName'>HexCode</span>
									<br>
									<input id='{$abbrev}_hexval' type='text' class='hexval' value='{$initial_color}' onfocus='javascript:this.select();' onblur='javascript:setHex(this);'>
								</div>

								<div class='grid-item item5' id='{$abbrev}_redSlider_cell'>
									<input id='{$abbrev}_redSlider' class='slider-red' type='range' min='0' max='255' value='{$rgb['red']}' oninput='javascript:updateColorPicker("{$abbrev}");'>
								</div>

								<div class='grid-item item6' id='{$abbrev}_greenSlider_cell'>
									<input id='{$abbrev}_greenSlider' class='slider-green' type='range' min='0' max='255' value='{$rgb['green']}' oninput='javascript:updateColorPicker("{$abbrev}");'>
								</div>

								<div class='grid-item item7' id='{$abbrev}_blueSlider_cell'>
									<input id='{$abbrev}_blueSlider' class='slider-blue' type='range' min='0' max='255' value='{$rgb['blue']}' oninput='javascript:updateColorPicker("{$abbrev}");'>
								</div>

								<div class='grid-item item8' id='{$abbrev}_lightDark_buttons_cell'>
									<input id='{$abbrev}_darkerBTN' class='darkerBTN' type='button' value='<<< darker' onclick='javascript:makeItDarker(this);'>
									<input id='{$abbrev}_lighterBTN' class='lighterBTN' type='button' value='lighter >>>' onclick='javascript:makeItLighter(this);'>
								</div>

								<div class='grid-item item9' id='{$abbrev}_saveButton_cell' style='background-color:{$initial_color};'>
									<span class='buttonBox'>
										<input disabled data-abbr='{$abbrev}' id='{$abbrev}_saveButton' type='button' value='SAVE' class='saveButton' onclick='javascript:saveColor(this);'>
										<input disabled id='{$abbrev}_resetButton' type='button' value='Reset' class='resetButton' onclick='javascript:resetColor(this);'>
									</span>
								</div>
								<div class='grid-item item10' id='{$abbrev}_colorBlocks_cell'>
COLORPICKER;
						$webColors =
							[
								"white"		=>	"#FFFFFF",
								"silver"	=>	"#C0C0C0",
								"gray"		=>	"#808080",
								"black"		=>	"#000000",
								"red"		=>	"#FF0000",
								"maroon"	=>	"#800000",
								"yellow"	=>	"#FFFF00",
								"olive"		=>	"#808000",
								"lime"		=>	"#00FF00",
								"green"		=>	"#008000",
								"aqua"		=>	"#00FFFF",
								"teal"		=>	"#008080",
								"blue"		=>	"#0000FF",
								"navy"		=>	"#000080",
								"fuchsia"	=>	"#FF00FF",
								"purple"	=>	"#800080"
							];
						$formElement .= "<div class='color-grid'>";
						foreach ($webColors as $color => $hexColorCode) {
							$formElement .= "<span data-abbr='{$abbrev}' class='color-item color_{$color}' onclick='javascript:setWebColor(this, \"{$hexColorCode}\");'>&nbsp;</span>&nbsp;";
						}
						$formElement .= "</div>"; // ends color-grid
						$formElement .= "</div>"; // ends grid-item item10
						$formElement .= "</div>"; // ends grid container

						echo $formElement;
						break;


					case "imageselect":
						$nofont = false;
						if ( 0 === strlen( $defaultFont['fontName'] ) ) {
							$defaultFont = ['fontName'=>'Choose Your Font', 'fontFile-base'=>''];
							$nofont = true;
						}
						if ( ! $nofont ) {
							$imgSrc = "<img id='sampleFontImage' src='" . $this->pluginDirectory['url'] . "assets/fonts/samples/" . $defaultFont['fontFile-base'] . ".png" . "'>";
						} else {
							$imgSrc = "";
						}

// HereDocs String for the text-box portion of the drop-down-list box
					$formElement = <<< "FONTTEXTBOX"
					{$crlf}<!-- ******************************************* -->{$crlf}
					<div class='colorPickerHeader'>$label</div>
					<div id='imageDropDown' onclick='javascript:showDropDown( "listDropDown" );'>{$crlf}
						<span class='imageSelectRow'>{$crlf}
							<span class='isRowCol1' id='selectedFontName'>{$crlf}
								{$defaultFont['fontName']}
							</span>{$crlf}
							<span class='isRowCol2' id='selectedFontSample'>{$crlf}
								{$imgSrc}{$crlf}
							</span>{$crlf}
							<span class='isRowCol3'>{$crlf}&nbsp;{$crlf}</span>{$crlf}
						</span>{$crlf}
					</div>{$crlf}<!-- End of id='imageDropDown' -->{$crlf}
					{$crlf}<!-- ******************************************* -->{$crlf}
					<div class='listDropDown' id='listDropDown'>{$crlf}
FONTTEXTBOX;

						foreach ( $selectOptions as $row ) {
							$jsdata =
								[
									'data-row'=>$row,
									'text-box'=>'imageDropDown',
									'list-box'=>'listDropDown',
									'url'=>$this->pluginDirectory['url'] . "assets/fonts/samples/"
								];
							$jsdata = json_encode( $jsdata );
							$src = $this->pluginDirectory['url'] .
								 'assets/fonts/samples/'		 .
								 $row['fontFile-base']		 . '.png';

							$imgSrc = "<img src='$src'>";

// HereDocs String for the list-box portion of the drop-down-list box.
// isRowCol3 is used strictly to provide spacing so the scrollbar doesn't hide part of the image.
						$formElement .= <<< "FONTLISTBOX"
							<div class='imageSelectRow' data-font='{$jsdata}' onclick='javascript:selectThisFont( this );'>{$crlf}
								<span class='isRowCol1'>{$crlf}
									{$row['fontName']}{$crlf}
								</span>{$crlf}
								<span class='isRowCol2'>{$crlf}
									{$imgSrc}{$crlf}
								</span>{$crlf}
								<span class='isRowCol3'>{$crlf}&nbsp;{$crlf}</span>{$crlf}
							</div>{$crlf}
FONTLISTBOX;
						}
						// These two lines MUST be outside the loop.
						$formElement .= "{$crlf}</div><!-- end of class='listDropDown' -->{$crlf}" .
										"{$crlf}<!-- ******************************************* -->{$crlf}";
						echo $formElement;
						break;
				} // end of switch( ) statement
			} else {
				$formElement = "<input type='text' " .
					 " name='" . esc_attr( $optionName ) . "' " .
					 " value='" . esc_attr( $optionValue ) . "' " .
					 " onblur='javascript:pas_cth_js_SetOption( this );' " .
					 " $readonly >";
			}

			$outputString = <<<"OPTION"
			{$crlf}<!-- start of class='pct' -->{$crlf}
			<div class='pct'>{$crlf}
				<span class='pctOptionHeading'>{$crlf}
					<span class='nobr'>{$label}<span class='dots'>$dots</span></span>{$crlf}
				</span>{$crlf}
				<span class='pctOptionValue'>{$crlf}
					{$formElement}{$crlf}
				</span>{$crlf}
			</div>{$crlf}<!-- end of class='pct' -->{$crlf}
OPTION;
			if ($skipwrite) {
				$outputString = "";
			}

			return ( $outputString );
		}

		function enumerateThemes( ) {
			$themes = array( );

			// Loads all theme data
			$all_themes = wp_get_themes( );

			// Loads theme names into themes array
			foreach ( $all_themes as $theme ) {
				$name = $theme->get( 'Name' );
				$stylesheet = $theme->get_stylesheet( );

				if ( $theme->parent( ) ) {
					$status = true;
				} else {
					$status = false;
				}
				$parent = $theme->get( 'Template' );
				$parentStylesheet = $theme->get_stylesheet( );

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
		function pas_cth_Options( ) {
			echo "<h1>Screen Shot Options</h1>";
			echo "<p id='notice'>";
			echo "If you make changes here and your screenshot.png doesn't change when you ";
			echo "generate it, clear your browser's image cache.";
			echo "</p>";
			echo $this->WriteOption(
				[
					'label'		 => 'Font: ',
					'optionName' => 'font',
					'defaultFont'=> get_option( 'pas_cth_font', unserialize( PAS_CTH_DEFAULT_FONT ) ),
					'type'		 => 'imageselect',
					'skipwrite'	 => true,
					'selectOptions'	=> $this->loadAvailableFonts( ),
				] );
			echo $this->WriteOption(
				[
					'label'		=> 'Text Color: ',
					'optionName'=> 'fcColor',
					'default'	=> get_option( 'pas_cth_fcc', PAS_CTH_DEFAULT_SCREENSHOT_BCCOLOR ),
					'type'		=> 'colorpicker',
					'skipwrite' => true,
					'cp_parameters' =>
						[
							'initial_color'	=> get_option('pas_cth_fcc', PAS_CTH_DEFAULT_SCREENSHOT_FCCOLOR ),
							'heading'		=> 'Text Color: ',
							'abbreviation'	=> 'fcc'
						]
				] );
			echo $this->WriteOption(
				[
					'label'		=> 'Background Color: ',
					'optionName'=> 'bcColor',
					'default'	=> get_option( 'pas_cth_bcc', PAS_CTH_DEFAULT_SCREENSHOT_BCCOLOR ),
					'type'		=> 'colorpicker',
					'skipwrite' => true,
					'cp_parameters' =>
						[
							'initial_color'	=> get_option('pas_cth_bcc', PAS_CTH_DEFAULT_SCREENSHOT_BCCOLOR ),
							'heading'		=> 'Background Color: ',
							'abbreviation'	=> 'bcc',
						]
				] );
			echo "<div id='popupMessageBox'></div>";

			// Dummy button. Options are saved onblur event for each option. This button simply
			// forces an onblur event to be fired from the last option that had focus.
			// No longer required.
//			echo "<input type='button' class='blueButton' value='Save Options'>";
		}
		/* Generates the screenshot.png file in the child theme, if one does not yet exist.
		 * If changes to the options do not show up, clear your browser's stored images,
		 * files, fonts, etc.
		 */
		function generateScreenShot( ) {
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


			// All done. Reload the Dashboard Themes page.
			// Response buffering turned on so we can do this.
			wp_redirect( admin_url( "themes.php" ) );
		}

		// showActiveChildTheme( ) will display the list of files for the child theme
		// in the left-hand pane.
		function showActiveChildTheme( ) {
			$currentThemeInfo = $this->activeThemeInfo; // this is an object.
			if ( $this->activeThemeInfo->templateStylesheet ) {
				echo "<p class='pasChildTheme_HDR'>CHILD THEME</p>";
				echo "<p class='actionReminder'>";
				echo "Left Click a file to <u>REMOVE</u> it from the child theme.<br>";
				echo "Right Click or long press a file to <u>EDIT</u> it.";
				echo "</p>";
			}
			echo "<p class='themeName'>" . $this->activeThemeInfo->childThemeName . "</p>";

			$childThemeFolder = $this->activeThemeInfo->getChildFolder( );

			echo "<div class='innerCellLeft'>";
			$this->listFolderFiles( $childThemeFolder, PAS_CTH_CHILDTHEME );
			echo "</div>";
		}

		// showActiveParentTheme( ) will display the list of files for the template theme
		// in the right-hand pane.
		function showActiveParentTheme( ) {
			echo "<p class='pasChildTheme_HDR'>TEMPLATE THEME</p>";
				echo "<p class='actionReminder'>";
				echo "Left Click a file to <u>COPY</u> it to the child theme.<br>";
				echo "Right Click or long press a file to <u>EDIT</u> it.";
				echo "</p>";
			echo "<p class='themeName'>" . $this->activeThemeInfo->templateThemeName . "</p>";

			$parentFolder = $this->activeThemeInfo->getTemplateFolder( );

			echo "<div class='innerCellRight'>";
			$this->listFolderFiles( $parentFolder, PAS_CTH_TEMPLATETHEME );
			echo "</div>";
		}
		function showCreateChildThemeForm() {
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

			echo "<div class='createChildThemeBox'>";

			echo "<p class='warningHeading'>Warning</p><br><br>";
			echo "The current theme: '<i><b>" . $this->activeThemeInfo->childThemeName . "</i></b>'";
			echo " is <u>not</u> a child theme.";
			echo "<br><br>"; // replace with CSS in future release;
			echo "Do you want to create a child theme?";
			echo "<br><br>"; // replace with CSS in future release;
			echo "Fill out the following form to create a child theme.<br>";
			echo "The only required fields are the <i>Child Theme Name</i> and the <i>Template Theme Name</i>";
			echo "<div class='createChildThemeBoxForm'>";
			echo "<div class='createChildThemeBoxForm_HDR'>Create Child Theme</div>";
			echo "<form>";
			echo "<input type='hidden' name='themeRoot' value='" . $this->activeThemeInfo->childThemeRoot . "'>";
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
			echo "<div class='buttonRow'>";
			echo "<input type='button' ";
			echo " value='Create Child Theme' ";
			echo " class='blueButton' ";
			echo " onclick='javascript:pas_cth_js_createChildTheme( this );'>";
			echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
			echo "<input type='button' ";
			echo " value='Reset' ";
			echo " class='blueButton' ";
			echo " onclick='javascript:this.form.reset();'>";
			echo "</div>";

			echo "</div>";
			echo "</form>";
			echo "</div>";
		}
		/*
		 *	manage_child_themes is the main driver function. This function is called from
		 * the Dashboard menu option 'Child Themes Helper'. This function either:
		 *	1 ) Displays the file list for the child theme and the file list for the template theme or
		 *	2 ) If the currently active theme is NOT a child theme, it displays the "form" to create a new
		 *	 child theme.
		 */
		function manage_child_themes() {
			if (defined("DEMO_CAPABILITY")) {
				$capability = constant("DEMO_CAPABILITY");
			} else {
				$capability = "manage_options";
			}
			if ( ! current_user_can( $capability ) ) { exit; }

			if ( ! $this->activeThemeInfo->isChildTheme ) {
				/* Current active theme is not a child theme.
				 * Prompt to create a child theme.
				 * This is set up to look like a typical HTML <form>,
				 * but it is not processed as one.
				 * We want to avoid refreshing the page so the
				 * output from the wp_ajax_createChildTheme function will be displayed.
				 */
				$this->showCreateChildThemeForm();
				return false;
			}

			$jsdata =
				[
					'childThemeRoot'	=>	$this->activeThemeInfo->childThemeRoot,
					'templateThemeRoot' =>	$this->activeThemeInfo->templateThemeRoot,
					'childStylesheet'	=>	$this->activeThemeInfo->childStylesheet,
					'templateStylesheet'=>	$this->activeThemeInfo->templateStylesheet,
				];
			$jsdata = json_encode($jsdata);
			echo "<div id='jsdata' style='display:none;' data-jsdata='$jsdata'></div>";

			echo "<div id='pas_cth_content'>";

			echo "<div id='themeGrid' class='pas-grid-container'>";
			echo "<div class='pas-grid-left-column'>";
			echo "	<div class='childPrompt' id='childPrompt' onclick='javascript:showChild();'>CHILD</div>";
			echo "	<div class='parentPrompt' id='parentPrompt' onclick='javascript:showParent();'>PARENT</div>";
			echo "</div>";
			echo "<div class='pas-grid-item-child' id='childGrid'>"; // Start grid item 1

			// Shows file list in the left pane
			$this->showActiveChildTheme( );

			echo "</div>"; // end grid item 1

			echo "<div class='pas-grid-item-parent' id='parentGrid'>"; // start grid item 2

			// Shows file list in the right pane
			$this->showActiveParentTheme( );

			echo	"</div>"; // end grid item 2
			echo	"</div>"; // end grid container
			// HoverPrompt is used during mouseovers on devices wider than 829px;
			// editFile is used when editting a file.
			// Both will be sized and positioned dynamically with Javascript
			echo	"<div id='hoverPrompt'></div>";

			echo	"<div id='editFile' data-gramm='false' >"
				.	"	<input type='hidden' id='directory' value=''>"
				.	"	<input type='hidden' id='file'	value=''>"
				.	"	<input type='hidden' id='themeType' value=''>"
				.	"	<input type='hidden' id='readOnlyFlag' value='false'>"
				.	"	<input type='hidden' id='currentFileExtension' value=''>"
				.	"<input type='button' value='Save File' disabled id='ef_saveButton' onclick='javascript:pas_cth_js_saveFile();'>"
				.	"<p id='ef_readonly_msg'>File is READ ONLY. Changes WILL NOT BE SAVED.</p>"
				.	"<p id='ef_filename'>FILENAME</p>"
				.	"<input type='button' value='Close File' id='ef_closeButton' onclick='javascript:pas_cth_js_closeEditFile();'>"
				.	(constant('WP_DEBUG') && defined('PLUGIN_DEVELOPMENT') && constant('PLUGIN_DEVELOPMENT') == "YES" ? "<input type='button' value='DEBUG' id='ef_debug_button' onclick='javascript:debug(this);'>" : "")
				.	(constant('WP_DEBUG') && defined('PLUGIN_DEVELOPMENT') && constant('PLUGIN_DEVELOPMENT') == "YES" ? "<input type='button' value='HEXDUMP' id='ef_hexdump_button' onclick='javascript:pas_cth_js_hexdump();'>" : "")
				.	"	<div id='editBox' data-gramm='false' spellcheck='false' autocapitalize='false' autocorrect='false' role='textbox' oninput='javascript:editBoxChange();'>"
				.	"	</div>"
				.	"</div>";

			echo "</div>"; // end of <div id='pas_cth_content'>

			echo	"<div id='savePrompt'>"
				.	"File has changed.<br>Do you want to save it?<br><br>"
				.	"	<input id='sp_saveButton' type='button' onclick='javascript:pas_cth_js_saveFile();' value='Save'>"
				.	"	&nbsp;&nbsp;&nbsp;"
				.	"	<input id='sp_closeButton' type='button' onclick='javascript:pas_cth_js_closeEditFile();' value='No Save'>"
				.	"</div>";
		}


		/*
		 * stripRoot( )
		 * The listFolderFiles( ) function takes a full physical path as a parameter.
		 * But the full path to the file must be known when the user clicks on a file
		 * in the file list. But the full path up to and including the "themes" folder
		 * is constant.
		 *
		 * The stripRoot( ) function removes everything in the $path up to and not including
		 * the theme's stylesheet folder. In other words, stripRoot( ) strips the theme root
		 * from the file path so that listFolderFiles( ) when writing out a file, doesn't have
		 * to include the full path in every file.
		 *
		 * stripRoot( ) takes the full $path and the $themeType as
		 * parameters.
		 */
		function stripRoot( $path, $themeType ) {
			// Strip the stylesheet also (+1).
			$sliceStart = $this->activeThemeInfo->getFolderCount( $themeType ) + 1;

			$folderSegments = explode( PAS_CTH_SEPARATOR, $path );
			$folderSegments = array_slice( $folderSegments, $sliceStart );
			$path = implode( PAS_CTH_SEPARATOR, $folderSegments );

			return $path;
		}

		/* The listFolderFiles( ) function is the heart of the child theme and template theme
		 * file listings.
		 * It is called recursively until all of the themes' files are found.
		 * It excludes the ".", "..", and ".git" folders, if they exist.
		 * $dir is the full rooted path to the theme's stylesheet.
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
					echo "<li><p class='pas_cth_directory'>" . $ff . "</p>" . $this->crlf;
					if( is_dir( $dir.PAS_CTH_SEPARATOR.$ff ) ) {
						$this->listFolderFiles( $dir.PAS_CTH_SEPARATOR.$ff, $themeType );
					}
				} else {
					// strips theme root, leaving stylesheet and sub folders and file.
					$shortDir = $this->stripRoot( $dir, $themeType );

					/* $jsdata or JavaScript data will be stuffed into the data-jsdata
					 * HTML attribute and written out as part of the file list. This way,
					 * on the onclick event, the file path and themeType will be passed to
					 * the pas_cth_js_selectFile( ) javascript function, and then
					 * on to the pas_cth_AJAXFunctions::selectFile( ) PHP function via an AJAX call.
					 */
					$jsdata = json_encode(
											[
											 'directory'=>$shortDir,
											 'file'=>$ff,
											 'themeType'=>$themeType,
											 'extension'=>pathinfo( $dir.PAS_CTH_SEPARATOR.$ff )['extension'],
											 'allowedFileTypes'=>get_option('pas_cth_edit_allowedFileTypes', ['php', 'js', 'css', 'txt', 'svg']),
											]
										 );
					echo "<li>"
						 . "<p class='file' "
						 . " data-jsdata='" . esc_attr( $jsdata ) . "' "
						 . " onclick='javascript:pas_cth_js_selectFile( this );' "
						 . " oncontextmenu='javascript:pas_cth_js_editFile( this );return false;' "
						 . " onmouseover='javascript:pas_cth_js_mouseOver( this );' "
						 . " onmouseout='javascript:pas_cth_js_mouseOut( this );' "
						 . ">" . $this->crlf;
					echo $ff . $this->crlf;
					echo "</p>" . $this->crlf;
				}
				echo "</li>" . $this->crlf;
			}
			echo '</ul>' . $this->crlf;

			echo "</div>" . $this->crlf;
		}
		function getFontSample( $fontFile, $fontName ) {
			$imageSize = ['width'=>300, 'height'=>50];
			$img = imagecreate( $imageSize['width'], $imageSize['height'] );
			$childThemeName = $this->activeThemeInfo->childThemeName;

			$bcColor	= "#FFFFFF";
			$rgb		= $this->libraryFunctions->getColors( $bcColor );
			$background = imagecolorallocate( $img, $rgb['red'], $rgb['green'], $rgb['blue'] );

			$fcColor	= "#000000";
			$rgb		= $this->libraryFunctions->getColors( $fcColor );
			$text_color = imagecolorallocate( $img, $rgb['red'], $rgb['green'], $rgb['blue'] );

			$font = $fontFile;
			$sampleText = $childThemeName;

			$xPos		= 0;
			$yPos		= 10;
			$sizes		= $this->libraryFunctions->getMaxFontSize(
							[
								'font'		=>	$font,
								'fontName'	=>	$fontName,
								'imageSize'	=>	$imageSize,
								'sampleText'=>	$sampleText,
								'xPos'		=>	$xPos,
								'yPos'		=>	$yPos,
							] );
			$angle		= 0;
			$bbox = imagefttext( $img,
								 $sizes['maxFontSize'],
								 $angle,
								 0,
								 45,
								 $text_color,
								 $font,
								 $sampleText );

			if ( ! file_exists( $this->pluginDirectory['path'] . 'assets/fonts/samples' ) ) {
				mkdir( $this->pluginDirectory['path'] . 'assets/fonts/samples' );
			}
			$fontSampleImageName =
					"assets/fonts/samples/" . trim( basename( $fontFile, ".ttf" ).PHP_EOL ) . ".png";
			$outFile = $this->pluginDirectory['path'] . $fontSampleImageName;

			imagepng( $img, $outFile );

			imagecolordeallocate( $img, $text_color );
			imagecolordeallocate( $img, $background );

			return ( $fontSampleImageName );
		}

	}
}
