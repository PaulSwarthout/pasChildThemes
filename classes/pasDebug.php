<?PHP
//xx
if ( ! class_exists( 'pasDebug' )  ) {
	class pasDebug {
		// $debugOutput is an array of arrays. Each inside array looks like this:
		//   'heading' => display heading
		//   'data'    => debug output.
		// There is one Array ( 'heading'=>xxx, 'data'=>xxx ) for each added output.
		private $debugOutput;
		private $dataToDump;
		private $ajax;
		private $onDumpExit;
		private $onDumpClear;
		private $dbWrite;
		private $dbClear;
		private $dbShow;
		private $data;
		private $args;
		private $dbTableName;
// Write multiple times to accumulate debug writes,
// Dump to write all stored writes out.
// Inputs:
//		Array(
//			'ajax' => true if in an AJAX call, false if not
//      'onDumpExit' => After dumping the stored "writes", exit execution ( default: do not exit )
//      'onDumpClear' => After dumping the stored "writes", clear the stored "writes". ( default: clear it ).
//     );

		function __construct( $args = Array() ) {
			global $wpdb;
			$this->args = $args;
			$this->ajax = ( array_key_exists( 'ajax', $args ) ? $args['ajax'] : false );
			$this->onDumpExit = ( array_key_exists( 'onDumpExit', $args ) ? $args['onDumpExit'] : true );
			$this->onDumpClear = ( array_key_exists( 'onDumpClear', $args ) ? $args['onDumpClear'] : true );
			$this->data = ( array_key_exists( 'dump', $args ) ? $args['dump'] : null );
			$this->dbWrite = ( array_key_exists( 'dbWrite', $args ) ? $args['dbWrite'] : false );
			$this->dbTableName = $wpdb->prefix . "debug_dbwrites";
			$this->dbClear = ( array_key_exists( 'dbClear', $args ) ? $args['dbClear'] : false );
			$this->dbShow  = ( array_key_exists( 'dbShow', $args ) ? $args['dbShow'] : false );

			if ( $this->data != null ) {
				$this->write( $this->data );
				$this->dump();
				unset( $this->data );
			}

			if ( $this->dbClear ) {
				$this->killDBLog();
				$this->dbClear = false;
			}

			if ( $this->dbWrite ) {
				$this->verifyTableExists();
			}
		}

		function verifyTableExists() {
			global $wpdb;
			$isql = " create table if not exists " . $this->dbTableName
				    . " (  ID	int auto_increment primary key, "
						. "   dbg_meta	varchar( 100 ) not null, "
			      . "   dbg_value	longtext not null "
						. "  ) engine=innodb; ";
			$wpdb->get_results( $isql );
		}

		function killDBLog() {
			global $wpdb;
			$isql = " drop table if exists " . $this->dbTableName . "; ";
			$wpdb->get_results( $isql );
		}

//  Inputs:
//		Array(  'heading'=> a heading to help identify this block of data in the output,
//           'data' => The data to dump.
		function setOptions( $options ) {
			if ( 0 < count( $options ) ) {
				foreach ( $options as $key => $value ) {
					switch ( strtolower( $key ) ) {
						case "ajax":
							$this->ajax = $value;
							break;
						case "ondumpexit":
							$this->onDumpExit = $value;
							break;
						case "ondumpclear":
							$this->onDumpClear = $value;
							break;
						case "dbwrite":
							$this->dbWrite = $value;
							if ( $value ) {
								$this->verifyTableExists();
							}
							break;
						case "dbclear": // Don't store the value. Just clear the database log.
							if ( $value ) {
								$this->killDBLog();
							}
							break;
						case "dbShow": // Just show the db log again.
							if ( $value ) {
								$this->dump();
							}
							break;

					}
				}
				$this->args = [ 'ajax'					=> $this->ajax,
												'onDumpExit'		=> $this->onDumpExit,
												'onDumpClear'		=> $this->onDumpClear,
												'dbWrite'				=> $this->dbWrite
											];
			}
		}
		function write( $block ) {
			global $wpdb;
			if ( $this->dbWrite ) {
				$this->verifyTableExists();
				$isql = " insert into " . $this->dbTableName
							. " ( dbg_meta, dbg_value ) "
							. " values ( %s, %s ); ";
				$isql = $wpdb->prepare( $isql, $block['heading'], serialize( $block ) );
				$wpdb->get_results( $isql );
			} else {
				$this->debugOutput[count( $this->debugOutput )] = $block;
			}
		}

//  Generally this function doesn't take any inputs.
//  However, the same arguments may be passed as are used in the __construct() function.
//  This allows the __construct() function to dump data as well as instantiate the class.
		function dump( $args = null ) {
			global $wpdb;
			// if WordPress WP_DEBUG constant is not set to true, write out nothing and exit.
			if ( !WP_DEBUG ) { return false; }

			if ( null != $args ) {
				$this->ajax = ( array_key_exists( 'ajax', $args ) ? $args['ajax'] : false );
				$this->onDumpExit = ( array_key_exists( 'onDumpExit', $args ) ? $args['onDumpExit'] : true );
				$this->onDumpClear = ( array_key_exists( 'onDumpClear', $args ) ? $args['onDumpClear'] : true );
				$this->dbWrite = ( array_key_exists( 'dbWrite', $args ) ? $args['dbWrite'] : false );
			}
			if ( $this->ajax ) {
				echo "DEBUG:{";
			}
			if (  ! $this->dbWrite  ) {
				for ( $ndx = 0; $ndx < count( $this->debugOutput ); $ndx++ ) {
					echo "<div class='debugHeading'>" . $this->debugOutput[$ndx]['heading'] . "</div>";
					echo "<div class='debugOutput'><pre>" . print_r( $this->debugOutput[$ndx]['data'], true ) . "</pre></div>";
				}
			} else {
				$isql = " select dbg_meta, dbg_value from " . $this->dbTableName . " order by ID asc; ";
				$results = $wpdb->get_results( $isql, ARRAY_A );
				foreach ( $results as $row ) {
					$heading = $row['dbg_meta'];
					$block = unserialize( $row['dbg_value'] );
					echo "<div class='debugHeading'>" . $heading . "</div>";
					echo "<div class='debugOutput'><pre>" . print_r( $block['data'], true ) . "</pre></div>";
				}
			}
			if ( $this->ajax ) {
				echo "}";
			}
			if ( $this->onDumpClear ) {
				if ( $this->dbWrite ) {
					$this->killDBLog();
				} else {
					unset( $this->debugOutput );
					$this->debugOutput = Array();
				}
			}
			if ( $this->onDumpExit ) {
				echo "<HR>Execution Terminated<HR>";
				exit;
			}
		}
	}
}
