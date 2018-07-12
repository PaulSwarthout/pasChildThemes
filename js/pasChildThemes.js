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
function clearHighlights() {
	liElements = document.getElementsByTagName("li")
	for (ndx = 0; ndx < liElements.length; ndx++) {
		if (liElements[ndx].getAttribute("data-bgc")) {
			liElements[ndx].style.backgroundColor = liElements[ndx].getAttribute("data-bgc")
			liElements[ndx].style.color = liElements[ndx].getAttribute("data-c")
			liElements[ndx].removeAttribute("data-bgc")
			liElements[ndx].removeAttribute("data-c")
		}
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
function deleteChildFile(element) {
	var xmlhttp = new XMLHttpRequest()
	var data = new FormData()
	var jsInput = JSON.parse(element.getAttribute("data-jsdata"))

	xmlhttp.open("POST", ajaxurl, true)
	data.append("themeRoot", jsInput['childThemeRoot'] );
	data.append("directory", jsInput['directory'] );
	data.append("fileToDelete", jsInput['childFileToRemove']);
	data.append("delimiter", jsInput['delimiter'] );
	data.append("action", jsInput['action']);

	xmlhttp.onreadystatechange = function () {
		var response = (xmlhttp.responseText.length >= 1 ? xmlhttp.responseText.left(xmlhttp.responseText.length - 1) : xmlhttp.responseText);

		switch (xmlhttp.status) {
			case 200: // Everything is okay
				if (response.length <= 0) {
					location.reload()
				} else {
					showBox(element).innerHTML = response
				}
				break;

			case 400:
				msg = "400 Error:<br>" + xmlhttp.statusText
				showBox(element).innerHTML = msg
				break;
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
						showBox(element).innerHTML = response
					}
					break;

				case 400: // There was an error
					msg = "400 Error:<br>" + xmlhttp.statusText
					showBox(element).innerHTML = msg
					break;
			}
		}
	}

	xmlhttp.send(data);
}
function copyFile(element) {
	var xmlhttp = new XMLHttpRequest()
	var data = new FormData()
	var jsInput

	jsInput = JSON.parse(element.getAttribute("data-jsdata")) // requires HTML5 global attribute support "data-*"

	xmlhttp.open("POST", ajaxurl,true) // AJAX call to "function pasChildThemes_selectFile()" in pasChildThemes.php

	data.append("action", "selectFile")
	data.append("directory", jsInput["directory"])
	data.append("file", jsInput["file"])
	data.append("type", jsInput["type"])
	data.append("delimiter", jsInput["delimiter"])

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
						showBox(element).innerHTML = response
					}
					break;

				case 400: // There was an error
					msg = "400 Error:<br>" + xmlhttp.statusText
					showBox(element).innerHTML = msg
					break;
			}
		}
	}
	xmlhttp.send(data);
}
function showBox(element) {
	var box = document.getElementById("actionBox")
	var e;
	if (box == null || box == undefined) {
		var box = document.createElement("div")
		var theBody = document.getElementsByTagName("body")[0];
		box.setAttribute("id", "actionBox");
		theBody.appendChild(box);
	}
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
						showBox(element).innerHTML = response
					}
					break;

				case 400: // There was an error
					msg = "400 Error:<br>" + xmlhttp.statusText + "<br>" + response;
					showBox(element).innerHTML = msg
					break;
			}
		}
	}
	xmlhttp.send(data);
}
