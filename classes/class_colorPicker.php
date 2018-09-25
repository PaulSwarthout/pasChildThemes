<?PHP
if ( ! class_exists( 'pas_cth_colorPicker' ) ) {
	class pas_cth_colorPicker {
		private $pluginDirectory;
		private $defaultColor;
		private $libraryFunctions;

		function __construct( $args ) {
			$this->defaultColor		=
				( array_key_exists( 'color', $args ) ? $args['color'] : "#800000" );
			$this->pluginDirectory	=
				( array_key_exists( 'pluginDirectory', $args ) ? $args['pluginDirectory'] : "" );
			$this->libraryFunctions = $args['libraryFunctions'];
		}

		function color_picker_styles( ) {
			// $uniqStr tricks your browser into not loading a cached version of the CSS file
			$uniqStr = ( constant( 'WP_DEBUG' ) ? "?u=" . rand( 0, 999999 ) . "&" : "" );
			wp_enqueue_style( 	'pas_cth_colorPicker',
								$this->pluginDirectory['url'] . "css/color_picker_grid.css" . $uniqStr,
								false );
		}

		function color_picker_scripts( ) {
			// $uniqStr tricks your browser into not loading a cached version of the JS file
			$uniqStr = ( constant( 'WP_DEBUG' ) ? "?u=" . rand( 0, 999999 ) . "&" : "" );
			wp_enqueue_script( 'pas_cth_color_picker_script',
								$this->pluginDirectory['url'] . "js/color_picker.js" . $uniqStr,
								false );
		}

		function getNewColor( $args ) {
			$initialColor		= esc_html( $args['initialColor'] );
			$callingFieldName	= esc_html( $args['callingFieldName'] );

			$rgb = $this->libraryFunctions->getColors( $initialColor );
			$output = <<< "GETNEWCOLOR"
	<form>
		<input type='hidden' name='originalColor'	 value='$initialColor'>
		<input type='hidden' name='callingFieldName' value='$callingFieldName'>
		<div id='cpTop'>
			<div id='cpOuter'> <!-- CSS Grid starts here -->
				<div id='color_picker_container'> <!-- col1 -->
					<div class='row1'>
						<div class='intval_item'>
							INT
							<br>
							<input type='text' id='redInt' name='redInt' value='{$rgb['red']}' onchange='javascript:setRed( this.value );'>
							<br>
							VALUE
						</div>
						<div class='identifier_item'>
							R<br>E<br>D
						</div>
						<div class='slider_item' id='redDIV' style='background:{$rgb['redColor']} ! important;'>
							<div class='slideContainer'>
								<input id='redSlider' class='slider-red' type='range' min='0' max='255' value='{$rgb['red']}' oninput='javascript:updateColorPicker( );'>
							</div>
						</div>
					</div> <!-- end row1 -->
					<div class='row2'>
						<div class='intval_item'>
							INT
							<br>
							<input type='text' id='greenInt' name='greenInt' value='{$rgb['green']}' onfocus='javascript:this.select( );' onchange='javascript:setGreen( this.value );'>
							<br>
							VALUE
						</div>
						<div class='identifier_item'>
							G<br>R<br>N
						</div>
						<div class='slider_item' id='greenDIV' style='background:{$rgb['greenColor']} ! important;'>
							<div class='slideContainer'>
								<input id='greenSlider' class='slider-green' type='range' min='0' max='255' value='{$rgb['green']}' oninput='javascript:updateColorPicker( );'>
							</div>
						</div>
					</div> <!-- end row2 -->
					<div class='row3'>
						<div class='intval_item'>
						INT<br><input type='text' id='blueInt' name='blueInt' value='{$rgb['blue']}' onchange='javascript:setBlue( this.value );'><br>VALUE
						</div>
						<div class='identifier_item'>
							B<br>L<br>U
						</div>
						<div class='slider_item' id='blueDIV' style='background:{$rgb['blueColor']} ! important;'>
							<div class='slideContainer'>
								<input id='blueSlider' class='slider-blue' type='range' min='0' max='255' value='{$rgb['blue']}' oninput='javascript:updateColorPicker( );'>
							</div>
						</div>
					</div> <!-- end row3 -->
				</div> <!-- end id=color_picker_container -->
				<div class='col2'>
					<div id='exampleDIV' style='background:{$rgb['color']} ! important;'>
						&nbsp;
					</div>
				</div>
				<div class='col3'>
					<input type='text' value='$initialColor' name='colorText' id='colorText' onchange='javascript:setColor( this.value );'><br>
					<input type='button' value='Save' class='smallButton' onclick='javascript:saveColor( this );'><br>
					<input type='button' value='Cancel' class='smallButton' onclick='javascript:cancelColorChange( this );'>
				</div>
			</div> <!-- end of id='cpOuter' --> <!-- end of CSS Grid -->
		</div> <!-- end of id='cpTop' -->
	</form> <!-- end of form -->
GETNEWCOLOR;
 // Previous line ends the HereDocs string. Do not indent that line, or add anything else to the end of it.
			return $output;
		}
		// Get the complementary color. If the color passed in is dark, then the output will be light
		// and vice versa.
		function invertColor( $hex, $bw ) {
			// Strip the leading pound sign. If it's been converted to a special character, strip that too
			$hex = ( substr( $hex, 0, 1 ) === "#"	?
						substr( $hex, 1 )			:
						( substr( $hex, 0, 6 ) === "&pound;"	?
							substr( $hex, 7 )					:
								( substr( $hex, 0, 4 ) === "&#163;"	?
									substr( $hex, 4 )					:
										$hex ) ) );

			$r = hexdec( substr( $hex, 0, 2 ) );
			$g = hexdec( substr( $hex, 2, 2 ) );
			$b = hexdec( substr( $hex, 4, 2 ) );

			if ( $bw ) {
				// http://stackoverflow.com/a/3943023/112731
				return ( ( $r * 0.299 + $g * 0.587 + $b * 0.114 ) > 186
					? '#000000'
					: '#FFFFFF' );
			}
			// invert color components
			$r = $this->digits( dechex( 255 - $r ), 2 );
			$g = $this->digits( dechex( 255 - $g ), 2 );
			$b = $this->digits( dechex( 255 - $b ), 2 );
			// pad each with zeros and return
			return "#" . $r . $g . $b;
		}
		/*
		 * digits( ) pads $v with zeroes to the left.
		 * For example, if we pass in the hex digit ( "A", 2 ) then digits( ) will return "0A"
		 */
		function digits( $v, $n ) {
			while ( strlen( $v ) < $n ) {
				$v = "0" . $v;
			}
			return ( $v );
		}
	}
}