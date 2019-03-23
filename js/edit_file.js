if (typeof String.prototype.toBold == "undefined") {
	String.prototype.toBold = function () {
		return "<span style='font-weight:bold;'>" + this + "</span>";
	}
}
if (typeof Element.prototype.setAttributes == "undefined") {
	Element.prototype.setAttributes = function (strListOfAttributes) {
		var ndx, nameValuePair;
		var key, attr;
		var attributes = strListOfAttributes.split(";");

		for (ndx = 0; ndx < attributes.length; ndx++) {
			nameValuePair = attributes[ndx].split("=");
			this.setAttribute(nameValuePair[0], nameValuePair[1]);
		}
	}
}
/*
 * pas_cth_debugMode is created by a wp_localized_script call in dashboard_scripts()
 * in classes/class_childThemesHelper.php.
 * If pas_cth_debugMode exists, then WP_DEBUG is defined as true in wp_config.php.
 */
function inDebugMode() {
	var rtn = false;
	if (typeof pas_cth_debugMode !== "undefined") {
		rtn = true;
	}
	return rtn;
}
/*
 * Get a pointer to all of the DOM elements used by the edit file functionality.
 */
function pas_cth_js_editElements() {
	this.editFile		= document.getElementById("editFile")
	this.editBox		= document.getElementById("editBox")
	this.wpbodyContent	= document.getElementById("wpbody-content")
	this.parentPosition	= getTopLeftPosition(this.wpbodyContent);
	this.filenameDisplay= document.getElementById("ef_filename");
	this.themeGrid		= document.getElementById("themeGrid");
	this.efSaveButton	= document.getElementById("ef_saveButton");
	this.efCloseButton	= document.getElementById("ef_closeButton");
	this.spSaveButton	= document.getElementById("sp_saveButton");
	this.spCloseButton	= document.getElementById("sp_closeButton");
	this.savePrompt		= document.getElementById("savePrompt");

	this.directoryINP	= document.getElementById("directory");
	this.filenameINP	= document.getElementById("file")
	this.themeTypeINP	= document.getElementById("themeType");
	this.readOnlyFlag	= document.getElementById("readOnlyFlag");
	this.readOnlyMsg	= document.getElementById("ef_readonly_msg");

	this.shield			= document.getElementById("shield");

	this.windowHeight	= window.innerHeight;
	this.windowWidth	= window.innerWidth;

	this.adminmenu		= document.getElementById("adminmenu");
	this.adminbar		= document.getElementById("wpadminbar");
	this.wpcontent		= document.getElementById("wpcontent");
	this.wpbody_content = document.getElementById("wpbody-content");

	this.currentFileExtension = document.getElementById("currentFileExtension");
	if (this.currentFileExtension == null) {
		this.currentFileExtension = document.createElement("input");
		this.currentFileExtension.setAttributes("type=hidden;id=currentFileExtension");
		document.getElementsByTagName("body")[0].appendChild(this.currentFileExtension);
	}
}
function pas_cth_js_findElement(element) {
	return (element == document.getElementById("currentFileExtension").value);
}
function pas_cth_js_editFile(event) {
	var element = document.getElementById(event.srcElement.dataset.elementid);
	var xmlhttp = new XMLHttpRequest();
	var data = new FormData();
	var jsInput = JSON.parse(element.getAttribute("data-jsdata"));
	var ee = new pas_cth_js_editElements();

/*
 * Verify that the selected file is a type that we can edit. If it's not, then return.
 */
	ee.currentFileExtension.value = jsInput['extension'];
	if (jsInput['allowedFileTypes'].findIndex(pas_cth_js_findElement) < 0) {
		var msg = "<div style='text-align:center;width:100%;'><h2>FILE TYPE ERROR</h2><hr>You can only edit/view files of the following types:<br>" +
			jsInput['allowedFileTypes'].toString().split(",").join("<br>").toBold() +
			"</div>";
		pas_cth_js_createBox("invalidFileTypeMessage", "", document.getElementsByTagName("body")[0], true).innerHTML += msg;
		return;
	}
/*
 * Make an AJAX call to retrieve the file contents from the web server.
 * The themeType indicates whether the file is read-only or writeable.
 */

	xmlhttp.open("POST", ajaxurl, true);

	data.append("action",	 'editFile');
	data.append("directory", jsInput['directory'] );
	data.append("file",		 jsInput['file']);
	data.append("themeType", jsInput['themeType'] );

	xmlhttp.onreadystatechange = function () {
		if (4 == xmlhttp.readyState) {
			var response = (xmlhttp.responseText.length >= 1 ?
								xmlhttp.responseText.left(xmlhttp.responseText.length - 1) :
								xmlhttp.responseText);

			switch (xmlhttp.status) {
				case 200: // Everything is okay
					if (response.length <= 0) {
						location.reload();
					} else {
						/*
						 * Function pas_cth_js_processResponse is in the js_common_fn.js file
						 * which then passes control back to the processEditFile() in this file.
						 */
						pas_cth_js_processResponse(response);
					}
					break;

				case 400:
					msg = "400 Error:<br>" + xmlhttp.statusText;
					pas_cth_js_showBox().innerHTML = msg;
					break;
			}
		}
	}
	xmlhttp.send(data)
}
function captureKeystrokes(element) {
	switch (event.keyCode) {
		case 9:
			event.preventDefault();
			insertTextAtCursor(String.fromCharCode(event.keyCode));
			editBoxChange();
			break;
		case 10:
		case 13:
			event.preventDefault();
			insertTextAtCursor(String.fromCharCode(10));
			editBoxChange();
			break;
		case 90: // CAP Z
			if (event.ctrlKey) { // CTRL-Z (undo) // doesn't currently work in all cases. Need to rewrite the edit handling to use execCommand.
			}
			break;
		case 16: // left shift
		case 8:  // backspace
		case 17: // left CTRL
			break;
		default:
			break;
	}
}
function clearSelection() {
	var sel = window.getSelection ? window.getSelection() : document.selection;
	if (sel) {
		if (sel.removeAllRanges) {
			sel.removeAllRanges();
		} else if (sel.empty) {
			sel.empty();
		}
	}
}
function insertTextAtCursor(text) {
    var sel, range, html;
	var cursorPosition = saveSelection();

	if (window.getSelection) {
        sel = window.getSelection();

        if (sel.getRangeAt && sel.rangeCount) {
            range = sel.getRangeAt(0);
            range.deleteContents();
			restoreSelection(cursorPosition);
			range.insertNode( document.createTextNode(text) );
			range.collapse();
        }
	} else if (document.selection && document.selection.createRange) {
        document.selection.createRange().text = text;
		document.selection.collapse();
    }


}
function saveSelection() {
    if (window.getSelection) {
        sel = window.getSelection();
        if (sel.getRangeAt && sel.rangeCount) {
            return sel.getRangeAt(0);
        }
    } else if (document.selection && document.selection.createRange) {
        return document.selection.createRange();
    }
    return null;
}

function restoreSelection(range) {
    if (range) {
        if (window.getSelection) {
            sel = window.getSelection();
            sel.removeAllRanges();
            sel.addRange(range);
        } else if (document.selection && range.select) {
            range.select();
        }
    }
}
function processEditFile(response) {
	var ee = new pas_cth_js_editElements();
	var responseSections = parseOutput(response);
	responseSections.ARGS = JSON.parse(responseSections.ARGS);

	ee.directoryINP.value = responseSections.ARGS['directory'];
	ee.filenameINP.value = responseSections.ARGS['file'];
	ee.themeTypeINP.value = responseSections.ARGS['themeType'];
	ee.readOnlyFlag.value = responseSections.ARGS['readOnlyFlag'];
	ee.filenameDisplay.innerHTML = "FILE: " + responseSections.ARGS['directory'] + "/" + responseSections.ARGS['file'];

	ee.efSaveButton.disabled = true;

	ee.editBox.innerHTML = responseSections.EDITBOX

	enableContent(ee);

	if (ee.readOnlyFlag.value.toLowerCase() == "true") {
		ee.readOnlyMsg.style.display = "inline";
	} else {
		ee.readOnlyMsg.style.display = "none";
	}

	ee.editBox.onkeydown = function () { captureKeystrokes(this) }

	ee.shield.style.display = "inline";
	ee.editFile.style.display = "grid";

}
function enableContent(elements) {
	elements.editBox.contentEditable		= true;

	elements.efSaveButton.contentEditable	= false;
	elements.efCloseButton.contentEditable	= false;

	elements.savePrompt.contentEditable		= false;
	elements.spSaveButton.contentEditable	= false;
	elements.spCloseButton.contentEditable	= false;
}

function pas_cth_js_closeEditFile() {
	var ee = new pas_cth_js_editElements();

	if ( ! ee.efSaveButton.disabled) {
		ee.savePrompt.style.cssText = "display:inline;"
		ee.efSaveButton.disabled = true;
	} else {
//		ee.themeGrid.style.display = "inline-grid";
		ee.editFile.style.display = "none";
		ee.shield.style.display = "none";
		ee.editBox.innerHTML = "";
		ee.savePrompt.style.display = "none";
	}
}
function pas_cth_js_closeFile() {
	var ee = new pas_cth_js_editElements();

	ee.efSaveButton.disabled = true;
	ee.savePrompt.style.display = "none";
	pas_cth_js_closeEditFile();

}
function editBoxChange() {
	if (document.getElementById("themeType").value.toLowerCase() == "child") {
		document.getElementById("ef_saveButton").disabled = false;
	}
}
function debug(element) {
	var ee = new pas_cth_js_editElements();
}
function pas_cth_js_saveFile() {
	var ee = new pas_cth_js_editElements();

	var xmlhttp = new XMLHttpRequest();
	var data = new FormData();
	var fileContents = "";

	xmlhttp.open("POST", ajaxurl, true);

	fileContents = ee.editBox.innerText; // .replace(/&lt;/g, "<").replace(/&gt;/g, ">");

	data.append("fileContents", fileContents);
	data.append("file",			ee.filenameINP.value);
	data.append("directory",	ee.directoryINP.value);
	data.append("themeType",	ee.themeTypeINP.value);
	data.append("action",		"saveFile");

	ee.efSaveButton.disabled = true; // last possible chance to disable the button.

	pas_cth_js_closeEditFile(); // Closing only clears the div and hides it. Not destroyed.

	xmlhttp.onreadystatechange = function () {
		if (4 == xmlhttp.readyState) {
			var response = (xmlhttp.responseText.length >= 1 ?
								xmlhttp.responseText.left(xmlhttp.responseText.length - 1) :
								xmlhttp.responseText);

			switch (xmlhttp.status) {
				case 200: // Everything is okay
					if (response.length <= 0) {

					} else {
						pas_cth_js_processResponse(response);
					}
					break;

				case 400:
				case 500:
					msg = xmlhttp.status + " Error:<br>" + xmlhttp.statusText;
					pas_cth_js_showBox().innerHTML = msg;
					break;
			}
		}
	}
	xmlhttp.send(data);
}
function parseOutput(response) {

	var blockArray = response.split('+|++|+');
	var ndx;
	var obj = new Object();

	for (ndx = 0; ndx < blockArray.length; ndx++) {
		items = blockArray[ndx].split('<:>');
		obj[items[0]] = items[1];
	}

	return obj;

}
function modify() {
	var ee = new pas_cth_js_editElements();

	ee.efSaveButton.disabled = false;
}