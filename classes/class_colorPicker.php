<?PHP
if ( ! class_exists('pas_cth_colorPicker') ) {
	class pas_cth_colorPicker {
		private $pluginDirectory;
		private $defaultColor;

		function __construct($args) {
			$this->defaultColor		= (array_key_exists('color', $args) ? $args['color'] : "#800000");
			$this->pluginDirectory	= (array_key_exists('pluginDirectory', $args) ? $args['pluginDirectory'] : "");
		}

		function color_picker_styles() {
			// $uniqStr tricks your browser into not loading a cached version of the CSS file
			$uniqStr = (constant( 'WP_DEBUG' ) ? "?u=" . rand(0, 999999) . "&" : "");
			wp_enqueue_style(	'pas_cth_colorPicker',
								$this->pluginDirectory['url'] . "css/color_picker_grid.css" . $uniqStr,
								false );
		}

		function color_picker_scripts() {
			// $uniqStr tricks your browser into not loading a cached version of the JS file
			$uniqStr = (constant( 'WP_DEBUG' ) ? "?u=" . rand(0, 999999) . "&" : "");
			wp_enqueue_script( 'pas_cth_color_picker_script',
								$this->pluginDirectory['url'] . "js/color_picker.js" . $uniqStr,
								false );
		}

		function getNewColor($args) {
			$initialColor		= $args['initialColor'];
			$callingFieldName	= $args['callingFieldName'];

			$rgb = pas_cth_getColors($initialColor);
			$output = <<< "GETNEWCOLOR"
	<form>
		<input type='hidden' name='originalColor'	 value='$initialColor'>
		<input type='hidden' name='callingFieldName' value='$callingFieldName'>
		<div id='cpTop'>
			<div id='cpOuter'>
				<div id='color_picker_container'> <!-- CSS Grid starts here. -->
					<div class='row1'>
						<div class='intval_item'>
							INT
							<br>
							<input type='text' id='redSliderValue' name='redSliderValue' value='{$rgb['red']}'>
							<br>
							VALUE
						</div>
						<div class='identifier_item'>
							R<br>E<br>D
						</div>
						<div class='slider_item' id='redDIV' style='background:{$rgb['redColor']} ! important;'>
							<div class='slideContainer'>
								<input id='redSlider' class='slider-red' type='range' min='0' max='255' value='{$rgb['red']}' oninput='javascript:setColor();'>
							</div>
						</div>
					</div> <!-- end row1 -->
					<div class='row2'>
						<div class='intval_item'>
							INT
							<br>
							<input type='text' id='greenSliderValue' name='greenSliderValue' value='{$rgb['green']}'>
							<br>
							VALUE
						</div>
						<div class='identifier_item'>
							G<br>R<br>E<br>E<br>N
						</div>
						<div class='slider_item' id='greenDIV' style='background:{$rgb['greenColor']} ! important;'>
							<div class='slideContainer'>
								<input id='greenSlider' class='slider-green' type='range' min='0' max='255' value='{$rgb['green']}' oninput='javascript:setColor();'>
							</div>
						</div>
					</div> <!-- end row2 -->
					<div class='row3'>
						<div class='intval_item'>
						INT<br><input type='text' id='blueSliderValue' name='blueSliderValue' value='{$rgb['blue']}'><br>VALUE
						</div>
						<div class='identifier_item'>
							B<br>L<br>U<br>E
						</div>
						<div class='slider_item' id='blueDIV' style='background:{$rgb['blueColor']} ! important;'>
							<div class='slideContainer'>
								<input id='blueSlider' class='slider-blue' type='range' min='0' max='255' value='{$rgb['blue']}' oninput='javascript:setColor();'>
							</div>
						</div>
					</div> <!-- end row3 -->
				</div>
				<div id='exampleDIV' style='background:{$rgb['color']} ! important;'>&nbsp;</div>
				<input type='text' value='$initialColor' name='exampleColor' id='exampleColor' onchange='javascript:setColor(this.value);'><br>
				<input type='button' value='Save Color' style='position:absolute;bottom:10px;right:10px;' class='blueButton' onclick='javascript:saveColor(this);'>
			</div> <!-- end of id='cpOuter' -->
		</div> <!-- end of id='cpTop' -->
	</form> <!-- end of form -->
GETNEWCOLOR;
			return $output;
		}
	}
}