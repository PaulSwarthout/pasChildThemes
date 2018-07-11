// pasChildThemes.js

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
					showBox(element)
					document.getElementById("actionBox").innerHTML = response
				}
				break;

			case 400:
				showBox(element); // creates "DIV" with id="actionBox" and displays it.
				msg = "400 Error:<br>" + xmlhttp.statusText
				document.getElementById("actionBox").innerHTML = msg
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
			debugger

			switch (xmlhttp.status) {
				case 200: // Everything is okay
					if (response.length <= 0) {
						location.reload();
					} else {
						showBox(element);
						document.getElementById("actionBox").innerHTML = response
					}
					break;

				case 400: // There was an error
					showBox(element); // creates "DIV" with id="actionBox" and displays it.
					msg = "400 Error:<br>" + xmlhttp.statusText
					document.getElementById("actionBox").innerHTML = msg
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
						showBox(element);
						document.getElementById("actionBox").innerHTML = response
					}
					break;

				case 400: // There was an error
					showBox(element); // creates "DIV" with id="actionBox" and displays it.
					msg = "400 Error:<br>" + xmlhttp.statusText
					document.getElementById("actionBox").innerHTML = msg
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

}