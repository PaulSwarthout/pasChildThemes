// pasChildThemes.js
if(typeof String.prototype.ltrim == "undefined") String.prototype.ltrim = function(){return this.replace(/^\s+/,"");}
if(typeof String.prototype.rtrim == "undefined") String.prototype.rtrim = function(){return this.replace(/\s+$/,"");}
if(typeof String.prototype.trim == "undefined") String.prototype.trim = function(){var str = this.ltrim();return str.rtrim();}
if(typeof String.prototype.right == "undefined") String.prototype.right = function(n){return this.substring(this.length - n, this.length)}
if(typeof String.prototype.left == "undefined") String.prototype.left = function(n) { return this.substring(0, n); }

// KillMe kills the error message boxes.
function killMe(element) {
	var elements
	element.parentNode.removeChild(element);
	element.remove();

	elements = document.getElementsByName("errorMessageBox")
	if (elements.length == 0) {
		var actionBox = document.getElementById("actionBox")
		actionBox.parentNode.removeChild(actionBox)
		actionBox.remove()
	}

}
function cancelOverwrite(element) {
	var box = document.getElementById("actionBox")
	if (box.parentNode == null) {
		var theBody = document.getElementsByTagName("body")[0];
		theBody.removeChild(box);
	} else {
		box.parentNode.removeChild(box);
	}
}
function cancelDeleteChild(element) {
	var box = document.getElementById("actionBox")
	if (box.parentNode == null) {
		var theBody = document.getElementsByTagName("body")[0];
		theBody.removeChild(box);
	} else {
		box.parentNode.removeChild(box);
	}
}
// removeChildFile will call deleteChildFile once it has been determined that the file being deleted hasn't been
//    modified and if it has, the user has decided to delete it anyway.
function removeChildFile(element) {
	var xmlhttp = new XMLHttpRequest()
	var data = new FormData()
	var jsInput = JSON.parse(element.getAttribute("data-jsdata"))

	xmlhttp.open("POST", ajaxurl, true)

	data.append("childThemeRoot",			jsInput['childThemeRoot'] );
	data.append("childStylesheet",		jsInput['childStylesheet']);
	data.append("templateThemeRoot",	jsInput["templateThemeRoot"]);
	data.append("templateStylesheet", jsInput['templateStylesheet']);
	data.append("directory",					jsInput['directory'] );
	data.append("childFileToRemove",	jsInput['childFileToRemove']);
	data.append("action",							jsInput['action']); // verifyRemoveFile

	xmlhttp.onreadystatechange = function () {
		if (xmlhttp.readyState == 4) {
			var response = (xmlhttp.responseText.length >= 1 ? xmlhttp.responseText.left(xmlhttp.responseText.length - 1) : xmlhttp.responseText);
debugger
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
function deleteChildFile(element) {
	var xmlhttp = new XMLHttpRequest()
	var data = new FormData()
	var jsInput = JSON.parse(element.getAttribute("data-jsdata"))

	xmlhttp.open("POST", ajaxurl, true)

	data.append("themeRoot",		jsInput['childThemeRoot'] );
	data.append("stylesheet",		jsInput['childStylesheet'] );
	data.append("directory",		jsInput['directory'] );
	data.append("fileToDelete", jsInput['childFileToRemove']);
	data.append("action",				jsInput['action']);

	xmlhttp.onreadystatechange = function () {
		if (xmlhttp.readyState == 4) {
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

function overwriteFile(element) {
	var xmlhttp = new XMLHttpRequest()
	var data = new FormData()
	var jsInput = JSON.parse(element.getAttribute("data-jsdata"))

	xmlhttp.open("POST", ajaxurl, true) // AJAX call to "function pasChildThemes_copyFile()" in pasChildThemes.php
	data.append("sourceFile", jsInput["sourceFile"])
	data.append("destinationFile", jsInput["destinationFile"])
	data.append("delimiter", jsInput["delimiter"])
	data.append("action", jsInput["action"]) // defines the php function: 'wp_ajax_copyFile' --> pasChildThemes_copyFile()

	xmlhttp.onreadystatechange = function () {
		if (xmlhttp.readyState == 4) {
			var response = (xmlhttp.responseText.length >= 1 ? xmlhttp.responseText.left(xmlhttp.responseText.length - 1) : xmlhttp.responseText);

			switch (xmlhttp.status) {
				case 200: // Everything is okay
					if (response.length <= 0) {
						location.reload();
					} else {
						showBox().innerHTML = response
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
function selectFile(element) {
	var xmlhttp = new XMLHttpRequest()
	var data = new FormData()
	var jsInput

	jsInput = JSON.parse(element.getAttribute("data-jsdata")) // requires HTML5 global attribute support "data-*"

	xmlhttp.open("POST", ajaxurl,true) // AJAX call to "function pasChildThemes_selectFile()" in pasChildThemes.php

	data.append("action",			"selectFile") // '/wp-content/plugins/pasChildThemes/lib/ajax_functions.php' function pasChildThemes_selectFile()
	data.append("directory",	jsInput["directory"])
	data.append("fileName",		jsInput["fileName"])
	data.append("themeType",	jsInput["themeType"])

	xmlhttp.onreadystatechange = function () {
		if (xmlhttp.readyState == 4) {
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
function processResponse(response) {
	if (response.left("MENU:{".length).toUpperCase() == "MENU:{") {
		menuResponse = response.right(response.length - "menu:{".length)
		menuResponse = menuResponse.left(menuResponse.length - 1);
		box = showBox()
		box.setAttribute("id", "themeMenu")
		box.innerHTML = menuResponse;

	} else if (response.left("DEBUG:{".length).toUpperCase() == "DEBUG:{") {
		actionBox = document.getElementById("actionBox")
		if (actionBox != null && actionBox != undefined) {
			actionBox.parentNode.removeChild(actionBox);
			actionBox.remove();
		}
		debugResponse = response.right(response.length - "debug:{".length)
		debugResponse = debugResponse.left(debugResponse.length - 1);
		box = createBox('debugBox', 'debug')
		box.innerHTML = debugResponse;
	} else {
		showBox().innerHTML = response
	}
}
function showBox() {
	var box = document.getElementById("actionBox")
	var e;
	if (box == null || box == undefined) {
		var box = document.createElement("div")
		var theBody = document.getElementsByTagName("body")[0];
		box.setAttribute("id", "actionBox");
		theBody.appendChild(box);
		box.onclick=function () {this.parentNode.removeChild(this);this.remove();}
	}
	return box;

}
function createBox(id,className) {
	var box = document.getElementById(id)
	if (box != null && box != undefined) {
		box.parentNode.removeChild(box);
		box.remove();
	}
	box = document.createElement("div")
	box.setAttribute("id", id)
	box.className = className
	theBody = document.getElementsByTagName("body")[0];
	theBody.appendChild(box)
	box.onclick= function () { this.parentNode.removeChild(this); this.remove();}
	return box;
}

function resetForm(frm) {
	frm.reset();
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
		if (xmlhttp.readyState == 4) {
			// strip the AJAX zero from wp_die() WORDPRESS ONLY
			var response = (xmlhttp.responseText.length >= 1 ? xmlhttp.responseText.left(xmlhttp.responseText.length - 1) : xmlhttp.responseText);

			switch (xmlhttp.status) {
				case 200: // Everything is Okay
					// If responseText is not empty, there might be a request to overwrite
					// or a request to delete that needs to be displayed.
					// else, reload the page.
					// <= 1 accounts for the AJAX return of zero that sometimes shows up despite my best efforts to avoid that.
					if (response.left("SUCCESS:".length) == "SUCCESS:") {
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

function showData(element) {
	var normalClass = "debugger"
	var pauseClass = "debuggerHide"

	var jsdata = element.getAttribute("data-jsdata")
	var d = document.getElementById("debugger")
	if (d == null || d == undefined) {
		d = document.getElementById("debuggerHide")
		if (d == null || d == undefined) {
			d = document.createElement("div")
			var theBody = document.getElementsByTagName("body")[0];
			d.setAttribute("id", "debugger")
			theBody.appendChild(d);
		}
	}
	// Set the "id" to normalClass for normal display, or pauseClass for mostly hidden, but left as
	//   reminder that this code needs to be removed.
	d.setAttribute("id", pauseClass)
	d.onmouseover = function () {
		if (d.id == "debugger") {
			d.style.fontSize = "14pt"
		} else {
			d.setAttribute("id", "debugger");
		}
	}
	d.onmouseout = function () {
		d.style.fontSize = "8pt"
	}
	d.onclick = function () {
		if (d.id == "debugger") {
			d.setAttribute("id", "debuggerHide")
		} else {
			d.parentNode.removeChild(d)
			d.remove();
		}
	}
	d.innerHTML = jsdata
}