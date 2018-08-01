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

			// Set the enviroment variable for GD

			if (array_key_exists('width', $args) ) {
				$imageSize['width'] = $args['width'];
			} else {
				$imageSize['width'] = PASCHILDTHEMES_DEFAULT_IMAGE_WIDTH;
			}

			if (array_key_exists('height', $args) ) {
				$imageSize['height'] = $args['height'];
			} else {
				$imageSize['height'] = PASCHILDTHEMES_DEFAULT_IMAGE_HEIGHT;
			}

			$img = imagecreate( $imageSize['width'], $imageSize['height'] );
			$background = imagecolorallocate( $img, 11, 102, 35 );
			$text_color = imagecolorallocate( $img, 255, 255, 0 );

			$font = $fontPath . "BLKCHCRY.TTF";

			// Define the strings to write out.
			// Padding is padding before the string.
			// yPos = startOffset + for each index(initial padding + string height)
			$texts = Array(
					['string' => $childThemeName,
				   'fontSize' => 60,
				   'fontName' => $font,
					 'pad'=>0],

					['string' => "...is a child of $templateThemeName",
					 'fontSize' => 48,
					 'fontName' => $font,
					 'pad'=>50],

					['string' => 'created by the Child Theme Helper plugin',
					 'fontSize' => 48,
					 'fontName' => $font,
					 'pad'=>150],

					['string' => 'http://www.PaulSwarthout.com/WordPress',
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

		function getSize($item) {
			$boundingBox = imagettfbbox($item['fontSize'], 0, $item['fontName'], $item['string']);
			$width = abs($boundingBox[2] - $boundingBox[0]);
			$height = abs($boundingBox[1] - $boundingBox[7]);
			return ['width'=>$width, 'height'=>$height];
		}
	}
}