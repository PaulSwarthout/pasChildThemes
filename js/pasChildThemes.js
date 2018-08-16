/* pasChildThemes.js
 * This file contains nearly pure JavaScript code.
 * With the exception of XMLHttpRequest() and JSON, no other WordPress core, nor 3rd party JavaScript
 * libraries are used, herein.
 */


/* pas_cth_js_selectFile() called from an onclick event in ListFolderFiles() in /pasChildThemes.php
 */
function pas_cth_js_selectFile(element) {
	var xmlhttp = new XMLHttpRequest();
	var data = new FormData();
	var jsInput;

	// requires HTML5 global attribute support for "data-*"
	jsInput = JSON.parse(element.getAttribute("data-jsdata"));

/* AJAX Call to pas_cth_AJAXFunctions::selectFile()
 * in 'classes/class_ajax_functions.php'
 */
	xmlhttp.open("POST", ajaxurl,true);
	data.append("action",	"selectFile");

	// $_POST[] values in pas_cth_AJAXFunctions::selectFile()
	data.append("directory",	jsInput["directory"]);
	data.append("fileName",		jsInput["fileName"]);
	data.append("themeType",	jsInput["themeType"]);

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
					if (response.length <= 0) {
						// refresh the current display
						location.reload();
					} else {
						// display any output from the wp_ajax_* function.
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
	data.append("action",							jsInput['delete_action']); // verifyRemoveFile

	// $_POST[] values in pas_cth_AJAXFunctions::verifyRemoveFile()
	data.append("childStylesheet",		jsInput['childStylesheet']);
	data.append("templateStylesheet", jsInput['templateStylesheet']);
	data.append("directory",					jsInput['directory'] );
	data.append("childFileToRemove",	jsInput['file']);


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
	data.append("action",				jsInput['action']);

	// $_POST[] values in pas_cth_AJAXFunctions::deleteFile()
	data.append("themeRoot",			jsInput['childThemeRoot'] );
	data.append("stylesheet",			jsInput['childStylesheet'] );
	data.append("directory",			jsInput['directory'] );
	data.append("childFileToRemove",	jsInput['childFileToRemove']);


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
	data.append("action",				jsInput['copy_action']);

	// $_POST[] values in pas_cth_AJAXFunctions::verifyCopyFile()
	data.append("childStylesheet",		jsInput['childStylesheet']);
	data.append("templateStylesheet",	jsInput['templateStylesheet']);
	data.append("directory",			jsInput['directory'] );
	data.append("templateFileToCopy",	jsInput['file']);

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
	data.append("action",				jsInput["action"]); // copyFile

	// $_POST[] values in pas_cth_AJAXFunctions::copyFile()
	data.append("childThemeRoot",		jsInput["childThemeRoot"]);
	data.append("childStylesheet",		jsInput["childStylesheet"]);
	data.append("templateThemeRoot",	jsInput["templateThemeRoot"]);
	data.append("templateStylesheet",	jsInput["templateStylesheet"]);
	data.append("directory",			jsInput["directory"]);
	data.append("templateFileToCopy",	jsInput["templateFileToCopy"]);

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