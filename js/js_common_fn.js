if(typeof String.prototype.ltrim == "undefined")
	String.prototype.ltrim = function(){return this.replace(/^\s+/,"");}
if(typeof String.prototype.rtrim == "undefined")
	String.prototype.rtrim = function(){return this.replace(/\s+$/,"");}
if(typeof String.prototype.trim == "undefined")
	String.prototype.trim = function(){var str = this.ltrim();return str.rtrim();}
if(typeof String.prototype.right == "undefined")
	String.prototype.right = function(n){return this.substring(this.length - n, this.length)}
if(typeof String.prototype.left == "undefined")
	String.prototype.left = function(n) { return this.substring(0, n); }

var mousePosition = {x:0, y:0, element:null};

window.onmousemove = function (e) {
	mousePosition.x = e.clientX;
	mousePosition.y = e.clientY;
}
if (document.getElementById("childGrid") != null && document.getElementById("parentGrid") != null) {
	window.onresize = function (e) {
		var childGrid = document.getElementById("childGrid")
		var parentGrid = document.getElementById("parentGrid")

		if (childGrid != null) {
			childGrid.style = "";
		}
		if (parentGrid != null) {
			parentGrid.style = "";
		}
	}
}
/*
if (window.location.href.toLowerCase().indexOf("screenshotoptions")	>= 0) {
	var fn = null;
	if (window.onload != null) {
		fn = window.onload;
	}
	window.onload = function () {
		updateColorPicker();
		document.getElementById("bcc_cpOuter").style.display = "inline";
		document.getElementById("fcc_cpOuter").style.display = "inline";


		if (fn != null) {
			window[fn]();
		}
	}
}
*/
function getElementTree(element) {
	if (element == null) {
		return [];
	} else {
		return element.getElementsByTagName("*");
	}
}
function kill(element) {
	if (element == null) {
		return false;
	}
	if (element.parentNode != null) {
		element.parentNode.removeChild(element);
	}
	element.remove();
	return true;
}
function searchTree(rootElement, tree, pointElement) {
	var found = false;

	if (rootElement == pointElement) {
		return true;
	} else {
		for (ndx = 0; ndx < tree.length && ! found; ndx++) {
			found = (tree[ndx] == pointElement ? true : false);
		}
		return found;
	}
}
var windowFlag = false;

if (window.location.href.indexOf("manage_child_themes")	>= 0) {
	window.onclick = function (e) {
		if (windowFlag) {
			windowFlag = false;
			return;
		}
		if (e.clientX == undefined || e.clientY == undefined) {
			return;
		}

		var pointElement = null;
		var actionBox = document.getElementById("actionBox");
		var errorMessageBox = document.getElementsByName("errorMessageBox")[0];

		pointElement = document.elementFromPoint(e.clientX, e.clientY);

		var tree = getElementTree(actionBox);
		var found = false;
		found = searchTree(actionBox, tree, pointElement);

		if (! found) {
			tree = getElementTree(errorMessageBox);
			found = searchTree(errorMessageBox, tree, pointElement);
		}

		if (! found) {
			kill(actionBox);
			kill(errorMessageBox);
		}
	}
}

// KillMe kills the error message boxes.
// After the last box has been destroyed, kill the actionBox div too.
function pas_cth_js_killMe(element) {
	var elements;
	if (element.parentNode != null) {
		element.parentNode.removeChild(element);
	}
	element.remove();

	elements = document.getElementsByName("errorMessageBox");
	if (0 == elements.length) {
		var actionBox = document.getElementById("actionBox");
		if (actionBox.parentNode != null) {
			actionBox.parentNode.removeChild(actionBox);
		}
		actionBox.remove();
	}
}
function pas_cth_js_popupMessage(abbr, msg) {
	var box = document.getElementById("popupMessageBox")
	box.innerHTML = msg;
	box.style.position = "fixed";
	box.style.left = mousePosition.x + 100 + "px";
	box.style.top  = mousePosition.y - 40 + "px";
	box.style.display = "inline";
	setTimeout(function () {
				var p = document.getElementById("popupMessageBox")
				p.innerHTML = "";
				p.style.display = "none";
			   }, 2000);
}
// Process the xmlhttp.responseText that was echo'd during the AJAX call
function pas_cth_js_processResponse(response) {
	if ("ABBREVIATION:{" == response.left("ABBREVIATION:{".length).toUpperCase()) {
		abbr = response.right(response.length - "ABBREVIATION:{".length);
		abbr = abbr.left(abbr.length - 1);
		pas_cth_js_popupMessage(abbr, "color saved");
		document.getElementById("popupMessageBox").style.display = "inline";
	} else if ("DEBUG:{" == response.left("DEBUG:{".length).toUpperCase()) {
		actionBox = document.getElementById("actionBox");
		if (actionBox != null && actionBox != undefined) {
			if (actionBox.parentNode != null) {
				actionBox.parentNode.removeChild(actionBox);
			}
			actionBox.remove();
		}
		debugResponse = response.right(response.length - "debug:{".length);
		debugResponse = debugResponse.left(debugResponse.length - 1);
		box = pas_cth_js_createBox('debugBox', 'debug');
		box.innerHTML = debugResponse;
	// Nothing special. We've got output from the PHP function, dump it to the screen.
	} else {
		if (response.length > 0) {
			pas_cth_js_createBox("actionBox", "").innerHTML = response;
			windowFlag = true;
		}
	}
}
function pas_cth_js_showBox() {
	return pas_cth_js_createBox("actionBox", "");
}
function pas_cth_js_createBox(id,className) {
	var box = document.getElementById(id);
	if (box != null && box != undefined) {
		if (box.parentNode != null) {
			box.parentNode.removeChild(box);
		}
		box.remove();
	}
	box = document.createElement("div");
	box.setAttribute("id", id);
	if (className.trim.length > 0) {
		box.className = className;
	}
	theBody = document.getElementsByTagName("body")[0];
	theBody.appendChild(box);
/*
	box.onclick= function () {
		if (this.parentNode != null) {
			this.parentNode.removeChild(this);
		}
		this.remove();
	}
*/
	return box;
}
