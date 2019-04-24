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

window.addEventListener("mousemove", function (e) {
	mousePosition.x = e.clientX;
	mousePosition.y = e.clientY;
});
function pas_cth_js_AJAXCall(action, dataBlock = [], callback = null, error_callback = null) {
	var xmlhttp = new XMLHttpRequest();
	var data	= new FormData();

	data.append("action", action);

	if (dataBlock != null) {
		Object.keys(dataBlock).forEach(function(key) {
			if (key != "action") {
				data.append(key, dataBlock[key]);
			}
		});
	}
	xmlhttp.open("POST", ajaxurl, true);
	xmlhttp.onreadystatechange = function () {
		if (4 == xmlhttp.readyState) {
			// The next line strips the admin_ajax.php 1 byte response from the beginning of the response.
			// Usually, admin_ajax.php returns a zero. This strips that.
			var response = (xmlhttp.responseText.length >= 1 ? xmlhttp.responseText.left(xmlhttp.responseText.length - 1) : "");
			if (xmlhttp.status == 200) {
				if (callback != null) {
					callback(response);
				}
			} else {
				if (error_callback != null) {
					error_callback(xmlhttp.statusText, response);
				} else {
					alert("AJAX Error:\n\n" + xmlhttp.statusText + "\n\n" + response);
				}
			}
		}
	}
	xmlhttp.send(data);
}
if (document.getElementById("childGrid") != null && document.getElementById("parentGrid") != null) {
	window.onresize = function (e) {
		var childGrid = document.getElementById("childGrid")
		var parentGrid = document.getElementById("parentGrid")

		// Clears inline styles
		if (childGrid != null) {
			childGrid.style = "";
		}
		if (parentGrid != null) {
			parentGrid.style = "";
		}
	}
}
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
		var actionBox = document.getElementById("pas_cth_actionBox");
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
		var actionBox = document.getElementById("pas_cth_actionBox");
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
	} else if ("EDITFILEOUTPUT:{" == response.left("EDITFILEOUTPUT:{".length).toUpperCase()) {
		response = response.right(response.length - "EDITFILEOUTPUT:{".length);
		response = response.left(response.length - 1);
		processEditFile(response);
	} else if ("DEBUG:{" == response.left("DEBUG:{".length).toUpperCase()) {
		actionBox = document.getElementById("pas_cth_actionBox");
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
			pas_cth_js_createBox("pas_cth_actionBox", "").innerHTML = response;
			windowFlag = true;
		}
	}
}
function pas_cth_js_showBox() {
	return pas_cth_js_createBox("pas_cth_actionBox", "");
}
function pas_cth_js_createBox(id, className = "", parent = document.getElementsByTagName("body")[0], clickClose = false) {
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

	parent.appendChild(box);

	if (clickClose) {
		box.onclick= function () {
			if (this.parentNode != null) {
				this.parentNode.removeChild(this);
			}
			this.remove();
		}
	} else {
		var dismissBTN = document.createElement("p")
		dismissBTN.setAttribute("id", "dismissBox");
		box.appendChild(dismissBTN);
		dismissBTN.innerHTML = "DISMISS";
		dismissBTN.onclick = function () {
			var ab = document.getElementById("pas_cth_actionBox");
			if (ab.parentNode != null) {
				ab.parentNode.removeChild(ab);
			}
			ab.remove();
		}


/*
		box.oncontextmenu = function () {
			box.style.width = "100%";
			box.style.height = "100%";
			box.style.position = "absolute";
			box.style.left = "180px";
			box.style.top  = "40px";
			box.style.zIndex = 9999999;
			box.style.overflow = "scroll";
			box.style.marginTop = "0px";
			box.style.marginLeft = "0px";
			return false;
		}
*/
	}
	return box;
}
/*
function findPos(obj) {
	var curleft = curtop = 0;
	if (obj.offsetParent) {
		do {
			curleft += obj.offsetLeft;
			curtop += obj.offsetTop;
		} while (obj = obj.offsetParent);
	}
	return {left:curleft ,top:curtop};
}
*/
function getPosition(element) {
	var rect = element.getBoundingClientRect();

	return {left : rect.left, top: rect.top, width : Math.abs(rect.right - rect.left), height : Math.abs(rect.bottom - rect.top) };
}
function pas_cth_js_addCloseButton(id, parent, text) {
	var element = document.createElement("p");
	element.setAttribute("id", id);
//	element.setAttribute("contentEditable", false);
	parent.appendChild(element);
	element.innerHTML = text;
	element.onclick = function () {
		var myParent = this.parentNode;
		var myGrandParent = myParent.parentNode;

		if (myGrandParent != null) {
			myGrandParent.removeChild(myParent);
			myParent.remove();
		}
	}
}
function getTopLeftPosition(obj = null) {
	if (obj != null) {
		return getPosition(obj);
	} else {
		return null;
	}
}
function displayError(str) {
	var box = document.getElementById("popupErrorMessage");
	var theBody = document.getElementsByTagName("body")[0];
	if (box != null) {
		if (box.parentNode != null) { box.parentNode.removeChild(box); }
		box.remove();
	}
	box = pas_cth_js_createBox("popupErrorMessage", "", theBody, true);
//	box.appendChild(document.createTextNode(str));
	box.innerHTML = str;
	return box;
}