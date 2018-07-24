<?php
if (! class_exists('pasChildThemes_ScreenShot') ) {
	class pasChildTheme_ScreenShot {
// $args is an associative array with the following indexes:
//		'childThemeName' => name of the child theme
//    'templateThemeName' => name of the parent theme.
//    'targetFile' => path to the screenshot.png file.
		function __construct($args) {
			$childThemeName = $args['childThemeName'];
			$templateThemeName = $args['templateThemeName'];
			$screenShotFile = $args['targetFile'];

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
			$angle = 0;

			$texts = Array(
					['string' => $childThemeName, 'fontSize' => 40, 'fontName' => 'Arial', 'pad'=>50],
					['string' => "...is a child of $templateThemeName", 'fontSize' => 28, 'fontName' => 'Arial', 'pad'=>150],
					['string' => 'created by the Child Theme Helper plugin', 'fontSize' => 28, 'fontName' => 'Arial', 'pad'=>100],
					['string' => 'http://www.PaulSwarthout.com/WordPress', 'fontSize' => 24, 'fontName' => 'Arial', 'pad'=>0]
					);
			$totalHeight = 0;
			for ($ndx = 0; $ndx < count($texts); $ndx++) {
				$size = $this->getSize(['string' => $texts[$ndx]['string'], 'fontSize'=> $texts[$ndx]['fontSize'], 'fontName' => $texts[$ndx]['fontName'] ] );
				$texts[$ndx]['width'] = $size['width'];
				$texts[$ndx]['height'] = $size['height'];
				$totalHeight += $size['height'] + $texts[$ndx]['pad'];
			}
			$totalHeight = ($imageSize['height'] - $totalHeight) / 2;

			for ($ndx = 0; $ndx < count($texts); $ndx++) {
				imagefttext( $img, $texts[$ndx]['fontSize'], $angle, ($imageSize['width'] - $texts[$ndx]['width'])/2, $texts[$ndx]['height'] + $totalHeight, $text_color, $texts[$ndx]['fontName'], $texts[$ndx]['string']);
				$totalHeight += $texts[$ndx]['height'] + $texts[$ndx]['pad'];
			}
			imagepng( $img, $screenShotFile );

			imagecolordeallocate( $img, $text_color );
			imagecolordeallocate( $img, $background );
			imagedestroy( $img );

			return true;
		}

		function getSize($item) {
			$boundingBox = imagettfbbox($item['fontSize'], 0, $item['fontName'], $item['string']);
			$width = abs($boundingBox[4] - $boundingBox[0]);
			$height = abs($boundingBox[0] - $boundingBox[6]);

			return ['width'=>$width, 'height'=>$height];
		}
	}
}