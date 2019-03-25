<?PHP
if ( ! class_exists( 'pas_cth_ChildThemesHelper' ) ) {
	class pas_cth_ChildThemesHelper {
		public $pluginDirectory;
		public $pluginName;
		public $pluginFolder;
		public $activeThemeInfo;
		public $colorPicker;
		public $fontSamples; // Array of sample font images, to be used in pas_cth_Options( );
		public $fontList;
		public $dataBlock;
		public $libraryFunctions;
		private $crlf;
		private $demo_mode;
		private $args;
		private $Themes;
		private $defaultTab;

		function __construct( $args ) {
			$this->pluginDirectory	= $args['pluginDirectory'];
			$this->pluginName		= $args['pluginName'];
			$this->pluginFolder		= $args['pluginFolder'];

			$this->allThemes		= $this->enumerateThemes();

			$this->colorPicker		= $args['colorPicker'];
			$this->fontSampleImages	= [];
			$this->libraryFunctions = $args['libraryFunctions'];
			$this->crlf				= $this->libraryFunctions->crlf();
			$this->demo_mode		= (array_key_exists('demo_args', $args) ? $args['demo_args'] : null);

			$this->activeThemeInfo	= (array_key_exists('activeThemeInfo', $args) ? $args['activeThemeInfo'] : null);

			$this->Themes			= (array_key_exists('Themes', $args) ? $args['Themes']	: null);
			$this->defaultTab		= (array_key_exists('defaultTab', $args) ? $args['defaultTab'] : null);
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
			$uniqStr = ( constant( 'WP_DEBUG' ) ? "?cacheBuster=" . time() . "&" : "" );
			wp_enqueue_style( 	'pasChildThemes',
								$this->pluginDirectory['url'] . "css/style.css" . $uniqStr,
								false );
			if (defined('WP_DEBUG') && constant('WP_DEBUG') && defined('PLUGIN_DEVELOPMENT') && constant('PLUGIN_DEVELOPMENT') == "YES") {
				wp_enqueue_style(	'pasChildThemes3',
									$this->pluginDirectory['url'] . 'css/hexdump.css' . $uniqStr,
									false );
			}
			wp_enqueue_style(	'pasChildThemes2',
								$this->pluginDirectory['url'] . 'css/menu_page.css' . $uniqStr,
								false );
			wp_enqueue_style(	'pasChildThemes4',
								$this->pluginDirectory['url'] . 'css/tabs.css' . $uniqStr,
								false );
		}

		// Load the pasChildThemes Javascript script file
		function dashboard_scripts( ) {
			// Prevents browser from caching the stylesheet during development
			$cacheBuster = ( constant( 'WP_DEBUG' ) ? "?cacheBuster=" . time() . "&" : "" );
			$scripts =
				[
					[ 'script' => 'pasChildThemes.js',	'topLoad'	=> true,	'debugOnly'	=> false],
					[ 'script' => 'js_common_fn.js',	'topLoad'	=> true,	'debugOnly'	=> false ],
					[ 'script' => 'edit_file.js',		'topLoad'	=> true,	'debugOnly'	=> false ],
					[ 'script' => 'tabs.js',			'topLoad'	=> false,	'debugOnly'	=> false ],
					[ 'script' => 'hexdump.js',			'topLoad'	=> true,	'debugOnly' => true, ],
					[ 'script' => 'menu.js',			'topLoad'	=> true,	'debugOnly'	=> false,],
				];
			for ($ndx = 0; $ndx < count($scripts); $ndx++) {
				if ($scripts[$ndx]['topLoad']) {
					wp_enqueue_script ("pas_cth_script_{$ndx}",
									   $this->pluginDirectory['url'] . "js/{$scripts[$ndx]['script']}{$cacheBuster}",
									   false );
				} else {
					wp_register_script("pas_cth_script_{$ndx}", "{$this->pluginDirectory['url']}js/{$scripts[$ndx]['script']}{$cacheBuster}", '', '5.1', true);
					wp_enqueue_script("pas_cth_script_{$ndx}");
				}
			}
		}
		function isDemo() {
			$userlogin = "";
			if (defined("DEMO_USER")) {
				$userlogin = strtolower(constant("DEMO_USER"));
			}
			if ($userlogin == strtolower(wp_get_current_user()->user_login) && defined("DEMO_CAPABILITY")) {
				$capability = constant("DEMO_CAPABILITY");
			} else {
				$capability = "manage_options";
			}
			return $capability;
		}

		// pasChildThemes Dashboard Menu
		function dashboard_menu( ) {
			$cap = $this->isDemo();

			add_menu_page( 	'ChildThemesHelper',
							'Child Themes Helper',
							$cap,
							'manage_child_themes',
							Array( $this, 'pas_cth_tabPage' ),
							"",
							61 // appears just below the Appearances menu.
						 );
		}
		function NoActiveThemeMsg() {
			echo "<div class='noActiveThemeSet'>";
			echo "Active Theme Not Set<hr>"
				."Please visit the <i>Options</i> tab to set the active child theme.";
			echo "</div>";
		}
		function pas_cth_tabPage() {
			$crlf = $this->crlf;
			$tabInfo =
				[
					[
						'title'		=>	'Options',				
						'slug'		=>	'options', 
						'content'	=>	'Child Themes Helper options.',
						'default'	=>	false,
					],
					[
						'title'		=>	'Create Child Theme',	
						'slug'		=>	'create-child-theme',
						'content'	=>	'Create new child theme.',
						'default'	=>	false,
					],
					[
						'title'		=>	'Copy/Edit Theme Files',
						'slug'		=>	'copy-theme-files',
						'content'	=>	'Copy or edit files from the template theme to the child theme.',
						'default'	=>	false,
					],
					[
						'title'		=>	'Screenshot',
						'slug'		=>	'screenshot',
						'content'	=>	'Screenshot options and generation.',
						'default'	=>	false,
					],
				];
			if (defined('WP_DEBUG') && defined('PLUGIN_DEVELOPMENT') && constant('WP_DEBUG') && constant('PLUGIN_DEVELOPMENT')) {
				array_push($tabInfo, 
					[
						'title'		=>	'Theme Data',
						'slug'		=>	'theme-data',
						'content'	=>	"<div style='background-color:white;font-size:12pt;font-weight:bold;'><pre>" . print_r($this->Themes, true) . "</pre></div>",
						'default'	=> false,
					]);
			}
			foreach ($tabInfo as $key => $tab) {
				if ($tab['slug'] == $this->defaultTab) {
					$tabInfo[$key]['default'] = true;
				}
			}
			echo "<div class='tab'>{$crlf}";
			foreach ($tabInfo	as $tab) {
				echo "<button class='tablinks' data-tab='{$tab['slug']}' onclick='openCTHTab(this, event);' " . ($tab['default'] ? " id='defaultOpen' " : "") . ">{$tab['title']}</button>{$crlf}";
			}
			echo "</div>{$crlf}";

			echo "<div id='tabPage'>";

			foreach ($tabInfo as $tab) {
				echo "<div data-tab='{$tab['slug']}' class='tabcontent tab_inactive'>\r\n";

				switch ($tab['slug']) {
					case "create-child-theme":
						$this->showCreateChildThemeForm();
						break;

					case "copy-theme-files":
						// Copy Theme Files
						if ($this->activeThemeInfo != null) {
							$this->manage_child_themes("COPY");
						} else {
							$this->noActiveThemeMsg();
						}
						break;

					case "screenshot":
						if ($this->activeThemeInfo != null) {
							$this->pas_cth_Options();
						} else {
							$this->noActiveThemeMsg();
						}
						break;
					case "options":
						$this->loadOptionsPage();
						break;
					case "theme-data":
						echo "<h3>{$tab['title']}</h3>";
						echo "<p>{$tab['content']}</p>";
						break;
					
					default:
						echo "	<h3>{$tab['title']}</h3>\r\n";
						echo "	<p>{$tab['content']}</p>\r\n";
						break;
				}

				echo "</div>\r\n\r\n";
			}
			echo "</div>"; // tabPage
			echo "<div id='child-themes-helper-page'></div>";
		}
		function loadOptionsPage() {
			$blogName = get_blogInfo('name');
			$expertMode = (get_option("pas_cth_expert_mode", "FALSE") == "FALSE" ? "" : " CHECKED ");
			if ($expertMode) {
				$class = " class='hideHelp' ";
			} else {
				$class = "";
			}
			$abbr	= "<span class='abbreviatedPluginName'>CTH</span>";
			echo <<< "HELP"
<div class='pas_cth_expertMode'>
<input type='checkbox' {$expertMode} onclick='javascript:pas_cth_js_expertMode(this);'>Expert Mode
</div>
<div id='optionsHelp' {$class}>
	<h1>Welcome to the Child Themes Helper plugin.</h1>
	<br><br>
	The Child Themes Helper plugin for WordPress child theme development, (hereinafter: {$abbr}) was developed to aid website developers who have a need to modify the underlying PHP of their website's installed theme(s).
	<br><br>
	Many people new to WordPress will download a free theme that they like and then make it their own using the theme customization features.
	But they will soon discover that many of the changes that they want to make cannot be made using customization.
	<br><br>
	The next logical step is to modify the downloaded theme's internal PHP files.
	<br><br>
	
	That is a bad thing. When the developer(s) who created the free theme that they downloaded, updates their theme and upload it to the WordPress Theme repository, those new-to-WordPress website developers will get a message about the theme needing to be updated.
	And 10 seconds after they tap the link to update the theme, they will notice that all of their changes are gone.
	<br><br>Poof<br><br>
	Welcome to the world of Child Themes, a feature of WordPress designed to solve this exact problem -- how to make direct modifications to your installed themes.
	<br><br>
	Before proceeding, here are a couple of links that you will want to refer to 35 times per day.
	<ul>
		<li><a href='https://codex.wordpress.org/' target='_blank'>The WordPress Codex</a> -- an online WordPress manual and repository of all information about WordPress.</li>
		<li><a href='https://developer.wordpress.org/themes/advanced-topics/child-themes/' target='_blank'>Child Themes</a> -- an advanced dive into everything you will need to know about Child Themes.</li>
	</ul>

	A simple working definition of a child theme is that it is like a sub-theme where you put all of your changes so that when the original theme -- the Template theme or the Parent theme -- gets updated, your changes are not lost.
	WordPress loads your changed files instead of the original files of the parent theme.
	<br><br>
	But your changed files must have the same name and location in your child theme as they do in the original theme, or your child theme will not work.
	That is an important thought and it bears repeating.
	<br><br>
	<b>The changed files in your child theme, must be named the same and exist in the same location in your child theme as they do in the original theme.</b>
	<br><br>
	The concept is easy enough.
	But in practice, you copy a file, then close the window. Then you open the child theme, and try to remember the path to where the file goes, and create each folder and subsequent subfolders and then finally paste the file.
	The Child Theme Helper plugin was created because I, the developer of {$abbr} would invariably screw it up and my child theme would not work and I would spend hours trying to figure out why.
	<br><br>
	<h3>The Child Themes Helper plugin has 4 areas of primary functionality.</h3> For more information on each of these, review the information at the top of each tab.
	<ol>
		<li>Create a new child theme from any of the template themes installed on your website. {$abbr} requires the child theme to exist before you can copy files to it.</li>
		<li>Copy files from the original theme to the child theme. {$abbr} guarantees that the folder structure in the child theme matches the folder structure in the original theme and that the file being copied is named the same as the original file, including capitalization. {$abbr} <i><b>makes this process point-and-click simple</b></i>.</li>
		<li>Directly edit your child theme's files or view the original theme's files directly in your browser. This is not meant as a substitute for using an offline editor, just a mechanism to make quick changes or peer into a file.</li>
		<li>Generate a temporary graphic that will clearly identify your new child theme in the WordPress list of themes.</li>
	</ol>

	Before you can start copying files to your child theme, you have to specify which child theme is the active theme.
	<br><br>
	<b>The active child theme does <u>NOT</u> have to be an activated theme, but it does have to be a child theme</b>.
	<br><br>
	Below you will find a list of themes installed on your {$blogName} website. The lines with a radio button at the beginning may be selected as the active theme.
	This selection <u>does not</u> change which theme is activated. It only specifies to {$abbr} which child/parent theme that it should manipulate.
	<br><br>
	Once selected, the page will reload and you will be on the Copy/Edit Theme Files tab.

	</div>
HELP;
			echo "<div id='pas_cth_options'>";
			echo "<div style='font-size:14pt;font-weight:bold;padding-bottom:15px;'>Please select the child theme that you want to manipulate.</div>";
			echo "<table>";
			echo "<tr><th>&nbsp;</th><th>Child Themes</th><th>Template Themes</th></tr>";
			foreach ($this->Themes->childParentThemesList as $object) {
				echo "<tr>";
				if ($object['child'] != "") {
					$selected = ($object['childStylesheet'] == $this->Themes->pas_cth_active_theme ? " CHECKED " : "");
					echo "<td class='checkbox'>";
					echo "<input type='radio' {$selected} name='selectChild' onclick='setDefaultChildTheme(this, \"{$object['childStylesheet']}\");'>";
					echo "</td>";
				} else {
					echo "<td class='checkbox'>&nbsp;</td>";
				}
				echo "<td class='data'>" . $object['child'] . "</td>";
				echo "<td class='data'>" . $object['parent'] . "</td>";
				echo "</tr>";
			}
			echo "</table>";
			echo "</div>";
		}

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

			$dots = DOTS; // string of periods. Will intentionally overflow the div.
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
						$generateButton = "<input type='button' value='generate screenshot' class='blueButton' onclick='javascript:generateScreenShot(\"{$abbrev}\");'>";
						$rgb = $this->libraryFunctions->getColors( $initial_color );

						$formElement = <<< "COLORPICKER"
							<input type='hidden' id='{$abbrev}_initial_color' value='{$initial_color}'>
							<input type='hidden' id='{$abbrev}_heading' value='$heading'>
							<div class='colorPickerHeader'>$heading {$generateButton}</div>
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
		function pas_cth_Options() {
			echo <<< 'CLEARCACHE'
<h2>Generate a Temporary ScreenShot.png for your child theme.</h2>
<p id='notice'>
	Your browser will attempt to cache the screenshots that appear on your WordPress Themes page.
	If you generate a new temporary graphic for your child theme and you still see the old one on the Themes page,
	you must clear your browser's image cache to see your new graphic.
</p>
CLEARCACHE;
// '
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
//			echo "</div>";
			echo "<div id='popupMessageBox'></div>";

			// Dummy button. Options are saved onblur event for each option. This button simply
			// forces an onblur event to be fired from the last option that had focus.
			// No longer required.
//			echo "<input type='button' class='blueButton' value='Save Options'>";
		}

		// showActiveChildTheme( ) will display the list of files for the child theme
		// in the left-hand pane.
		function showActiveChildTheme() {
			$currentThemeInfo = $this->activeThemeInfo; // this is an object.
			if ( $this->activeThemeInfo->templateStylesheet ) {
				echo "<p class='pasChildTheme_HDR'>CHILD THEME</p>";
				echo "<p class='actionReminder'>";
				echo "Right Click or long press on a file to see an action menu";
				echo "</p>";
			}
			echo "<p class='themeName'>" . $this->activeThemeInfo->childThemeName . "</p>";

			$childThemeFolder = $this->activeThemeInfo->getChildFolder();

			echo "<div class='innerCellLeft'>";
			$this->listFolderFiles( $childThemeFolder, PAS_CTH_CHILDTHEME );
			echo "</div>";
		}

		// showActiveParentTheme( ) will display the list of files for the template theme
		// in the right-hand pane.
		function showActiveParentTheme( ) {
			echo "<p class='pasChildTheme_HDR'>TEMPLATE THEME</p>";
				echo "<p class='actionReminder'>";
				echo "Right Click or long press on a file to see an action menu";
				echo "</p>";
			echo "<p class='themeName'>" . $this->activeThemeInfo->templateThemeName . "</p>";

			$parentFolder = $this->activeThemeInfo->getTemplateFolder( );

			echo "<div class='innerCellRight'>";
			$this->listFolderFiles( $parentFolder, PAS_CTH_TEMPLATETHEME );
			echo "</div>";
		}
		function showCreateChildThemeForm() {
			$pas_cth_active_theme = get_option("pas_cth_active_theme", false);
			$activeTheme = ($pas_cth_active_theme !== false && wp_get_theme($pas_cth_active_theme)->exists() ? wp_get_theme($pas_cth_active_theme) : wp_get_theme());
			$currentTemplate = ($activeTheme->parent() != null ? $activeTheme->parent() : $activeTheme);
			$currentTemplateName = $currentTemplate->name;

			$select = "<label for='templateTheme'>"
			 . "Template Theme ( defaults to currently active template theme )"
							. "<br><select name='templateTheme' id='templateTheme'>";
			foreach ( $this->Themes->listTemplateThemes as $key => $theme ) {
				$selected = ($theme['themeName'] == $currentTemplateName ? " SELECTED " : "");

				$select .= "<option value='"
						.			esc_attr( $key )
						.			"' $selected>"
						.			esc_html( $theme['themeName'] )
						. "</option>";
			}
			$select .= "</select>";

			$adminThemesPage = admin_url("themes.php");
			$themeRoot		 = ($this->activeThemeInfo != null ? $this->activeThemeInfo->childThemeRoot : wp_get_theme()->get_theme_root());

			echo "<div class='createChildThemeBox'>";

			$createChildTheme = <<< "CREATECHILDTHEME"
	<div class='createChildThemeBoxForm'>
		Fill out the following form to create a new child theme. Only the <i>Child Theme Name</i> and the <i>Template Theme Name</i> drop down box are required.
		<br><br>
		<div class='createChildThemeBoxForm_HDR'>Create Child Theme</div>
			<form>
				<input type='hidden' name='themeRoot' value='{$themeRoot}'>
				<input type='hidden' name='action' value='createChildTheme'>
				<input type='hidden' name='href' value='{$adminThemesPage}'>
				<label for='childThemeName'>
					Child Theme Name:
					<br>
					<input type='text' name='childThemeName' id='childThemeName' value='' required pattern='^[a-zA-Z][a-zA-Z0-9\-\ ]+' data-message='Child Theme Names must begin with a letter' onblur='javascript:pas_cth_validateField(this);'>
				</label>
			<br>
			{$select}<br> <!-- displays list of non-child installed themes -->
			<label for='ThemeURI'>
			Theme URI<br>
			<input type='text' name='themeURI' id='themeURI' value='' pattern='^[a-zA-Z]{4,5}\://[a-zA-Z0-9:/\-\.\&\=\?]+$' data-message='Invalid URI' onblur='javascript:pas_cth_validateField(this);'>
			</label><br>
			<label for='Description'>
			Theme Description<br>
			<textarea required id='description' name='description' pattern='^[a-zA-Z0-9\.:;?#\%,\(\)/ ]+$' data-message='You may use letters, numbers, and special characters that you would normally use in writing, only. No HTML or other scripts are allowed here.' onblur='javascript:pas_cth_validateField(this);'></textarea>
			</label><br>
			<label for='authorName'>
			Author Name:<br>
			<input type='text' id='authorName' name='authorName' value='' pattern='^[a-zA-Z \.]+$' data-message='upper or lower case, spaces, or periods, only.' onblur='javascript:pas_cth_validateField(this);'>
			</label><br>
			<label for='authorURI'>
			Author URI:<br>
			<input type='text' id='authorURI' name='authorURI' value=''  pattern='^[a-zA-Z]{4,5}\://[a-zA-Z0-9:/\-\.\&\=\?]+$' data-message='Invalid URI' onblur='javascript:pas_cth_validateField(this);'>
			</label><br>
			<input type='hidden' id='version' name='version' value='0.0.1' readonly>
			<br>

			<div class='buttonRow'>
				<input type='button'
					value='Create Child Theme'
					class='blueButton'
					onclick='javascript:pas_cth_js_createChildTheme( this );'
				>
				&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
				<input type='button'
					value='Reset'
					class='blueButton'
					onclick='javascript:this.form.reset();'
				>
			</div>
			<br>
		</div>
		</form>
	</div>
CREATECHILDTHEME;
			echo $createChildTheme;
		}
		/*
		 *	manage_child_themes is the main driver function. This function is called from
		 * the Dashboard menu option 'Child Themes Helper'. This function either:
		 *	1 ) Displays the file list for the child theme and the file list for the template theme or
		 *	2 ) If the currently active theme is NOT a child theme, it displays the "form" to create a new
		 *	 child theme.
		 */
		function manage_child_themes($action = "COPY") {
			if ($this->activeThemeInfo == null) {
				return;
			}
			if (defined("DEMO_CAPABILITY")) {
				$capability = constant("DEMO_CAPABILITY");
			} else {
				$capability = "manage_options";
			}
			if ( ! current_user_can( $capability ) ) { exit; }

/*
	<div id='availableActions'>
		<ul style='list-style-type:disc;font-size:12pt;'>
			<li>
				Right click on a file in the child theme.
				<ul class='child'>
					<li>Remove the file from the child theme</li>
					<li>Edit the file</li>
				</ul>
			</li>
			<li>
				Right click on a file in the parent theme.
				<ul class='child'>
					<li>Copy the file from the parent theme to the child theme</li>
					<li>View the contents of the parent theme file.</li>
				</ul>
			</li>
		</ul>
	</div>
*/

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
			echo	"</div>"; // end pas_cth_content;
			// HoverPrompt is used during mouseovers on devices wider than 829px;
			// editFile is used when editting a file.
			// Both will be sized and positioned dynamically with Javascript
			echo	"<div id='hoverPrompt'></div>";

			$debugBTN	= (constant('WP_DEBUG') && defined('PLUGIN_DEVELOPMENT') && constant('PLUGIN_DEVELOPMENT') == "YES" ? "<input type='button' value='DEBUG' id='ef_debug_button' onclick='javascript:debug(this);'>" : "");
			$hexdumpBTN	= (constant('WP_DEBUG') && defined('PLUGIN_DEVELOPMENT') && constant('PLUGIN_DEVELOPMENT') == "YES" ? "<input type='button' value='HEXDUMP' id='ef_hexdump_button' onclick='javascript:pas_cth_js_hexdump();'>" : "");

			$editFileOutput = <<< "EDITFILE"

				<div id='shield'>
					<div id='editFile' data-gramm='false' >
						<input type='hidden' id='directory' value=''>
						<input type='hidden' id='file'	value=''>
						<input type='hidden' id='themeType' value=''>
						<input type='hidden' id='readOnlyFlag' value='false'>
						<input type='hidden' id='currentFileExtension' value=''>
						<input type='button' value='Save File' disabled id='ef_saveButton' onclick='javascript:pas_cth_js_saveFile();'>
						<p id='ef_readonly_msg'>Template Theme files are READ ONLY. Changes WILL NOT BE SAVED.</p>
						<p id='ef_filename'>FILENAME</p>
						<input type='button' value='Close File' id='ef_closeButton' onclick='javascript:pas_cth_js_closeEditFile();'>
						{$debugBTN}
						{$hexdumpBTN}
						<div id='editBox' data-gramm='false' spellcheck='false' autocapitalize='false' autocorrect='false' role='textbox' oninput='javascript:editBoxChange();'>
						</div>
					</div>
				</div>
				<div id='savePrompt'>
					File has changed.<br>Do you want to save it?<br><br>
					<input id='sp_saveButton' type='button' onclick='javascript:pas_cth_js_saveFile();' value='Save'>
					&nbsp;&nbsp;&nbsp;
					<input id='sp_closeButton' type='button' onclick='javascript:pas_cth_js_closeEditFile();' value='No Save'>
				</div>
EDITFILE;

			echo $editFileOutput;
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
											 'allowedFileTypes'=>get_option('pas_cth_edit_allowedFileTypes'),
											]
										 );
					echo "<li>"
						 . "<p class='file' "
						 . " data-jsdata='" . esc_attr( $jsdata ) . "' "
						 . " oncontextmenu='javascript:pas_cth_js_openMenu( this, event );' >";
/*
 *						 . " onclick='javascript:pas_cth_js_selectFile( this );' "
 *						 . " oncontextmenu='javascript:pas_cth_js_editFile( this );return false;' "
 *						 . " onmouseover='javascript:pas_cth_js_mouseOver( this );' "
 *						 . " onmouseout='javascript:pas_cth_js_mouseOut( this );' "
 *						 . ">" . $this->crlf;
*/
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
