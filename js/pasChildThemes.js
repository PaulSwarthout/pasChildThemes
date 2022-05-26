"use strict;"
/* pasChildThemes.js
 * This file contains nearly pure JavaScript code.
 */

if (typeof(String.prototype.trim) == "undefined") {
	String.prototype.trim = function () {
		return this.ltrim().rtrim();
	}
}

/* 
 * pas_cth_js_selectFile() called from an onclick event in ListFolderFiles() in /pasChildThemes.php
 */
function pas_cth_js_selectFile(event) {
	var element = document.getElementById(event.srcElement.dataset.elementid);
	var jsInput;
	var box;

	pas_cth_spinner.wait_cursor();

	// requires HTML5 global attribute support for "data-*"
	jsInput = JSON.parse(element.getAttribute("data-jsdata"));
	/*
	 * If selected file is '/functions.php' or '/style.css' display a message, then bail.
	 */
	if (jsInput['directory'].length == 0 && (jsInput['file'].toLowerCase() == "style.css" || jsInput['file'].toLowerCase() == "functions.php")) {
		msg = "<p class='warningHeading'>Action Not Allowed</p><br><br>" +
		      "Overwriting or deleting a theme's primary stylesheet or functions.php file is not allowed.<br><br>" +
			  "Click anywhere in this message box to close it.";


		box = pas_cth_js_showBox();
		box.innerHTML = msg;
		box.onclick = function () {
			if (this.parentNode != null) {
				this.parentNode.removeChild(this);
			}
			this.remove();
		}
		document.getElementById("pas_cth_actionBox").style.display = "inline";
		windowFlag = true; // Prevents the window.onclick event from closing this prompt.

		pas_cth_spinner.default_cursor();

		return;
	}


	element.setAttribute("data-jsdata", JSON.stringify(jsInput));


	switch (jsInput['themeType'].toLowerCase()) {
		case "child":
			jsInput['action'] = 'verifyRemoveFile';
			element.setAttribute("data-jsdata", JSON.stringify(jsInput));
			pas_cth_js_removeChildFile(element);
			break;
		case "parent":
			jsInput['action'] = 'verifyCopyFile';
			element.setAttribute("data-jsdata", JSON.stringify(jsInput));
			pas_cth_js_copyTemplateFile(element);
			break;
	}
	pas_cth_spinner.default_cursor();

}
function pas_cth_js_showWait() {
	pas_cth_spinner.wait_cursor();
//	var body = document.getElementsByTagName("body")[0];
//	body.style.cursor = "wait";
}
function pas_cth_js_hideWait() {
//	var body = document.getElementsByTagName("body")[0];
//	body.style.cursor = "";
	pas_cth_spinner.default_cursor();
}
var pas_cth_js_spinTimer = 0;
var pas_cth_js_spinPosition = 0;
function pas_cth_js_spin() {
}

/* removeChildFile() is called from an onclick event of a button press
 * set up in pas_cth_AJAXFunctions::selectFile()
 * in file '/pasChildThemes.php'
 */
function pas_cth_js_removeChildFile(element) {
	var jsInput = JSON.parse(element.getAttribute("data-jsdata"));
	var dataBlock = {};
	pas_cth_js_showWait();

	// $_POST[] values in pas_cth_AJAXFunctions::verifyRemoveFile()
	dataBlock.directory = jsInput['directory'];
	dataBlock.file		= jsInput['file'];
	var successCallback = function (response) {
		if (response.length) {
			pas_cth_js_processResponse(response);
			pas_cth_js_hideWait();
		} else {
			location.reload();
		}
	}
	var failureCallback = function (status, response) {
		var msg = "400 Error:<br>" + status;
		pas_cth_js_showBox().innerHTML = msg;
		pas_cth_js_hideWait();
	}
	pas_cth_js_AJAXCall(jsInput['action'], dataBlock, successCallback, failureCallback);
}
/* pas_cth_js_deleteChildFile() is called from an onclick event in a popup error box
 * set up in pas_cth_AJAXFunctions::verifyRemoveFile() in 'classes/class_ajax_functions.php'
 *
 */
function pas_cth_js_deleteChildFile(element) {
	var jsInput = JSON.parse(element.getAttribute("data-jsdata"));
	var dataBlock = {};
	pas_cth_js_showWait();

	// $_POST[] values in pas_cth_AJAXFunctions::deleteFile()
	dataBlock.directory = jsInput['directory'];
	dataBlock.file		= jsInput['file'];
	var successCallback = function (response) {
		if (response.length) {
			pas_cth_js_processResponse(response);
			pas_cth_js_hideWait();
		} else {
			location.reload();
		}
	}
	var failureCallback = function(status, response) {
		var msg = "400 Error:<br>" + status;
		pas_cth_js_showBox().innerHTML = msg;
		pas_cth_js_hideWait();
	}
	pas_cth_js_AJAXCall
(jsInput['action'], dataBlock, successCallback, failureCallback);
}
function pas_cth_js_successCallback(response) {
	if (response.length) {
		pas_cth_js_processResponse(response);
		pas_cth_js_hideWait();
		pas_cth_spinner.default_cursor();
	} else {
		location.reload();
	}
}
function pas_cth_js_failureCallback(status, response) {
	var msg = "400 Error:<br>" + status;
	pas_cth_js_showBox().innerHTML = msg;
	pas_cth_js_hideWait();
	pas_cth_spinner.default_cursor();
}
/*
 * pas_cth_js_copyTemplateFile() responds to an onclick event set up pas_cth_AJAXFunctions::selectFile()
 * in 'classes/class_ajax_functions.php' when a user clicks a file in the template theme files list.
 */
function pas_cth_js_copyTemplateFile(element) {
	var jsInput = JSON.parse(element.getAttribute("data-jsdata"));
	var dataBlock = {};

	// $_POST[] values in pas_cth_AJAXFunctions::verifyCopyFile()
	dataBlock.directory = jsInput['directory'];
	dataBlock.file		= jsInput['file'];
	pas_cth_js_showWait();
	var successCallback = function (response) {
		if (response.length) {
			pas_cth_js_processResponse(response);
			pas_cth_js_hideWait();
		} else {
			location.reload();
		}
	}
	var failureCallback = function (status, response) {
		var msg = "400 Error:<br>" + status + "<HR>" + response;
		pas_cth_js_showBox().innerHTML = msg;
		pas_cth_js_hideWait();
	}
	pas_cth_js_AJAXCall(jsInput['action'], dataBlock, successCallback, failureCallback);
}
function pas_cth_js_overwriteFile(element) {
	var jsInput = JSON.parse(element.getAttribute("data-jsdata"));
	var dataBlock = {};
	pas_cth_js_showWait();

	dataBlock.directory = jsInput['directory'];
	dataBlock.file		= jsInput['file'];
	// copyFile
	pas_cth_js_AJAXCall(jsInput['action'], dataBlock, pas_cth_js_successCallback, pas_cth_js_failureCallback);
}
function pas_cth_js_resetChildThemeForm(frm) {
	var formElements = frm.elements, ndx;
	for (ndx = 0; ndx < formElements.length; ndx++) {
		formElements[ndx].style.backgroundColor = "";
	}
	frm.reset();
}
/* The pas_cth_js_createChildTheme() function processes the form in
 * pas_cth_ChildThemesHelper::manage_child_themes() in file 'classes/class_childThemesHelper.php'
 * without actually executing a "Submit" on that form. This prevents the page refresh and allows
 * us to redirect to the admin_url("themes.php") page once the child theme has been created.
 */
function pas_cth_js_createChildTheme(element) {
	var ndx = 0,
		frm = element.form,
		formElements = frm.elements,
		dataBlock = {},
		jsInput,
		action,
		e, err = 0;
	pas_cth_js_showWait();
	// Used to validate the data in the form before making the AJAX call to save the data and create the child theme.
	this.test = function (elementValue = "", elementPattern = "", elementRequiredFlag = false) {
		var re;
		var result = false;
		if (elementRequiredFlag) {
			if (elementValue.length) {
				if (elementPattern.length) { // element is required, value is not blank, pattern exists, return true if value matches pattern.
					re = new RegExp(elementPattern);
					result = re.test(elementValue);
				} else { // element is required, value is not blank, no pattern specified, so any value will do, return true
					result = true;
				}
			} else  { // element is required, value is blank, return false;
				result = false;
			}
		} else {
			if (elementPattern.length) {
				if (elementValue.length) { // element is NOT required, value is not blank, pattern exists, return true if value matches pattern
					re = new RegExp(elementPattern);
					result = re.test(elementValue);
				} else { // element is NOT required. value is blank. return true;
					result = true;
				}
			} else { // element is NOT required, pattern is blank, return true
				result = true;
			}
		}
		return result;
	}
	var outputMessage = "";
	for (ndx = 0; ndx < formElements.length; ndx++) {
		e = formElements[ndx];
		if (! this.test(e.value, e.dataset.pattern, e.required) ) {
			e.style.backgroundColor = "#FFFF90";
			outputMessage += (outputMessage.length ? "<hr>" + e.dataset.message : e.dataset.message);
			err++;
		}
	}
	if (err) {
		var errorBox = pas_cth_library.displayError(outputMessage);
		errorBox.style.width = "300px";
		errorBox.style.height = "300px";
		errorBox.style.overflowY = "scroll";
		errorBox.style.marginLeft = "-150px";
		errorBox.style.marginTop = "-150px";
		pas_cth_js_hideWait();
		return;
	}



	/* Move the data from the form to the FormData object.
	 * Data will be accessible using the $_POST[] array in pas_cth_AJAXFunctions::createChildTheme()
	 * The "action" value, used by wp_ajax_* to target the appropriate PHP function, is an <INPUT>
	 * element and will get copied to the FormData in the first case of the switch statement below.
	 */
	for (ndx = 0; ndx < formElements.length; ndx++) {
		switch (formElements[ndx].tagName.toUpperCase()) {
			case "INPUT":
				if (formElements[ndx].name.toLowerCase() == "action") {
					action = formElements[ndx].value;
				} else {
					dataBlock[formElements[ndx].name] = formElements[ndx].value;
				}
				break;
			case "TEXTAREA":
		 		dataBlock[formElements[ndx].name] = formElements[ndx].value;
				break;
			case "SELECT":
				dataBlock[formElements[ndx].name] = formElements[ndx].options[formElements[ndx].selectedIndex].value;
				break;
			case "BUTTON":
				// ignore
				break;
		}
	}
	var successCallback = function (response) {
		if ("SUCCESS:" == response.left("SUCCESS:".length)) {
			location.href='/wp-admin/themes.php';
		} else if (response.length >= 1) {
			pas_cth_js_processResponse(response);
			pas_cth_js_hideWait();
		}
	}
	// AJAX call to pas_cth_AJAXFunctions::createChildTheme() in 'classes/class_ajax_functions.php'
	pas_cth_js_AJAXCall(action, dataBlock, successCallback, pas_cth_js_failureCallback);
}
// Responds to an onclick event, on a cancel button.
function pas_cth_js_cancelOverwrite(element) {
	var box = document.getElementById("pas_cth_actionBox");
	if (null == box.parentNode) {
		var theBody = document.getElementsByTagName("body")[0];
		theBody.removeChild(box);
	} else {
		box.parentNode.removeChild(box);
	}
	pas_cth_js_hideWait();
}
// Responds to an onclick event
function pas_cth_js_cancelDeleteChild(element) {
	var box = document.getElementById("pas_cth_actionBox");
	if (null == box.parentNode) {
		var theBody = document.getElementsByTagName("body")[0];
		theBody.removeChild(box);
	} else {
		box.parentNode.removeChild(box);
	}
	pas_cth_js_hideWait();
}


// Responds to an onblur event on the ScreenShot Options page.
function pas_cth_js_SetOption(element) {
	var dataBlock = {};

	dataBlock.optionName	= element.name;
	dataBlock.optionValue	= element.value;
	var successCallback = function (response) {
		alert("Here");
		if ("SUCCESS:" == response.left("SUCCESS:".length)) {
			location.href = "/wp-admin/themes.php";
		} else if (response.length >= 1) {
			pas_cth_js_showBox().innerHTML = response;
		}
		pas_cth_js_hideWait();
	}
	pas_cth_js_AJAXCall('saveOptions', dataBlock, successCallback, pas_cth_js_failureCallback);
}
function showChild() {
	document.getElementById("childGrid").style.display = "inline";
	document.getElementById("parentGrid").style.display = "none";
}
function showParent() {
	document.getElementById("childGrid").style.display = "none";
	document.getElementById("parentGrid").style.display = "inline";
}
function debugTip(action, msg) {
	switch (action.toLowerCase()) {
		case "show":
			var tipBox = document.createElement("div")
			tipBox.setAttribute("id", "tipBox")
			tipBox.setAttribute("class", "tipBox");
			tipBox.innerHTML = msg;


			tipBox.style.left = (mousePosition.x + 10) + "px";
			tipBox.style.top  = (mousePosition.y + 10) + "px";
			document.getElementsByTagName("body")[0].appendChild(tipBox);
			break;
		case "hide":
			kill(document.getElementById("tipBox"));
			break;
	}
}
function pas_cth_validateField(element = null) {
	if (element == null) {
		return;
	}
	var strValue	= element.value.trim();
	var ptrn		= element.dataset.pattern.trim();
	if (strValue.length == 0 || ptrn.length == 0) {
		return;
	}
	var re = new RegExp(ptrn);
	var box;
	var boxID = "pas_cth_actionBox";
	var parent = document.getElementsByTagName("body")[0];
	var onclickFunction = true;
	var className = "";


	if (! re.test(strValue)) {
		box = pas_cth_js_createBox(boxID, className, parent, onclickFunction);
		box.innerHTML = element.dataset.message + "<br><br>Click on this messagebox to close it";
	}
}
function pas_cth_js_launch(btn) {
	var ajaxAction = btn.getAttribute("data-ajax")
	var FN = function (response) {
		if (response.length > 0) {


		}
	}
	pas_cth_js_AJAXCall(ajaxAction);
}