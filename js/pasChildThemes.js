// pasChildThemes.js

/* selectFile() called from an onclick event in ListFolderFiles() in /pasChildThemes.php
 */
function selectFile(element) {
	var xmlhttp = new XMLHttpRequest()
	var data = new FormData()
	var jsInput

	jsInput = JSON.parse(element.getAttribute("data-jsdata")) // requires HTML5 global attribute support "data-*"

	xmlhttp.open("POST", ajaxurl,true) // AJAX call to "function pasChildThemes_selectFile()" in pasChildThemes.php

	data.append("directory",	jsInput["directory"])
	data.append("fileName",		jsInput["fileName"])
	data.append("themeType",	jsInput["themeType"])

/* AJAX Call to pasChildThemes_selectFile()
 * in '/lib/ajax_functions.php'
 */
	data.append("action",			"selectFile")

	xmlhttp.onreadystatechange = function () {
		if (4 == xmlhttp.readyState) {
			// strip the AJAX zero from wp_die() WORDPRESS ONLY
			var response = (xmlhttp.responseText.length >= 1 ? xmlhttp.responseText.left(xmlhttp.responseText.length - 1) : xmlhttp.responseText);

			switch (xmlhttp.status) {
				case 200: // Everything is Okay
					// If responseText is not empty, there might be a request to overwrite
					// or a request to delete that needs to be displayed.
					// else, reload the page.
					// <= 1 accounts for the AJAX return of zero that sometimes shows up despite my best efforts to avoid that.
					if (response.length <= 0) {
						location.reload();
					} else {
						processResponse(response);
					}
					break;

				case 400: // There was an error
					msg = "400 Error:<br>" + xmlhttp.statusText
					showBox().innerHTML = msg
					break;
			}
		}
	}
	xmlhttp.send(data);
}
/* removeChildFile() is called from an onclick event of a button press set up in pasChildThemes_selectFile()
 * in file '/pasChildThemes.php'
 */
function removeChildFile(element) {
	var xmlhttp = new XMLHttpRequest()
	var data = new FormData()
	var jsInput = JSON.parse(element.getAttribute("data-jsdata"))

	xmlhttp.open("POST", ajaxurl, true)

	data.append("childStylesheet",		jsInput['childStylesheet']);
	data.append("templateStylesheet", jsInput['templateStylesheet']);
	data.append("directory",					jsInput['directory'] );
	data.append("childFileToRemove",	jsInput['file']);

/* AJAX call to pasChildThemes_verifyRemoveFile()
 * in '/lib/ajax_functions.php'
 */
	data.append("action",							jsInput['delete_action']); // verifyRemoveFile

	xmlhttp.onreadystatechange = function () {
		if (4 == xmlhttp.readyState) {
			var response = (xmlhttp.responseText.length >= 1 ? xmlhttp.responseText.left(xmlhttp.responseText.length - 1) : xmlhttp.responseText);

			switch (xmlhttp.status) {
				case 200: // Everything is okay
					if (response.length <= 0) {
						location.reload()
					} else {
						processResponse(response);
					}
					break;

				case 400:
					msg = "400 Error:<br>" + xmlhttp.statusText
					showBox().innerHTML = msg
					break;
			}
		}
	}
	xmlhttp.send(data)
}
/* deleteChildFile() is called from an onclick event in a popup error box set up in
 */
function deleteChildFile(element) {
	var xmlhttp = new XMLHttpRequest()
	var data = new FormData()
	var jsInput = JSON.parse(element.getAttribute("data-jsdata"))

	xmlhttp.open("POST", ajaxurl, true)

	data.append("themeRoot",		jsInput['childThemeRoot'] );
	data.append("stylesheet",		jsInput['childStylesheet'] );
	data.append("directory",		jsInput['directory'] );
	data.append("fileToDelete", jsInput['childFileToRemove']);

/* AJAX call to pasChildThemes_deleteFile
 * in '/lib/ajax_functions.php'
 */
	data.append("action",				jsInput['action']);

	xmlhttp.onreadystatechange = function () {
		if (4 == xmlhttp.readyState) {
			var response = (xmlhttp.responseText.length >= 1 ? xmlhttp.responseText.left(xmlhttp.responseText.length - 1) : xmlhttp.responseText);

			switch (xmlhttp.status) {
				case 200: // Everything is okay
					if (response.length <= 0) {
						location.reload()
					} else {
						processResponse(response);
					}
					break;

				case 400:
					msg = "400 Error:<br>" + xmlhttp.statusText
					showBox().innerHTML = msg
					break;
			}
		}
	}
	xmlhttp.send(data)
}
/* copyTemplateFile() responds to an onclick event set up by pasChildThemes_selectFile
 * when a template theme file is clicked.
 */
function copyTemplateFile(element) {
	var xmlhttp = new XMLHttpRequest();
	var data = new FormData();
	var jsInput = JSON.parse(element.getAttribute("data-jsdata"));

	xmlhttp.open("POST", ajaxurl, true);

	data.append("childStylesheet",		jsInput['childStylesheet']);
	data.append("templateStylesheet", jsInput['templateStylesheet']);
	data.append("directory",					jsInput['directory'] );
	data.append("templateFileToCopy",	jsInput['file']);

/* AJAX call to pasChildThemes_verifyCopyFile in '/lib/ajax_functions.php
 */
	data.append("action",							jsInput['copy_action']);

	xmlhttp.onreadystatechange = function () {
		if (4 == xmlhttp.readyState) {
			var response = (1 <= xmlhttp.responseText.length ? xmlhttp.responseText.left(xmlhttp.responseText.length - 1) : xmlhttp.responseText);
			switch (xmlhttp.status) {
				case 200: // Everything is okay
					if (response.length <= 0) {
						location.reload();
					} else {
						processResponse(response);
					}
					break;

				case 400:
					msg = "400 Error:<br>" + xmlhttp.statusText + "<HR>" + response;
					showBox().innerHTML = msg;
					break;
			}
		}
	}
	xmlhttp.send(data);
}
function overwriteFile(element) {
	var xmlhttp = new XMLHttpRequest()
	var data = new FormData()
	var jsInput = JSON.parse(element.getAttribute("data-jsdata"))

	xmlhttp.open("POST", ajaxurl, true) // AJAX call to "function pasChildThemes_copyFile()" in pasChildThemes.php
	data.append("childThemeRoot",			jsInput["childThemeRoot"])
	data.append("childStylesheet",		jsInput["childStylesheet"])
	data.append("templateThemeRoot",	jsInput["templateThemeRoot"])
	data.append("templateStylesheet",	jsInput["templateStylesheet"])
	data.append("directory",					jsInput["directory"])
	data.append("templateFileToCopy",	jsInput["templateFileToCopy"])
	data.append("action",							jsInput["action"]) // defines the php function: 'wp_ajax_copyFile' --> pasChildThemes_copyFile()

	xmlhttp.onreadystatechange = function () {
		if (4 == xmlhttp.readyState) {
			var response = (xmlhttp.responseText.length >= 1 ? xmlhttp.responseText.left(xmlhttp.responseText.length - 1) : xmlhttp.responseText);

			switch (xmlhttp.status) {
				case 200: // Everything is okay
					if (response.length <= 0) {
						location.reload();
					} else {
						processResponse(response);
					}
					break;

				case 400: // There was an error
					msg = "400 Error:<br>" + xmlhttp.statusText
					showBox().innerHTML = msg
					break;
			}
		}
	}

	xmlhttp.send(data);
}

function createChildTheme(element) {
	var frm = element.form
	var formElements = frm.elements
	var xmlhttp = new XMLHttpRequest()
	var data = new FormData()
	var jsInput

	for (ndx = 0; ndx < formElements.length; ndx++) {
		switch (formElements[ndx].tagName.toUpperCase()) {
			case "INPUT":
				data.append(formElements[ndx].name, formElements[ndx].value)
				break;
			case "TEXTAREA":
				data.append(formElements[ndx].name, formElements[ndx].value)
				break;
			case "SELECT":
				data.append(formElements[ndx].name, formElements[ndx].options[formElements[ndx].selectedIndex].value)
				break;
			case "BUTTON":
				// ignore
				break;
		}
	}
	xmlhttp.open("POST", ajaxurl, true);
	xmlhttp.onreadystatechange = function () {
		if (4 == xmlhttp.readyState) {
			// strip the AJAX zero from wp_die() WORDPRESS ONLY
			var response = (xmlhttp.responseText.length >= 1 ? xmlhttp.responseText.left(xmlhttp.responseText.length - 1) : xmlhttp.responseText);

			switch (xmlhttp.status) {
				case 200: // Everything is Okay
					// If responseText is not empty, there might be a request to overwrite
					// or a request to delete that needs to be displayed.
					// else, reload the page.
					// <= 1 accounts for the AJAX return of zero that sometimes shows up despite my best efforts to avoid that.
					if ("SUCCESS:" == response.left("SUCCESS:".length)) {
						location.href="/wp-admin/themes.php"
					} else if (response.length >= 1) {
						showBox().innerHTML = response
					}
					break;

				case 400: // There was an error
					msg = "400 Error:<br>" + xmlhttp.statusText + "<br>" + response;
					showBox().innerHTML = msg
					break;
			}
		}
	}
	xmlhttp.send(data);
}
function cancelOverwrite(element) {
	var box = document.getElementById("actionBox")
	if (null == box.parentNode) {
		var theBody = document.getElementsByTagName("body")[0];
		theBody.removeChild(box);
	} else {
		box.parentNode.removeChild(box);
	}
}
function cancelDeleteChild(element) {
	var box = document.getElementById("actionBox")
	if (null == box.parentNode) {
		var theBody = document.getElementsByTagName("body")[0];
		theBody.removeChild(box);
	} else {
		box.parentNode.removeChild(box);
	}
}
function editFile(element) {
	alert("Coming soon. This feature is not yet implemented.")
}
