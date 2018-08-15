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
		function __construct( $args ) {
			$childThemeName		= $args['childThemeName'];
			$templateThemeName	= $args['templateThemeName'];
			$screenShotFile		= $args['targetFile'];
			$pluginDirectory	= $args['pluginDirectory'];
			$activeThemeInfo	= $args['activeThemeInfo'];

			$fontPath = $pluginDirectory['path'] . 'assets/fonts/';
			$fontPath = $activeThemeInfo->fixDelimiters( $fontPath );
			putenv( "GDFONTPATH=$fontPath" );

			// Set the enviroment variable for GD
			$imageSize['width'] = get_option( "pas_cth_imageWidth", PAS_CTH_DEFAULT_IMAGE_WIDTH );
			$imageSize['height']= get_option( "pas_cth_imageHeight",PAS_CTH_DEFAULT_IMAGE_HEIGHT );

			$img = imagecreate(  $imageSize['width'], $imageSize['height']  );

			$bcColor	= get_option( "pas_cth_bcColor", PAS_CTH_DEFAULT_SCREENSHOT_BCCOLOR );
			$rgb		= $this->getColors( $bcColor );
			$background = imagecolorallocate(  $img, $rgb['red'], $rgb['green'], $rgb['blue']  );

			$fcColor	= get_option( "pas_cth_fcColor", PAS_CTH_DEFAULT_SCREENSHOT_FCCOLOR );
			$rgb		= $this->getColors( $fcColor );
			$text_color = imagecolorallocate(  $img, $rgb['red'], $rgb['green'], $rgb['blue']  );

			$font = $fontPath . get_option( 'pas_cth_font', PAS_CTH_DEFAULT_SCREENSHOT_FONT );

			// Define the strings to write out.
			// Padding is padding before the string.
			// yPos = startOffset + for each index( initial padding + string height )
			$texts =
				[
					[
						'string' => $childThemeName,
						'fontSize' => 50,
						'fontName' => $font,
						'pad'=>0,
					],

					[
						'string' => "...is a child of $templateThemeName",
						'fontSize' => 48,
						'fontName' => $font,
						'pad'=>50
					],

					[
						'string' => PAS_CTH_PLUGINNAME,
						'fontSize' => 40,
						'fontName' => $font,
						'pad'=>150
					],

					[
						'string' => PAS_CTH_MYURL,
						'fontSize' => 36,
						'fontName' => $font,
						'pad'=>100
					]
				];
			// Calculate the total height so we can center the text block in the image.
			$totalHeight = 0;
			for ( $ndx = 0; $ndx < count( $texts ); $ndx++ ) {
				$size = $this->getSize(
						[
							'string' => $texts[$ndx]['string'],
							'fontSize'=> $texts[$ndx]['fontSize'],
							'fontName' => $texts[$ndx]['fontName']
						] );
				$texts[$ndx]['width']	= $size['width'];
				$texts[$ndx]['height']	= $size['height'];
				$totalHeight			+= $texts[$ndx]['pad'] + $size['height'];
			}

			$startYPos = ( $imageSize['height'] - $totalHeight ) / 2;
			$offset = $startYPos;

			for ( $ndx = 0; $ndx < count( $texts ); $ndx++ ) {

				$offset		+= $texts[$ndx]['pad'];
				$xPos		= floor( ( $imageSize['width'] - $texts[$ndx]['width'] )/2 );
				$yPos		= floor( $offset );
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
				$offset += $texts[$ndx]['height'];
			}

			imagepng(  $img, $screenShotFile  );

			imagecolordeallocate(  $img, $text_color  );
			imagecolordeallocate(  $img, $background  );
			imagedestroy(  $img  );

			return true;
		}
		/* Converts a hex string of colors such as: #AABBCC to an array of decimal values.
		 *  For example: an input of: #2AC4D2 would return:
		 *  ['red'=>42, 'green'=>196, 'blue'=> 210]
		 */
		function getColors( $hexCode ) {
			// Strip '#' from the front, if it exists
			// This way, the function works, whether or not the '#' is prepended to the input.
			$hexCode = (substr($hexCode, 0, 1) === "#" ? substr($hexCode, 1) : $hexCode);
			return	[
						'red'	=> hexdec( substr( $hexCode, 0, 2 ) ),
						'green'	=> hexdec( substr( $hexCode, 2, 2 ) ),
						'blue'	=> hexdec( substr( $hexCode, 4, 2 ) )
					];
		}

		// Gets the graphical size of a string based upon the chosen font, font size, and the string
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
	}
}