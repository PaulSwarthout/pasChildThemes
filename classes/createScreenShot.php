<?php
if (! class_exists('pasChildThemes_ScreenShot') ) {
	class pasChildTheme_ScreenShot {
/*
 * $args is an associative array of named parameters.
 *   'childThemeName' => name of the child theme,
 *   'templateThemeName' => name of the template theme
 *   'targetFile' => path to the screenshot.png file
 *   'pluginDirectory' => fully qualified path to the plugin directory.
 */
		public $fontPath;
		function __construct($args) {
			global $currentThemeObject;

			$childThemeName = $args['childThemeName'];
			$templateThemeName = $args['templateThemeName'];
			$screenShotFile = $args['targetFile'];
			$pluginDirectory = $args['pluginDirectory'];
			$fontPath = $currentThemeObject->fixDelimiters($pluginDirectory . "assets/fonts/");
			putenv("GDFONTPATH=$fontPath");

			// Set the enviroment variable for GD
			$imageSize['width'] = get_option("pasChildThemes_imageWidth", PASCHILDTHEMES_DEFAULT_IMAGE_WIDTH);
			$imageSize['height']= get_option("pasChildThemes_imageHeight",PASCHILDTHEMES_DEFAULT_IMAGE_HEIGHT);

			$img = imagecreate( $imageSize['width'], $imageSize['height'] );
			$bcColor = get_option("pasChildThemes_bcColor", PASCHILDTHEMES_DEFAULT_SCREENSHOT_BCCOLOR);
			$fcColor = get_option("pasChildThemes_fcColor", PASCHILDTHEMES_DEFAULT_SCREENSHOT_FCCOLOR);

			$rgb = $this->getColors($bcColor);
			$background = imagecolorallocate( $img, $rgb['red'], $rgb['green'], $rgb['blue'] );
			$rgb = $this->getColors($fcColor);
			$text_color = imagecolorallocate( $img, $rgb['red'], $rgb['green'], $rgb['blue'] );

			$font = $fontPath . get_option('pasChildThemes_font', PASCHILDTHEMES_DEFAULT_SCREENSHOT_FONT);

			// Define the strings to write out.
			// Padding is padding before the string.
			// yPos = startOffset + for each index(initial padding + string height)
			$texts = Array(
					['string' => get_option("pasChildThemes_string1", $childThemeName),
				   'fontSize' => 50,
				   'fontName' => $font,
					 'pad'=>0],

					['string' => get_option("pasChildThemes_string2", "...is a child of $templateThemeName"),
					 'fontSize' => 48,
					 'fontName' => $font,
					 'pad'=>50],

					['string' => get_option("pasChildThemes_string3", PASCHILDTHEMES_NAME),
					 'fontSize' => 48,
					 'fontName' => $font,
					 'pad'=>150],

					['string' => get_option("pasChildThemes_string4", PAULSWARTHOUT_URL),
					 'fontSize' => 44,
					 'fontName' => $font,
					 'pad'=>100]

					);
			// Calculate the total height so we can center the text.
			$totalHeight = 0;
			for ($ndx = 0; $ndx < count($texts); $ndx++) {
				$size = $this->getSize(['string' => $texts[$ndx]['string'], 'fontSize'=> $texts[$ndx]['fontSize'], 'fontName' => $texts[$ndx]['fontName'] ] );
				$texts[$ndx]['width'] = $size['width'];
				$texts[$ndx]['height'] = $size['height'];
				$totalHeight += $texts[$ndx]['pad'] + $size['height'];
			}


			// Reusing the variable $totalHeight. Will now hold the starting point on the canvas for the Y axis.
			$startYPos = ($imageSize['height'] - $totalHeight) / 2;
			$offset = $startYPos;
			for ($ndx = 0; $ndx < count($texts); $ndx++) {
				$offset  += $texts[$ndx]['pad'];
				$xPos		  = floor(($imageSize['width'] - $texts[$ndx]['width'])/2);
				$yPos		  = floor($offset);
				$fontSize = $texts[$ndx]['fontSize'];
				$angle    = 0;
				$fontName = $texts[$ndx]['fontName'];
				$textLine = $texts[$ndx]['string'];
				imagefttext( $img, $fontSize, $angle, $xPos, $yPos, $text_color, $fontName, $textLine);
				$offset += $texts[$ndx]['height']; // must be set after $yPos is set. Bottom of loop is best.
			}

			imagepng( $img, $screenShotFile );

			imagecolordeallocate( $img, $text_color );
			imagecolordeallocate( $img, $background );
			imagedestroy( $img );

			return true;
		}

		function getColors($hexCode) {
			return ['red'		=>hexdec(substr($hexCode, 1, 2)),
				      'green'	=>hexdec(substr($hexCode, 3, 2)),
							'blue'	=>hexdec(substr($hexCode, 5, 2))
						 ];
		}

		function getSize($item) {
			$boundingBox = imagettfbbox($item['fontSize'], 0, $item['fontName'], $item['string']);
			$width = abs($boundingBox[2] - $boundingBox[0]);
			$height = abs($boundingBox[1] - $boundingBox[7]);
			return ['width'=>$width, 'height'=>$height];
		}
	}
}