<?PHP
if ( ! class_exists( 'pas_cth_ChildThemesHelper' ) ) {
	class pas_cth_ChildThemesHelper {
		public $pluginDirectory;
		public $pluginName;
		public $pluginFolder;
		public $activeThemeInfo;
		public $allThemes;
		public $colorPicker;
		public $fontSamples; // Array of sample font images, to be used in pas_cth_Options();

		function __construct( $args ) {
			$this->pluginDirectory	= $args['pluginDirectory'];
			$this->pluginName		= $args['pluginName'];
			$this->pluginFolder		= $args['pluginFolder'];
			$this->activeThemeInfo	= $args['activeThemeInfo'];
			$this->allThemes		= $this->enumerateThemes();
			$this->colorPicker		= $args['colorPicker'];
			$this->fontSampleImages	= [];
		}
		function __destruct() {
			foreach ($this->fontSampleImages as $img) {
				imagedestroy(  $img  );
			}
			unset($this->fontSampleImages);
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
		function loadAvailableFonts() {
			$fonts = [];
			$fonts_folder = $this->pluginDirectory['path'] . "assets/fonts";
			$folder_files = scandir($fonts_folder);
			foreach ($folder_files as $file) {
				if (strtoupper(pathInfo($fonts_folder . '/' . $file, PATHINFO_EXTENSION)) === "TTF") {
					$meta = new FontMeta($fonts_folder . '/' . $file);
					$fontName = $meta->getFontName();
					$sampleImage = $this->getFontSample($fonts_folder . '/' . $file, $fontName);
					$fontArgs = [
									'fontFile-base'=>basename($file, ".ttf").PHP_EOL,
									'fontName'=>$fontName
								];
					array_push($fonts, $fontArgs);
					unset ($fontArgs);
					unset($meta);
				}
			}
			return $fonts;
		}

		function WriteOption( $args ) {
			$label			= (array_key_exists('label', $args) ? $args['label'] : "");
			$optionName		= (array_key_exists('optionName', $args) ? $args['optionName'] : "");
			$defaultValue	= (array_key_exists('default', $args) ? $args['default'] : "");
			$defaultFont	= (array_key_exists('defaultFont', $args) ? $args['defaultFont'] : "['fontName'=>'Roboto Medium', 'fontFile-base'=>'Roboto-Medium']");
			$selectOptions	= (array_key_exists('selectOptions', $args) ? $args['selectOptions'] : "");
			$readonly		= (array_key_exists( 'readonly', $args ) ? " READONLY " : "");
			$ifColorPicker =
				( array_key_exists( 'colorPicker', $args ) ? $args['colorPicker'] : false );

			$dots = DOTS; // string of periods. Will overflow the div.
			$optionValue = get_option( "pas_cth_$optionName", $defaultValue );

			if (constant('WP_DEBUG')) {
				$crlf = "\r\n";
			} else {
				$crlf = "";
			}

			if ( array_key_exists( 'type', $args ) ) {
				switch ( strtolower( $args['type'] ) ) {
					case "input":
						$formElement = "<input type='text' "
									 . "       name='$optionName' "
									 . "       value='$optionValue' "
									 . "       onfocus='javascript:pas_cth_js_showColorPicker(this);' "
									 . (array_key_exists('showColor', $args) ? ($args['showColor'] ? " style='background-color:$optionValue;color:" . $this->colorPicker->invertColor($optionValue, true) . ";' " : "") : "")
									 . $readonly . " >";
						break;
					case "select": // currently, only a font dropdown is required.
						$formElement =
							"<select name='" . esc_attr($optionName) . "' " .
							"        onblur='javascript:pas_cth_js_SetOption(this);' " .
							"        $readonly >" .
							"<option value=''>Choose the Font</option>";
						if ( array_key_exists( 'options', $args ) ) {
							foreach ( $options as $fontOption ) {
								$selected = ( $fontOption['fontFile-basename'] == $optionValue ? " SELECTED " : "" );
								$formElement .= "<option value='" . $fontOption['fontFile'] . "' $selected >" .
												$fontOption['fontName'] . "</option>";
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
						break;
					case "imageselect":
						$nofont = false;


						if (0 === strlen($defaultFont['fontName'])) {
							$defaultFont = ['fontName'=>'Choose Your Font', 'fontFile-base'=>''];
							$nofont = true;
						}
						if ( ! $nofont) {
							$imgSrc = "<img id='sampleFontImage' src='" . $this->pluginDirectory['url'] . "assets/fonts/samples/" . $defaultFont['fontFile-base'] . ".png" . "'>";
						} else {
							$imgSrc = "";
						}

// HereDocs String for the text-box portion of the drop-down-list box
					$formElement = <<< "FONTTEXTBOX"
					{$crlf}<!-- ******************************************* -->{$crlf}
					<div id='imageDropDown' onclick='javascript:showDropDown("listDropDown");'>{$crlf}
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

						foreach ($selectOptions as $row) {
							$jsdata =
								[
									'data-row'=>$row,
									'text-box'=>'imageDropDown',
									'list-box'=>'listDropDown',
									'url'=>$this->pluginDirectory['url'] . "assets/fonts/samples/"
								];
							$jsdata = json_encode($jsdata);
							$imgSrc = "<img src='" . $this->pluginDirectory['url'] . 'assets/fonts/samples/' . $row['fontFile-base'] . '.png' . "'>";

// HereDocs String for the list-box portion of the drop-down-list box.
						$formElement .= <<< "FONTLISTBOX"
							<div class='imageSelectRow' data-font='{$jsdata}' onclick='javascript:selectThisFont(this);'>{$crlf}
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
						$formElement .= "$crlf~</div><!-- end of class='listDropDown' -->$crlf" .
										"$crlf<!-- ******************************************* -->$crlf";
						break;
				} // end of switch() statement
			} else {
				$formElement = "<input type='text' " .
					           "       name='" . esc_attr($optionName) . "' " .
					           "       value='" . esc_attr($optionValue) . "' " .
					           "       onblur='javascript:pas_cth_js_SetOption( this );' " .
					           "       $readonly >";
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
/*
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
*/
			echo $this->WriteOption(
				[
					'label'		=> 'Background Color: ',
					'optionName'=> 'bcColor',
					'default'=> get_option( 'pas_cth_bcColor', PAS_CTH_DEFAULT_SCREENSHOT_BCCOLOR ),
					'colorPicker'=> true,
					'type'		=> 'input',
					'showColor' => true
				] );

			echo $this->WriteOption(
				[
					'label'		=> 'Text Color: ',
					'optionName'=> 'fcColor',
					'default'=> get_option( 'pas_cth_fcColor', PAS_CTH_DEFAULT_SCREENSHOT_FCCOLOR ),
					'colorPicker'=> true,
					'type'		=> 'input',
					'showColor' => true
				] );

			echo $this->WriteOption(
				[
					'label'		 => 'Font: ',
					'optionName' => 'font',
					'defaultFont'=> get_option( 'pas_cth_font', unserialize(PAS_CTH_DEFAULT_FONT) ),
					'type'		 => 'imageselect',
					'selectOptions'	=> $this->loadAvailableFonts(),
				] );

			// Dummy button. Options are saved onblur event for each option. This button simply
			// forces an onblur event to be fired from the last option that had focus.

			echo "<input type='button' class='blueButton' value='Save Options'>";
		}
		/* Generates the screenshot.png file in the child theme, if one does not yet exist.
		 * If changes to the options do not show up, clear your browser's stored images,
		 * files, fonts, etc.
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

			// Shows file list in the left pane
			$this->showActiveChildTheme();

			echo "</div>"; // end grid item 1

			echo "<div class='pas-grid-item'>"; // start grid item 2

			// Shows file list in the right pane
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
					echo "<li><p class='pas_cth_directory'>" . $ff . "</p>";
					if( is_dir( $dir.PAS_CTH_SEPARATOR.$ff ) ) {
						$this->listFolderFiles( $dir.PAS_CTH_SEPARATOR.$ff, $themeType );
					}
				} else {
					// strips theme root, leaving stylesheet and sub folders and file.
					$shortDir = $this->stripRoot( $dir, $themeType );

					/* $jsdata or JavaScript data will be stuffed into the data-jsdata
					 * HTML attribute and written out as part of the file list. This way,
					 * on the onclick event, the file path and themeType will be passed to
					 * the pas_cth_js_selectFile() javascript function, and then
					 * on to the pas_cth_AJAXFunctions::selectFile() PHP function via an AJAX call.
					 */
					$jsdata = json_encode(
											[
											 'directory'=>$shortDir,
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
		function getFontSample($fontFile, $fontName) {
			$imageSize = ['width'=>300, 'height'=>50];
			$img = imagecreate(  $imageSize['width'], $imageSize['height']  );
			$childThemeName = $this->activeThemeInfo->childThemeName;

			$bcColor	= "#FFFFFF";
			$rgb		= pas_cth_getColors( $bcColor );
			$background = imagecolorallocate(  $img, $rgb['red'], $rgb['green'], $rgb['blue']  );

			$fcColor	= "#000000";
			$rgb		= pas_cth_getColors( $fcColor );
			$text_color = imagecolorallocate(  $img, $rgb['red'], $rgb['green'], $rgb['blue']  );

			$font = $fontFile;
			$sampleText = $childThemeName;

			$xPos		= 0;
			$yPos		= 10;
			$sizes		= $this->getFontSize( [
												'fontName'=>$fontName,
												'font'=>$font,
												'imageSize'=>$imageSize,
												'sampleText'=>$sampleText,
												'xPos'=>$xPos,
												'yPos'=>$yPos,
											  ] );
			$angle		= 0;
			$bbox = imagefttext(  $img,
								  $sizes['maxFontSize'],
								  $angle,
								  0,
								  45,
								  $text_color,
								  $font,
								  $sampleText );

			$fontSampleImageName =
					"assets/fonts/samples/" . trim(basename($fontFile, ".ttf").PHP_EOL) . ".png";
			$outFile = $this->pluginDirectory['path'] . $fontSampleImageName;

			imagepng(  $img, $outFile  );

			imagecolordeallocate(  $img, $text_color  );
			imagecolordeallocate(  $img, $background  );

			return ($fontSampleImageName);
		}

		function getSize( $item ) {
			/* imagettfbox() returns an array of indices representing the x and y coordinates for
			 * each of the 4 corners of an imaginary box that bounds the $item. The definition of
			 * what each indice of $boundingBox represents may be found here:
			 * http://php.net/manual/en/function.imagettfbbox.php
			 */
			$boundingBox = imagettfbbox( $item['fontSize'], 0, $item['fontName'], $item['string'] );
			$width = abs( $boundingBox[2] - $boundingBox[0] );
			$height = abs( $boundingBox[1] - $boundingBox[7] );

			return ['width'=>$width, 'height'=>$height];
		}
		function sampleIsTooBig($args) {
			$imageSize = $args['imageSize'];
			$sampleSize = $args['sampleSize'];
			$result = false;

			if ($imageSize['width'] < $sampleSize['width'] || $imageSize['height'] < $sampleSize['height']) {
				$result = true;
			}

			return $result;

		}
		function sampleIsTooSmall($args) {
			$imageSize = $args['imageSize'];
			$sampleSize = $args['sampleSize'];
			$result = false;
			if ($sampleSize['width'] < $imageSize['width'] && $sampleSize['height'] < $imageSize['height']) {
				$result = true;
			}

			return $result;
		}
		/*
		 * Calculate the largest font size that will fit in the $imgWidth x $imgHeight space.
		 */
		function getFontSize($args) {
			$font = $args['font'];
			$fontNamey = $args['fontName'];
			$imageSize = $args['imageSize'];
			$sampleText = $args['sampleText'];

			// Presumably this font size is bigger than what will fit in the 300 x 50 space.
			$fontSize = 70;
			// reduce the font size until it's smaller than the space.
			do {
				$fontSize -= 10;
				$size = $this->getSize(['fontSize'=>$fontSize, 'fontName'=>$font, 'string'=>$sampleText]);
			} while ($this->sampleIsTooBig(['font'=>$args['fontName'],'fontSize'=>$fontSize,'imageSize'=>$imageSize, 'sampleSize'=>$size]));

			// increase the font size until it's bigger than the space
			while ($this->sampleIsTooSmall(['font'=>$args['fontName'],'fontSize'=>$fontSize, 'imageSize'=>$imageSize, 'sampleSize'=>$size])) {
				$fontSize += 2;
				$size = $this->getSize(['fontSize'=>$fontSize, 'fontName'=>$font, 'string'=>$sampleText]);
			}
			// At this point, we are 1 iteration of the above loop too big. Subtract 2 point sizes
			// And we have the largest font size that will fit in the 300 x 50 sample font space.
			$fontSize -= 2;
			$size = $this->getSize(['fontSize'=>$fontSize, 'fontName'=>$font, 'string'=>$sampleText]);
			$rtn = [
						'maxFontSize'=>$fontSize,
						'sampleWidth'=>$size['width'],
						'sampleHeight'=>$size['height'],
						'imageSize'=>$imageSize,
						'font'=>$font
					];
			return $rtn;
		}
	}
}
