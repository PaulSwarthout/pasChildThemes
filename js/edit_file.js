function findElement(element) {
	return (element == document.getElementById("currentFileExtension").value);
}
if (typeof String.prototype.toBold == "undefined") {
	String.prototype.toBold = function () {
		return "<span style='font-weight:bold;'>" + this + "</span>";
	}
}
function pas_cth_js_editFile(element) {
	var xmlhttp = new XMLHttpRequest();
	var data = new FormData();
	var jsInput = JSON.parse(element.getAttribute("data-jsdata"));
	var box;

	var variable = document.getElementById("currentFileExtension");
	if (variable == null) {
		variable = document.createElement("input");
		variable.type = "hidden"
		variable.setAttribute("id", "findIndexValue");
		document.getElementsByTagName("body")[0].appendChild(variable);
	}
	variable.value = jsInput['extension'];

	if (jsInput['allowedFileTypes'].findIndex(findElement) < 0) {
		var msg = "<div style='text-align:center;width:100%;'><h2>FILE TYPE ERROR</h2><hr>You can only edit files of the following types:<br>" +
			jsInput['allowedFileTypes'].toString().split(",").join("<br>").toBold() +
			"<br>To add other file types, please visit the options page.</div>";
		pas_cth_js_createBox("actionBox", "", document.getElementsByTagName("body")[0], true).innerHTML += msg;
		return;
	}

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
debugger
	switch (event.keyCode) {
		case 9:
			event.preventDefault();
			insertTextAtCursor(String.fromCharCode(event.keyCode));
			break;
		case 10:
		case 13:
			event.preventDefault();
			insertTextAtCursor(String.fromCharCode(10));
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
function pas_cth_js_editElements() {
	this.editFile		= document.getElementById("editFile")
	this.editBox		= document.getElementById("editBox")
	this.wpbodyContent	= document.getElementById("wpbody-content")
	this.parentPosition	= getTopLeftPosition(this.wpbodyContent);
//	this.efButtonRow	= document.getElementById("ef_buttonRow")
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

	this.windowHeight	= window.innerHeight;
	this.windowWidth	= window.innerWidth;

	this.adminmenu		= document.getElementById("adminmenu");
	this.adminbar		= document.getElementById("wpadminbar");
	this.wpcontent		= document.getElementById("wpcontent");
}
if (typeof Element.prototype.alignWith == "undefined") {
	Element.prototype.alignWith = function (objectToAlignWith) {
		var pos = getPosition(objectToAlignWith);
		this.style.position = "absolute";
		this.style.left = pos.left;
		this.style.top = pos.top;
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

//	ee.wpbodyContent.style.height = (ee.windowHeight * 0.7) + "px";

	enableContent(ee);

	if (ee.readOnlyFlag.value.toLowerCase() == "true") {
		ee.readOnlyMsg.style.display = "inline";
	} else {
		ee.readOnlyMsg.style.display = "none";
	}

	document.getElementsByTagName("body")[0].appendChild(ee.editFile);

	ee.editFile.alignWith(ee.themeGrid);


	ee.editBox.onresize  = function () {
		var ee = new pas_cth_js_editElements();
		ee.editFile.alignWith(ee.themeGrid);
	}

	ee.editBox.onkeydown = function () { captureKeystrokes(this) }

	ee.editFile.style.display = "grid";
	ee.themeGrid.style.display = "none";

}
function enableContent(elements) {
	elements.editBox.contentEditable		= true;

//	elements.efButtonRow.contentEditable	= false;
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
		ee.themeGrid.style.display = "inline-grid";
		ee.editFile.style.display = "none";
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
function editBoxChange(element) {
	if (document.getElementById("themeType").value.toLowerCase() == "child") {
		document.getElementById("ef_saveButton").disabled = false;
	}
}
function debug(element) {
	var ee = new pas_cth_js_editElements();
	debugger;
}
function pas_cth_js_saveFile() {
	var ee = new pas_cth_js_editElements();

	var xmlhttp = new XMLHttpRequest();
	var data = new FormData();
	var fileContents = "";

	xmlhttp.open("POST", ajaxurl, true);

	fileContents = ee.editBox.innerHTML.replace(/&lt;/g, "<").replace(/&gt;/g, ">");

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