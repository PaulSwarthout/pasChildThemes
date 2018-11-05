/* pasChildThemes.js
 * This file contains nearly pure JavaScript code.
 * With the exception of XMLHttpRequest() and JSON, no other WordPress core, nor 3rd party JavaScript
 * libraries are used, herein.
 */


/* pas_cth_js_selectFile() called from an onclick event in ListFolderFiles() in /pasChildThemes.php
 */
function pas_cth_js_noEditYet() {
	var box = pas_cth_js_showBox();
	var msg = "<p class='warningHeading'>Not Yet Implemented</p><br><br>" +
			  "The ability to directly edit a file has not been created yet. " +
			  "We anticipate that feature will be available in version 1.3.x.<br><br>" +
			  "Click anywhere in this message to close it.";
	box.onclick = function () {
		kill(this);
	}
	box.innerHTML = msg;
}
function pas_cth_js_selectFile(element) {
	var xmlhttp = new XMLHttpRequest();
	var data = new FormData();
	var jsInput;
	var box;

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
		document.getElementById("actionBox").style.display = "inline";
		windowFlag = true; // Prevents the window.onclick event from closing this prompt.
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
}


/* removeChildFile() is called from an onclick event of a button press
 * set up in pas_cth_AJAXFunctions::selectFile()
 * in file '/pasChildThemes.php'
 */
function pas_cth_js_removeChildFile(element) {
	var xmlhttp = new XMLHttpRequest();
	var data = new FormData();
	var jsInput = JSON.parse(element.getAttribute("data-jsdata"));

/* AJAX call to pas_cth_AJAXFunctions::verifyRemoveFile()
 * in 'classes/class_ajax_functions.php'
 */
	xmlhttp.open("POST", ajaxurl, true);

	data.append("action", jsInput['action']); // verifyRemoveFile

	// $_POST[] values in pas_cth_AJAXFunctions::verifyRemoveFile()
	data.append("directory",	jsInput['directory'] );
	data.append("file",			jsInput['file']);


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
/* pas_cth_js_deleteChildFile() is called from an onclick event in a popup error box
 * set up in pas_cth_AJAXFunctions::verifyRemoveFile() in 'classes/class_ajax_functions.php'
 *
 */
function pas_cth_js_deleteChildFile(element) {
	var xmlhttp = new XMLHttpRequest();
	var data = new FormData();
	var jsInput = JSON.parse(element.getAttribute("data-jsdata"));

	// AJAX call to pas_cth_AJAXFunctions::deleteFile() in 'classes/class_ajax_functions.php'
	xmlhttp.open("POST", ajaxurl, true);
	data.append("action",		jsInput['action']);

	// $_POST[] values in pas_cth_AJAXFunctions::deleteFile()
	data.append("directory",	jsInput['directory'] );
	data.append("file",			jsInput['file']);


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
	xmlhttp.send(data);
}
/*
 * pas_cth_js_copyTemplateFile() responds to an onclick event set up pas_cth_AJAXFunctions::selectFile()
 * in 'classes/class_ajax_functions.php' when a user clicks a file in the template theme files list.
 */
function pas_cth_js_copyTemplateFile(element) {
	var xmlhttp = new XMLHttpRequest();
	var data = new FormData();
	var jsInput = JSON.parse(element.getAttribute("data-jsdata"));

	// AJAX call to pas_cth_AJAXFunctions::verifyCopyFile() in 'classes/class_ajax_functions.php'
	xmlhttp.open("POST", ajaxurl, true);
	data.append("action",				jsInput['action']);

	// $_POST[] values in pas_cth_AJAXFunctions::verifyCopyFile()
	data.append("directory",	jsInput['directory'] );
	data.append("file",			jsInput['file']);

	xmlhttp.onreadystatechange = function () {
		if (4 == xmlhttp.readyState) {
			var response = (1 <= xmlhttp.responseText.length ?
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
					msg = "400 Error:<br>" + xmlhttp.statusText + "<HR>" + response;
					pas_cth_js_showBox().innerHTML = msg;
					break;
			}
		}
	}
	xmlhttp.send(data);
}
function pas_cth_js_overwriteFile(element) {
	var xmlhttp = new XMLHttpRequest();
	var data = new FormData();
	var jsInput = JSON.parse(element.getAttribute("data-jsdata"));

	// AJAX call to pas_cth_AJAXFunctions::copyFile() in 'classes/class_ajax_functions.php'
	xmlhttp.open("POST", ajaxurl, true);
	data.append("action",		jsInput["action"]); // copyFile

	// $_POST[] values in pas_cth_AJAXFunctions::copyFile()
	data.append("directory",	jsInput["directory"]);
	data.append("file",			jsInput["file"]);

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

				case 400: // There was an error
					msg = "400 Error:<br>" + xmlhttp.statusText;
					pas_cth_js_showBox().innerHTML = msg;
					break;
			}
		}
	}

	xmlhttp.send(data);
}
/* The pas_cth_js_createChildTheme() function processes the form in
 * pas_cth_ChildThemesHelper::manage_child_themes() in file 'classes/class_childThemesHelper.php'
 * without actually executing a "Submit" on that form. This prevents the page refresh and allows
 * us to redirect to the admin_url("themes.php") page once the child theme has been created.
 */
function pas_cth_js_createChildTheme(element) {
	var frm = element.form;
	var formElements = frm.elements;
	var xmlhttp = new XMLHttpRequest();
	var data = new FormData();
	var jsInput;

	/* Move the data from the form to the FormData object.
	 * Data will be accessible using the $_POST[] array in pas_cth_AJAXFunctions::createChildTheme()
	 * The "action" value, used by wp_ajax_* to target the appropriate PHP function, is an <INPUT>
	 * element and will get copied to the FormData in the first case of the switch statement below.
	 */
	for (ndx = 0; ndx < formElements.length; ndx++) {
		switch (formElements[ndx].tagName.toUpperCase()) {
			case "INPUT":
				data.append(formElements[ndx].name,
							formElements[ndx].value);
				break;
			case "TEXTAREA":
				data.append(formElements[ndx].name,
							formElements[ndx].value);
				break;
			case "SELECT":
				data.append(formElements[ndx].name,
							formElements[ndx].options[formElements[ndx].selectedIndex].value);
				break;
			case "BUTTON":
				// ignore
				break;
		}
	}
	// AJAX call to pas_cth_AJAXFunctions::createChildTheme() in 'classes/class_ajax_functions.php'
	xmlhttp.open("POST", ajaxurl, true);

	xmlhttp.onreadystatechange = function () {
		if (4 == xmlhttp.readyState) {
			// strip the AJAX zero from wp_die() WORDPRESS ONLY
			var response = (xmlhttp.responseText.length >= 1 ?
								xmlhttp.responseText.left(xmlhttp.responseText.length - 1) :
								xmlhttp.responseText);

			switch (xmlhttp.status) {
				case 200: // Everything is Okay
					/* If responseText is not empty, there might be a request to overwrite
					 * or a request to delete that needs to be displayed.
					 * else, reload the page.
					 * <= 1 accounts for the AJAX return of zero that sometimes shows up
					 * despite my best efforts to avoid that.
					 */
					if ("SUCCESS:" == response.left("SUCCESS:".length)) {
						location.href="/wp-admin/themes.php";
					} else if (response.length >= 1) {
						pas_cth_js_processResponse(response);
					}
					break;

				case 400: // There was an error
					msg = "400 Error:<br>" + xmlhttp.statusText + "<br>" + response;
					pas_cth_js_showBox().innerHTML = msg;
					break;
			}
		}
	}
	xmlhttp.send(data);
}
// Responds to an onclick event, on a cancel button.
function pas_cth_js_cancelOverwrite(element) {
	var box = document.getElementById("actionBox");
	if (null == box.parentNode) {
		var theBody = document.getElementsByTagName("body")[0];
		theBody.removeChild(box);
	} else {
		box.parentNode.removeChild(box);
	}
}
// Responds to an onclick event
function pas_cth_js_cancelDeleteChild(element) {
	var box = document.getElementById("actionBox");
	if (null == box.parentNode) {
		var theBody = document.getElementsByTagName("body")[0];
		theBody.removeChild(box);
	} else {
		box.parentNode.removeChild(box);
	}
}

function pas_cth_js_editFile(element) {
	alert("Coming soon. This feature is not yet implemented.");
}

// Responds to an onblur event on the ScreenShot Options page.
function pas_cth_js_SetOption(element) {
	var xmlhttp = new XMLHttpRequest();
	var data = new FormData();

	data.append('action', 'saveOptions');
	data.append('optionName', element.name);
	data.append('optionValue', element.value);

	xmlhttp.open("POST", ajaxurl, true);
	xmlhttp.onreadystatechange = function () {
		if (4 == xmlhttp.readyState) {
			// strip the AJAX zero from wp_die() WORDPRESS ONLY
			var response = (xmlhttp.responseText.length >= 1 ?
								xmlhttp.responseText.left(xmlhttp.responseText.length - 1) :
								xmlhttp.responseText);

			switch (xmlhttp.status) {
				case 200: // Everything is Okay
					/* If responseText is not empty, there might be a request to overwrite
					 * or a request to delete that needs to be displayed.
					 * else, reload the page.
					 * <= 1 accounts for the AJAX return of zero that sometimes shows up
					 * despite my best efforts to avoid that.
					 */
					if ("SUCCESS:" == response.left("SUCCESS:".length)) {
						location.href="/wp-admin/themes.php"
					} else if (response.length >= 1) {
						pas_cth_js_showBox().innerHTML = response;
					}
					break;

				case 400: // There was an error
					msg = "400 Error:<br>" + xmlhttp.statusText + "<br>" + response;
					pas_cth_js_showBox().innerHTML = msg;
					break;
			}
		}
	}
	xmlhttp.send(data);
}
function pas_cth_js_mouseOver(element) {
	var jsdata = JSON.parse(element.getAttribute("data-jsdata"));
	var themeType = jsdata['themeType'];
	var filename = jsdata['file'];
	var msg;
	var mID = document.getElementById("hoverPrompt");


	switch (themeType.toLowerCase()) {
		case "child":
			msg = "File: <font class='fileHighlight'>" + filename + "</font><br>" +
				  "<div id='innerLine'>" +
				  "  <font class='actionPrompt'>Left Click</font> to <font class='redHighlight'>Remove</font> from the Child Theme.<br>" +
				  "  <br>" +
				  "  <font class='actionPrompt'>Right Click</font> to <font class='redHighlight'>Edit</font> the file." +
				  "</div>";
			break;
		case "parent":
			msg = "File: <font class='fileHighlight'>" + filename + "</font><br>" +
				  "<div id='innerLine'>" +
				  "  <font class='actionPrompt'>Left Click</font> to <font class='redHighlight'>Copy</font> to the Child Theme.<br>" +
				  "  <br>" +
				  "	 <font class='actionPrompt'>Right Click</font> to <font class='redHighlight'>Edit</font> the file." +
				  "</div>";
			break;
	}
	mID.innerHTML = msg;
	mID.style.cssText = "visibility:visible;";
	mID.style.left = mousePosition["x"] + 25 + "px";
	mID.style.top = mousePosition["y"] + "px";
}
function pas_cth_js_mouseOut(element) {
	var mID = document.getElementById("hoverPrompt");
	mID.style.visibility = "hidden";
	mID.innerHTML = "";
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