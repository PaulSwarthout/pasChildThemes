<?PHP
if (! class_exists('pasDebug') ) {
	class pasDebug {
		// $debugOutput is an array of arrays. Each inside array looks like this:
		//   'heading' => display heading
		//   'data'    => debug output.
		// There is one Array ('heading'=>xxx, 'data'=>xxx) for each added output.
		private $debugOutput;
		private $dataToDump;
		private $ajax;
		private $onDumpExit;
		private $onDumpClear;
		private $data;
		private $args;
// Write multiple times to accumulate debug writes,
// Dump to write all stored writes out.
// Inputs:
//		Array(
//			'ajax' => true if in an AJAX call, false if not
//      'onDumpExit' => After dumping the stored "writes", exit execution (default: do not exit)
//      'onDumpClear' => After dumping the stored "writes", clear the stored "writes". (default: clear it).
//    );

		function __construct($args) {
			$this->args = $args;
			$this->ajax = (array_key_exists('ajax', $args) ? $args['ajax'] : false);
			$this->onDumpExit = (array_key_exists('onDumpExit', $args) ? $args['onDumpExit'] : true);
			$this->onDumpClear = (array_key_exists('onDumpClear', $args) ? $args['onDumpClear'] : true);
			$this->data = (array_key_exists('dump', $args) ? $args['dump'] : null);

			if ($this->data != null) {
				$this->write($this->data);
				$this->dump();
				unset($this->data);
			}
		}

//  Inputs:
//		Array( 'heading'=> a heading to help identify this block of data in the output,
//           'data' => The data to dump.
		function write($block) {
			$this->debugOutput[count($this->debugOutput)] = $block;
		}

//  Generally this function doesn't take any inputs.
//  However, the same arguments may be passed as are used in the __construct() function.
//  This allows the __construct() function to dump data as well as instantiate the class.
		function dump($args = null) {
			// if WordPress WP_DEBUG constant is not set to true, write out nothing and exit.
			if (null != $args) {
				$this->ajax = (array_key_exists('ajax', $args) ? $args['ajax'] : false);
				$this->onDumpExit = (array_key_exists('onDumpExit', $args) ? $args['onDumpExit'] : true);
				$this->onDumpClear = (array_key_exists('onDumpClear', $args) ? $args['onDumpClear'] : true);
			}
			if (!WP_DEBUG) { return false; }
			if ($this->ajax) { echo "DEBUG:{"; }
			for ($ndx = 0; $ndx < count($this->debugOutput); $ndx++) {
				echo "<div class='debugHeading'>" . $this->debugOutput[$ndx]['heading'] . "</div>";
				echo "<div class='debugOutput'><pre>" . print_r($this->debugOutput[$ndx]['data'], true) . "</pre></div>";
			}
			if ($this->ajax) { echo "}"; }
			if ($this->onDumpExit) {
				echo "<HR>Execution Terminated<HR>";
				exit;
			}
			if ($this->onDumpClear) {
				unset($this->debugOutput);
				$this->debugOutput = Array();
			}
		}
	}
}
