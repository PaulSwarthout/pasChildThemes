function pas_cth_js_editFile(element) {
	var xmlhttp = new XMLHttpRequest();
	var data = new FormData();
	var jsInput = JSON.parse(element.getAttribute("data-jsdata"));
	var box;

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
        }
	} else if (document.selection && document.selection.createRange) {
        document.selection.createRange().text = text;
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
	var editFile = document.getElementById("editFile")
	var editBox = document.getElementById("editBox")
	var wpbodyContent = document.getElementById("wpbody-content")
	var parentPosition = getTopLeftPosition(wpbodyContent);
	var efButtonRow = document.getElementById("ef_buttonRow")
	var themeGrid = document.getElementById("themeGrid");
	var saveButton = document.getElementById("ef_saveButton");
	var closeButton = document.getElementById("ef_closeButton");

	var directoryINP = document.getElementById("directory");
	var filenameINP = document.getElementById("file")
	var themeTypeINP = document.getElementById("themeType");
	var readOnlyFlag = document.getElementById("readOnlyFlag");

	var responseSections = parseOutput(response);
	responseSections.ARGS = JSON.parse(responseSections.ARGS);

	directoryINP.value = responseSections.ARGS['directory'];
	filenameINP.value = responseSections.ARGS['file'];
	themeTypeINP.value = responseSections.ARGS['themeType'];
	readOnlyFlag.value = responseSections.ARGS['readOnlyFlag'];


	saveButton.disabled = true;

	editBox.innerHTML = responseSections.EDITBOX
	editBox.style.position = "absolute";
	editBox.style.left = "0px";
	editBox.style.top  = "30px";

	if (readOnlyFlag.value.toLowerCase() == "true") {
		editBox.contentEditable = false;
		document.getElementById("ef_readonly_msg").style.display = "inline";
	} else {
		editBox.contentEditable = true;
		document.getElementById("ef_readonly_msg").style.display = "none";
	}

	editFile.style.position = "absolute";
	editFile.style.left = parentPosition.left + "px";
	editFile.style.top = parentPosition.top + "px";

	editBox.onkeydown = function () { captureKeystrokes(this) }

	editFile.style.display = "inline-grid";
	themeGrid.style.display = "none";

}
function closeEditFile() {
	var editBox		= document.getElementById("editBox");
	var editFile	= document.getElementById("editFile");
	var themeGrid	= document.getElementById("themeGrid");
	var saveButton	= document.getElementById("ef_saveButton");
	var prompt		= document.getElementById("savePrompt");

	if ( ! saveButton.disabled) {
		prompt.style.cssText = "display:inline;"
	} else {
		themeGrid.style.display = "inline-grid";
		editFile.style.display = "none";
		editBox.innerHTML = "";
	}
}
function pas_cth_js_closeFile() {
	var savePrompt = document.getElementById("savePrompt");
	var saveButton = document.getElementById("ef_saveButton");

	saveButton.disabled = true;
	savePrompt.style.display = "none";
	closeEditFile();

}
function editBoxChange(element) {
	var saveButton = document.getElementById("ef_saveButton");
	saveButton.disabled = false;
}
function captureKeystrokes(element) {
	if (event.keyCode == 9) {
		event.preventDefault();
		insertTextAtCursor(String.fromCharCode(9));
	}
}
function debug(element) {
	var wpbodyContent = document.getElementById("wpbody-content");
	var elementList = wpbodyContent.getElementsByTagName("*")

	debugger;
}
function pas_cth_js_saveFile() {

	var xmlhttp = new XMLHttpRequest();
	var data = new FormData();
	var saveButton	= document.getElementById("ef_saveButton");

	xmlhttp.open("POST", ajaxurl, true);

	var editBox = document.getElementById("editBox")
	var directoryINP = document.getElementById("directory");
	var fileINP		 = document.getElementById("file")
	var themeTypeINP = document.getElementById("themeType")

	var fileContents = editBox.innerHTML.replace(/&lt;/g, "<").replace(/&gt;/g, ">");

	data.append("fileContents", fileContents);
	data.append("file", fileINP.value);
	data.append("directory", directoryINP.value);
	data.append("themeType", themeTypeINP.value);
	data.append("action", "saveFile");

	saveButton.disabled = true;
	closeEditFile();
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
