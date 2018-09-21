<?php
if ( ! class_exists( 'pas_cth_ScreenShot' )  ) {
	class pas_cth_ScreenShot {
		/*
		* $args is an associative array of named parameters.
		*   'childThemeName' => name of the child theme,
		*   'templateThemeName' => name of the template theme
		*   'targetFile' => path to the screenshot.png file
		*   'pluginDirectory' => fully qualified path to the plugin directory.
		*
		* This class will get some enhancements for the next release.
		*/
		private $libraryFunctions;

		function __construct( $args ) {
			$childThemeName			= $args['childThemeName'];
			$templateThemeName		= $args['templateThemeName'];
			$screenShotFile			= $args['targetFile'];
			$pluginDirectory		= $args['pluginDirectory'];
			$activeThemeInfo		= $args['activeThemeInfo'];
			$this->libraryFunctions	= $args['libraryFunctions'];

			$fontPath = $pluginDirectory['path'] . 'assets/fonts/';
			$fontPath = $activeThemeInfo->fixDelimiters( $fontPath );
			putenv( "GDFONTPATH=$fontPath" );

			// Set the enviroment variable for GD
			$imageSize['width'] = get_option( "pas_cth_imageWidth", PAS_CTH_DEFAULT_IMAGE_WIDTH );
			$imageSize['height']= get_option( "pas_cth_imageHeight",PAS_CTH_DEFAULT_IMAGE_HEIGHT );

			$img = imagecreate(  $imageSize['width'], $imageSize['height']  );

			$bcColor	= get_option( "pas_cth_bcColor", PAS_CTH_DEFAULT_SCREENSHOT_BCCOLOR );
			$rgb		= $this->libraryFunctions->getColors( $bcColor );
			$background = imagecolorallocate(  $img, $rgb['red'], $rgb['green'], $rgb['blue']  );

			$fcColor	= get_option( "pas_cth_fcColor", PAS_CTH_DEFAULT_SCREENSHOT_FCCOLOR );
			$rgb		= $this->libraryFunctions->getColors( $fcColor );
			$text_color = imagecolorallocate(  $img, $rgb['red'], $rgb['green'], $rgb['blue']  );

			$fontData = get_option( 'pas_cth_font', PAS_CTH_DEFAULT_FONT );
			$font = $pluginDirectory['path'] . "assets/fonts/" . $fontData['fontFile-base'] . ".ttf";

			// Define the strings to write out.
			// Padding is padding before the string.
			// yPos = startOffset + for each index( initial padding + string height )

			$totalLines = 4;
			$texts =
				[
					0	=>
						$this->buildBlock(
							[
								'item'				=>	0,
								'font'				=>	$font,
								'imageSize'			=>	$imageSize,
								'sampleText'		=>	$childThemeName,
								'fontSizeReduction' =>	(integer) 0,
								'totalLines'		=>	$totalLines,
								'pad'				=>	0
							]
						),

					1	=>
						$this->buildBlock(
							[
								'item'				=>	1,
								'font'				=>	$font,
								'imageSize'			=>	$imageSize,
								'sampleText'		=>	"is a child of $templateThemeName",
								'fontSizeReduction' =>	(integer) (-5),
								'totalLines'		=>	$totalLines,
								'pad'				=>	0
							]
						),

					2	=>
						$this->buildBlock(
							[
								'item'				=>	2,
								'font'				=>	$font,
								'imageSize'			=>	$imageSize,
								'sampleText'		=>	PAS_CTH_PLUGINNAME,
								'fontSizeReduction'	=>	(integer) (-5),
								'totalLines'		=>	$totalLines,
								'pad'				=>	0
							]
						),

					3	=>
						$this->buildBlock(
							[
								'item'				=>	3,
								'font'				=>	$font,
								'imageSize'			=>	$imageSize,
								'sampleText'		=>	PAS_CTH_MYURL,
								'fontSizeReduction' =>	(integer) (-10),
								'totalLines'		=>	$totalLines,
								'pad'				=>	0
							]
						)
				];

			// Calculate the total height so we can center the text block in the image.
			$totalHeight = 0;
			for ( $ndx = 0; $ndx < count( $texts ); $ndx++ ) {
				$size = $this->libraryFunctions->getSize(
						[
							'string' => $texts[$ndx]['string'],
							'fontSize'=> $texts[$ndx]['fontSize'],
							'fontName' => $texts[$ndx]['fontName']
						] );
				$texts[$ndx]['width']	= $size['width'];
				$texts[$ndx]['height']	= $size['height'];
				$totalHeight			+= $texts[$ndx]['height'];
			}

			$blankSpace = $imageSize['height'] - $totalHeight; // total unused space

			// Leave space, above and below and following each line. 4 lines = 6 spaces.
			$padding = floor($blankSpace / ($texts[0]['totalLines'] + 2));
			$totalHeight += $texts[0]['totalLines'] * $padding;
			for ($ndx = 0; $ndx < count($texts); $ndx++) {
				$texts[$ndx]['pad'] = $padding;
			}

			$startYPos = ( $imageSize['height'] - $totalHeight ) / 2;
			$offset = $startYPos;

			for ( $ndx = 0; $ndx < count( $texts ); $ndx++ ) {

				$xPos		= floor( ( $imageSize['width'] - $texts[$ndx]['width'] )/2 );
				$yPos		= floor( $offset + $texts[$ndx]['height']);
				$fontSize	= $texts[$ndx]['fontSize'];
				$angle		= 0;
				$fontName	= $texts[$ndx]['fontName'];
				$textLine	= $texts[$ndx]['string'];

				$bbox = imagefttext(  $img,
									  $fontSize,
									  $angle,
									  $xPos,
									  $yPos,
									  $text_color,
									  $fontName,
									  $textLine );

				// must be set after $yPos is set. Bottom of loop is best.
				$offset += $texts[$ndx]['height'] + $texts[$ndx]['pad'];
			}

			imagepng(  $img, $screenShotFile  );

			imagecolordeallocate(  $img, $text_color  );
			imagecolordeallocate(  $img, $background  );
			imagedestroy(  $img  );

			return true;
		}
		function buildBlock($args) {
			$sizeArgs =
				[
					'font'			=>	$args['font'],
					'imageSize'		=>	$args['imageSize'],
					'sampleText'	=>	$args['sampleText'],
					'totalLines'	=>	$args['totalLines']
				];
			$size = $this->libraryFunctions->getMaxFontSize($sizeArgs);

			return
				[
					'fontName'	=> $args['font'],
					'fontSize'	=> $size['maxFontSize'] + $args['fontSizeReduction'],
					'pad'		=> 0,
					'string'	=> $args['sampleText'],
					'width'		=> $size['sampleWidth'],
					'height'	=> $size['sampleHeight'],
					'totalLines'=> $args['totalLines']
				];

		}
	}
}