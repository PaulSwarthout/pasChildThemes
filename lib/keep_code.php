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
